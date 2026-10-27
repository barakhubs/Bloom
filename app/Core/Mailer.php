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

    /** Customer account email-verification ('verify') or password-reset ('reset') link. */
    public static function sendCustomerLink(string $email, string $name, string $url, string $purpose): bool
    {
        $settings = Setting::all();
        $siteName = $settings['smtp_from_name'] ?: 'Bloom Beyond Borders';

        if ($purpose === 'verify') {
            $subject = "Confirm your {$siteName} account email";
            $intro = "Thanks for creating an account with {$siteName}. Please confirm your email address by following the link below. "
                . 'Once confirmed, any earlier orders and donations made with this email will appear in your account.';
            $expiry = "This link expires in 48 hours. If you didn't create an account, you can ignore this email.";
        } else {
            $subject = "Reset your {$siteName} password";
            $intro = 'A password reset was requested for your account. Follow the link below to choose a new password:';
            $expiry = "This link expires in 2 hours and can only be used once. If you didn't ask for this, you can ignore this email.";
        }

        return self::sendTo($settings, $email, $name, $subject, "Hi {$name},\n\n{$intro}\n\n{$url}\n\n{$expiry}\n", "customer {$purpose} email");
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

    /** Order details to the buyer once the webhook confirms payment (no account needed). */
    public static function sendOrderConfirmation(array $order): bool
    {
        if (empty($order['customer_email'])) {
            return false;
        }

        $settings = Setting::all();
        $siteName = $settings['smtp_from_name'] ?: 'Bloom Beyond Borders';

        $body = 'Hi ' . ($order['customer_name'] ?: 'there') . ",\n\n"
            . "Thank you for your order! Every purchase supports our work with children, women, and families.\n\n"
            . self::orderSummary($order)
            . "\nWe'll email you again when your order ships. If you have any questions, just reply to this email"
            . (!empty($settings['contact_email']) ? " or write to {$settings['contact_email']}" : '') . ".\n\n"
            . "{$siteName}\n";

        return self::sendTo($settings, $order['customer_email'], $order['customer_name'] ?: '', "Your {$siteName} order {$order['reference']}", $body, 'order confirmation');
    }

    /** New paid order heads-up to the org's contact email; flags items that just sold out. */
    public static function sendOrderNotification(array $order, array $soldOut = []): bool
    {
        $settings = Setting::all();

        $body = "A new shop order has been paid and is ready to ship.\n\n"
            . 'Customer: ' . ($order['customer_name'] ?: '(no name)') . ' <' . ($order['customer_email'] ?: 'no email') . '>'
            . (!empty($order['customer_phone']) ? ", {$order['customer_phone']}" : '') . "\n\n"
            . self::orderSummary($order)
            . (!empty($soldOut) ? "\nNow out of stock: " . implode(', ', $soldOut) . "\n" : '')
            . "\nManage it in the admin under Orders.\n";

        return self::sendTo($settings, (string) ($settings['contact_email'] ?? ''), '', "New order {$order['reference']} (" . Money::format((int) $order['total_cents']) . ')', $body, 'order notification');
    }

    /** "Your order has shipped" to the buyer, sent when an admin marks it shipped. */
    public static function sendOrderShipped(array $order): bool
    {
        if (empty($order['customer_email'])) {
            return false;
        }

        $settings = Setting::all();
        $siteName = $settings['smtp_from_name'] ?: 'Bloom Beyond Borders';

        $body = 'Hi ' . ($order['customer_name'] ?: 'there') . ",\n\n"
            . "Good news: your order {$order['reference']} is on its way.\n\n"
            . self::orderSummary($order)
            . "\nThank you for supporting {$siteName}.\n";

        return self::sendTo($settings, $order['customer_email'], $order['customer_name'] ?: '', "Your order {$order['reference']} has shipped", $body, 'order shipped email');
    }

    /** Plain-text items, totals and ship-to block shared by the order emails. */
    private static function orderSummary(array $order): string
    {
        $text = "Order: {$order['reference']}\n\n";

        foreach ($order['items'] as $item) {
            $text .= (int) $item['quantity'] . ' x ' . $item['product_name'] . ' - '
                . Money::format((int) $item['unit_price_cents'] * (int) $item['quantity']) . "\n";
        }

        $text .= "\nSubtotal: " . Money::format((int) $order['subtotal_cents']) . "\n"
            . 'Shipping: ' . Money::format((int) $order['shipping_cents']) . "\n"
            . 'Total: ' . Money::format((int) $order['total_cents']) . "\n";

        $address = array_filter([
            $order['ship_name'] ?? null,
            $order['ship_line1'] ?? null,
            $order['ship_line2'] ?? null,
            trim(implode(' ', array_filter([$order['ship_city'] ?? null, $order['ship_state'] ?? null, $order['ship_postal_code'] ?? null]))),
            $order['ship_country'] ?? null,
        ]);

        if (!empty($address)) {
            $text .= "\nShipping to:\n" . implode("\n", $address) . "\n";
        }

        return $text;
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
