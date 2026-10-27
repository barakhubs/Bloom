<?php

namespace App\Core;

use App\Models\Setting;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    /**
     * Sends a contact-form notification to the org's contact email using the
     * admin-configured SMTP settings. Returns false (and logs) without throwing
     * if SMTP isn't configured yet or the send fails - a missing/broken SMTP
     * setup should never block saving the submission to the database.
     */
    public static function sendContactNotification(string $name, string $email, string $message): bool
    {
        $settings = Setting::all();
        $mail = self::make($settings, 'contact notification');

        if ($mail === null) {
            return false;
        }

        try {
            $recipient = $settings['contact_email'] ?: $mail->From;
            $mail->addAddress($recipient);
            $mail->addReplyTo($email, $name);

            $mail->Subject = 'New contact form submission from ' . $name;
            $mail->Body = "From: {$name} <{$email}>\n\n{$message}";

            $mail->send();

            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer: failed to send contact notification - ' . $mail->ErrorInfo);

            return false;
        }
    }

    /**
     * Emails a back-office invite ($purpose 'invite') or password-reset
     * ('reset') link. Same failure contract as above: false + log, never throws,
     * so callers can fall back to showing the link to the admin.
     */
    public static function sendAccountLink(string $email, string $name, string $url, string $purpose): bool
    {
        $settings = Setting::all();
        $mail = self::make($settings, "{$purpose} email");

        if ($mail === null) {
            return false;
        }

        $siteName = $settings['smtp_from_name'] ?: 'Bloom Beyond Borders';

        if ($purpose === 'invite') {
            $subject = "You've been invited to the {$siteName} admin";
            $intro = "You've been given an account on the {$siteName} website admin. "
                . 'Follow the link below to choose your password and sign in:';
            $expiry = 'This link expires in 72 hours and can only be used once.';
        } else {
            $subject = "Reset your {$siteName} admin password";
            $intro = 'A password reset was requested for your admin account. '
                . 'Follow the link below to choose a new password:';
            $expiry = "This link expires in 2 hours and can only be used once. If you didn't ask for this, you can ignore this email.";
        }

        try {
            $mail->addAddress($email, $name);
            $mail->Subject = $subject;
            $mail->Body = "Hi {$name},\n\n{$intro}\n\n{$url}\n\n{$expiry}\n";

            $mail->send();

            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer: failed to send {$purpose} email - " . $mail->ErrorInfo);

            return false;
        }
    }

    /** Thank-you / receipt to the donor once the webhook confirms payment. Same failure contract. */
    public static function sendDonationReceipt(array $donation): bool
    {
        if (empty($donation['donor_email'])) {
            return false;
        }

        $settings = Setting::all();
        $siteName = $settings['smtp_from_name'] ?: 'Bloom Beyond Borders';
        $name = $donation['donor_name'] ?: 'friend';
        $towards = $donation['scheme_name'] ?? null;

        $body = "Dear {$name},\n\n"
            . 'Thank you for your donation of ' . Money::format((int) $donation['amount_cents'])
            . ($towards ? " towards \"{$towards}\"" : '') . " to {$siteName}.\n\n"
            . "Your generosity directly supports vulnerable children and women in Uganda and immigrant families in the US.\n\n"
            . 'Donation reference: D-' . (int) $donation['id'] . "\n"
            . 'Date: ' . date('j F Y', strtotime((string) ($donation['paid_at'] ?? 'now'))) . "\n\n"
            . "With gratitude,\n{$siteName}\n";

        return self::sendTo($settings, (string) $donation['donor_email'], $donation['donor_name'] ?: '', "Thank you for your donation to {$siteName}", $body, 'donation receipt');
    }

    /** Short heads-up to the org's contact email about a new paid donation. */
    public static function sendDonationNotification(array $donation): bool
    {
        $settings = Setting::all();
        $amount = Money::format((int) $donation['amount_cents']);
        $towards = $donation['scheme_name'] ?? 'General donation';

        $body = "A new donation has been received.\n\n"
            . "Amount: {$amount}\n"
            . "Towards: {$towards}\n"
            . 'From: ' . ($donation['donor_name'] ?: '(no name)') . ' <' . ($donation['donor_email'] ?: 'no email') . ">\n"
            . (!empty($donation['message']) ? "\nMessage:\n{$donation['message']}\n" : '');

        return self::sendTo($settings, (string) ($settings['contact_email'] ?? ''), '', "New donation: {$amount} ({$towards})", $body, 'donation notification');
    }

    /** Plain-text email to one recipient; false + log on any failure (never throws). */
    private static function sendTo(array $settings, string $to, string $toName, string $subject, string $body, string $context): bool
    {
        $mail = self::make($settings, $context);

        if ($mail === null) {
            return false;
        }

        try {
            $mail->addAddress($to !== '' ? $to : $mail->From, $toName);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();

            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer: failed to send {$context} - " . $mail->ErrorInfo);

            return false;
        }
    }

    /** A PHPMailer configured from the SMTP settings, or null (logged) if SMTP isn't set up. */
    private static function make(array $settings, string $context): ?PHPMailer
    {
        $host = trim($settings['smtp_host'] ?? '');

        if ($host === '') {
            error_log("Mailer: SMTP host not configured in settings, skipping {$context}.");

            return null;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $settings['smtp_username'] ?? '';
            $mail->Password = $settings['smtp_password'] ?? '';
            $mail->SMTPSecure = $settings['smtp_encryption'] ?: PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) ($settings['smtp_port'] ?: 587);

            $fromEmail = $settings['smtp_from_email'] ?: 'no-reply@bloombeyondborders.org';
            $fromName = $settings['smtp_from_name'] ?: 'Bloom Beyond Borders';
            $mail->setFrom($fromEmail, $fromName);
        } catch (PHPMailerException $e) {
            error_log("Mailer: invalid SMTP settings for {$context} - " . $e->getMessage());

            return null;
        }

        return $mail;
    }
}
