<?php

namespace App\Controllers;

use App\Controllers\Admin\AccountController as AdminAccountController;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\CustomerAuth;
use App\Core\CustomerLinks;
use App\Models\Customer;
use App\Models\CustomerToken;

/**
 * Optional customer accounts: sign up, sign in, email verification, and
 * password reset. Guest checkout and guest donations never require any of this.
 */
class CustomerAuthController extends Controller
{
    public function showRegister(): void
    {
        if (CustomerAuth::check()) {
            header('Location: /account');
            exit;
        }

        [$errors, $old] = $this->takeFlash();

        $this->view('account/auth/register', [
            'title' => 'Create an Account - Bloom Beyond Borders',
            'pageTitle' => 'Create an Account',
            'noindex' => true,
            'errors' => $errors,
            'old' => $old + ['email' => (string) ($_GET['email'] ?? '')],
            'csrfToken' => Csrf::token(),
        ]);
    }

    /**
     * Responds identically whether or not the email already has an account, so
     * the form can't be used to find out who's registered. An existing owner
     * gets a password-reset link instead of a duplicate account.
     */
    public function register(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            $this->checkEmail($email);
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->fail('/account/register', ['general' => 'Your session expired. Please try again.'], ['name' => $name, 'email' => $email]);
        }

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Please enter your name.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        $passwordError = AdminAccountController::passwordError($password, (string) ($_POST['password_confirmation'] ?? ''));
        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        }

        if (!empty($errors)) {
            $this->fail('/account/register', $errors, ['name' => $name, 'email' => $email]);
        }

        $customers = new Customer();
        $existing = $customers->findByEmail($email);

        if ($existing !== null) {
            $this->sendLink($existing, $existing['email_verified_at'] !== null ? 'reset' : 'verify');
        } else {
            $id = $customers->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
            $this->sendLink($customers->find($id), 'verify');
        }

        $this->checkEmail($email);
    }

    public function showCheckEmail(): void
    {
        $email = $_SESSION['account_check_email'] ?? null;
        unset($_SESSION['account_check_email']);

        $this->view('account/auth/check-email', [
            'title' => 'Check Your Email - Bloom Beyond Borders',
            'pageTitle' => 'Check Your Email',
            'noindex' => true,
            'email' => $email,
        ]);
    }

    public function verify(string $token): void
    {
        $tokens = new CustomerToken();
        $row = $tokens->findValid($token, 'verify');
        $customers = new Customer();
        $customer = $row !== null ? $customers->find((int) $row['customer_id']) : null;

        if ($customer === null || !$tokens->consume((int) $row['id'])) {
            $_SESSION['account_flash'] = 'That confirmation link is invalid or has expired. Sign in to request a new one.';
            header('Location: /account');
            exit;
        }

        // Ownership of the address is now proven, so it's safe to attach earlier
        // guest orders/donations placed with it.
        $customers->markVerified((int) $customer['id']);
        $customers->linkGuestHistory((int) $customer['id'], $customer['email']);
        CustomerAuth::refresh();

        $_SESSION['account_flash'] = 'Thanks, your email is confirmed. Any earlier orders and donations made with it now appear in your account.';
        header('Location: /account');
        exit;
    }

    public function showLogin(): void
    {
        $next = CustomerAuth::safeNext($_GET['next'] ?? null);

        if (CustomerAuth::check()) {
            header('Location: ' . $next);
            exit;
        }

        [$errors, $old] = $this->takeFlash();
        $flash = $_SESSION['account_flash'] ?? null;
        unset($_SESSION['account_flash']);

        $this->view('account/auth/login', [
            'title' => 'Sign In - Bloom Beyond Borders',
            'pageTitle' => 'Sign In',
            'noindex' => true,
            'errors' => $errors,
            'old' => $old,
            'next' => $next,
            'flash' => $flash,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function login(): void
    {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $next = CustomerAuth::safeNext($_POST['next'] ?? null);
        $back = '/account/login?next=' . rawurlencode($next);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->fail($back, ['general' => 'Your session expired. Please try again.'], ['email' => $email]);
        }

        $customers = new Customer();
        $customer = $email !== '' ? $customers->findByEmail($email) : null;

        if ($customer === null || !password_verify($password, $customer['password_hash'])) {
            $this->fail($back, ['general' => 'Incorrect email or password.'], ['email' => $email]);
        }

        if (password_needs_rehash($customer['password_hash'], PASSWORD_DEFAULT)) {
            $customers->setPassword((int) $customer['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        CustomerAuth::login((int) $customer['id']);
        $customers->touchLogin((int) $customer['id']);
        header('Location: ' . $next);
        exit;
    }

    public function logout(): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            CustomerAuth::logout();
        }

        header('Location: /');
        exit;
    }

    public function showForgot(): void
    {
        $sent = $_SESSION['account_forgot_sent'] ?? false;
        unset($_SESSION['account_forgot_sent']);

        $this->view('account/auth/forgot', [
            'title' => 'Forgot Password - Bloom Beyond Borders',
            'pageTitle' => 'Forgot Password',
            'noindex' => true,
            'sent' => $sent,
            'csrfToken' => Csrf::token(),
        ]);
    }

    /** Same response whether or not the email has an account. */
    public function forgot(): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null) && trim((string) ($_POST['website'] ?? '')) === '') {
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $customer = $email !== '' ? (new Customer())->findByEmail($email) : null;

            if ($customer !== null) {
                $this->sendLink($customer, 'reset');
            }

            $_SESSION['account_forgot_sent'] = true;
        }

        header('Location: /account/forgot');
        exit;
    }

    public function showReset(string $token): void
    {
        [$errors] = $this->takeFlash();

        $this->view('account/auth/reset', [
            'title' => 'Choose a New Password - Bloom Beyond Borders',
            'pageTitle' => 'New Password',
            'noindex' => true,
            'token' => $token,
            'valid' => (new CustomerToken())->findValid($token, 'reset') !== null,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function reset(string $token): void
    {
        $back = '/account/reset/' . rawurlencode($token);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->fail($back, ['general' => 'Your session expired. Please try again.'], []);
        }

        $tokens = new CustomerToken();
        $row = $tokens->findValid($token, 'reset');
        $customers = new Customer();
        $customer = $row !== null ? $customers->find((int) $row['customer_id']) : null;

        if ($customer === null) {
            header('Location: ' . $back);
            exit;
        }

        $password = (string) ($_POST['password'] ?? '');
        $passwordError = AdminAccountController::passwordError($password, (string) ($_POST['password_confirmation'] ?? ''));

        if ($passwordError !== null) {
            $this->fail($back, ['password' => $passwordError], []);
        }

        if (!$tokens->consume((int) $row['id'])) {
            header('Location: ' . $back);
            exit;
        }

        $customers->setPassword((int) $customer['id'], password_hash($password, PASSWORD_DEFAULT));

        // The reset link was delivered to this inbox, which also proves ownership.
        if ($customer['email_verified_at'] === null) {
            $customers->markVerified((int) $customer['id']);
            $customers->linkGuestHistory((int) $customer['id'], $customer['email']);
        }

        CustomerAuth::login((int) $customer['id']);
        $customers->touchLogin((int) $customer['id']);
        $_SESSION['account_flash'] = 'Your password has been changed.';
        header('Location: /account');
        exit;
    }

    private function sendLink(array $customer, string $purpose): void
    {
        if (!CustomerLinks::send($customer, $purpose)) {
            error_log("Customer {$purpose} email could not be sent to customer #{$customer['id']} (check SMTP settings).");
        }
    }

    private function checkEmail(string $email): never
    {
        $_SESSION['account_check_email'] = $email;
        header('Location: /account/check-email');
        exit;
    }

    /** @return array{0: array, 1: array} flashed [errors, old input] */
    private function takeFlash(): array
    {
        $errors = $_SESSION['account_errors'] ?? [];
        $old = $_SESSION['account_old'] ?? [];
        unset($_SESSION['account_errors'], $_SESSION['account_old']);

        return [$errors, $old];
    }

    private function fail(string $to, array $errors, array $old): never
    {
        $_SESSION['account_errors'] = $errors;
        $_SESSION['account_old'] = $old;
        header('Location: ' . $to);
        exit;
    }
}
