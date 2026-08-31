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
        $host = trim($settings['smtp_host'] ?? '');

        if ($host === '') {
            error_log('Mailer: SMTP host not configured in settings, skipping contact notification email.');

            return false;
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

            $recipient = $settings['contact_email'] ?: $fromEmail;
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
}
