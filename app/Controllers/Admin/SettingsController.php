<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Upload;
use App\Models\Setting;

class SettingsController extends AdminController
{
    private const TEXT_FIELDS = [
        'contact_phone',
        'contact_email',
        'contact_address',
        'social_linkedin',
        'social_instagram',
        'social_facebook',
        'social_x',
        'whatsapp_us',
        'whatsapp_ug',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_encryption',
        'smtp_from_email',
        'smtp_from_name',
    ];

    public function index(): void
    {
        $errors = $_SESSION['settings_errors'] ?? [];
        $success = $_SESSION['settings_success'] ?? false;
        unset($_SESSION['settings_errors'], $_SESSION['settings_success']);

        $this->view('admin/settings/index', [
            'title' => 'Site Settings - Bloom Beyond Borders Admin',
            'settings' => Setting::all(),
            'errors' => $errors,
            'success' => $success,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $_SESSION['settings_errors'] = ['general' => 'Your session expired. Please try again.'];
            header('Location: /admin/settings');
            exit;
        }

        foreach (self::TEXT_FIELDS as $field) {
            Setting::set($field, trim((string) ($_POST[$field] ?? '')));
        }

        // Only overwrite the stored SMTP password if a new one was actually typed -
        // the field always renders empty, so an untouched submit must not blank it.
        $smtpPassword = trim((string) ($_POST['smtp_password'] ?? ''));
        if ($smtpPassword !== '') {
            Setting::set('smtp_password', $smtpPassword);
        }

        $errors = [];

        try {
            $logoPath = Upload::store($_FILES['site_logo'] ?? [], 'logos', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
            if ($logoPath !== null) {
                Setting::set('site_logo_path', $logoPath);
            }
        } catch (\RuntimeException $e) {
            $errors['site_logo'] = $e->getMessage();
        }

        try {
            $faviconPath = Upload::store($_FILES['favicon'] ?? [], 'logos', ['ico', 'png'], 512 * 1024);
            if ($faviconPath !== null) {
                Setting::set('favicon_path', $faviconPath);
            }
        } catch (\RuntimeException $e) {
            $errors['favicon'] = $e->getMessage();
        }

        try {
            $footerLogoPath = Upload::store($_FILES['footer_logo'] ?? [], 'logos', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
            if ($footerLogoPath !== null) {
                Setting::set('footer_logo_path', $footerLogoPath);
            }
        } catch (\RuntimeException $e) {
            $errors['footer_logo'] = $e->getMessage();
        }

        if (!empty($errors)) {
            $_SESSION['settings_errors'] = $errors;
        } else {
            $_SESSION['settings_success'] = true;
        }

        header('Location: /admin/settings');
        exit;
    }
}
