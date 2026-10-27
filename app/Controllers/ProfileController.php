<?php

namespace App\Controllers;

use App\Controllers\Admin\AccountController as AdminAccountController;
use App\Core\Csrf;
use App\Core\CustomerAuth;
use App\Core\CustomerController;
use App\Core\CustomerLinks;
use App\Core\Shop;
use App\Models\Customer;
use App\Models\CustomerToken;

/** Profile, saved shipping address, email/password changes, and account deletion. */
class ProfileController extends CustomerController
{
    public function index(): void
    {
        $errors = $_SESSION['profile_errors'] ?? [];
        $flash = $_SESSION['account_flash'] ?? null;
        unset($_SESSION['profile_errors'], $_SESSION['account_flash']);

        $this->view('account/profile', [
            'title' => 'Profile - Bloom Beyond Borders',
            'pageTitle' => 'My Profile',
            'noindex' => true,
            'customer' => $this->customer,
            'shipCountries' => Shop::shipCountries(),
            'errors' => $errors,
            'flash' => $flash,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(): void
    {
        $this->guardCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $field = fn (string $key, int $max) => mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max) ?: null;
        $country = strtoupper(trim((string) ($_POST['ship_country'] ?? '')));

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Please enter your name.';
        }
        if ($country !== '' && !preg_match('/^[A-Z]{2}$/', $country)) {
            $errors['ship_country'] = 'Choose a country.';
        }
        if ($field('ship_line1', 255) !== null && ($field('ship_city', 120) === null || $country === '')) {
            $errors['ship_line1'] = 'Please complete the address (street, city and country), or leave it all blank.';
        }

        if (!empty($errors)) {
            $this->fail($errors);
        }

        (new Customer())->updateProfile((int) $this->customer['id'], [
            'name' => mb_substr($name, 0, 150),
            'phone' => $field('phone', 50),
            'ship_line1' => $field('ship_line1', 255),
            'ship_line2' => $field('ship_line2', 255),
            'ship_city' => $field('ship_city', 120),
            'ship_state' => $field('ship_state', 120),
            'ship_postal_code' => $field('ship_postal_code', 30),
            'ship_country' => $country !== '' ? $country : null,
        ]);

        $this->done('Your profile has been saved.');
    }

    /** Needs the current password; the new address must be confirmed before history is linked to it. */
    public function changeEmail(): void
    {
        $this->guardCsrf();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $customers = new Customer();

        if (!password_verify((string) ($_POST['current_password'] ?? ''), $this->customer['password_hash'])) {
            $this->fail(['email' => 'Your current password is incorrect.']);
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail(['email' => 'Please enter a valid email address.']);
        }
        if ($email === $this->customer['email']) {
            $this->done('That\'s already your email address.');
        }
        if ($customers->findByEmail($email) !== null) {
            $this->fail(['email' => 'That email address is already used by another account.']);
        }

        $customerId = (int) $this->customer['id'];
        (new CustomerToken())->voidAll($customerId);
        $customers->changeEmail($customerId, $email);
        CustomerAuth::refresh();
        CustomerLinks::send($customers->find($customerId), 'verify');

        $this->done('Email updated. We\'ve sent a confirmation link to ' . $email . '.');
    }

    public function changePassword(): void
    {
        $this->guardCsrf();

        if (!password_verify((string) ($_POST['current_password'] ?? ''), $this->customer['password_hash'])) {
            $this->fail(['password' => 'Your current password is incorrect.']);
        }

        $password = (string) ($_POST['password'] ?? '');
        $passwordError = AdminAccountController::passwordError($password, (string) ($_POST['password_confirmation'] ?? ''));

        if ($passwordError !== null) {
            $this->fail(['password' => $passwordError]);
        }

        (new Customer())->setPassword((int) $this->customer['id'], password_hash($password, PASSWORD_DEFAULT));
        (new CustomerToken())->voidAll((int) $this->customer['id']);
        session_regenerate_id(true);

        $this->done('Your password has been changed.');
    }

    /** Orders and donations are kept for the org's records but unlinked from the account. */
    public function destroy(): void
    {
        $this->guardCsrf();

        if (!password_verify((string) ($_POST['current_password'] ?? ''), $this->customer['password_hash'])) {
            $this->fail(['delete' => 'Your password is incorrect.']);
        }

        (new Customer())->delete((int) $this->customer['id']);
        CustomerAuth::logout();

        $_SESSION['account_flash'] = 'Your account has been deleted.';
        header('Location: /account/login');
        exit;
    }

    private function guardCsrf(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->fail(['general' => 'Your session expired. Please try again.']);
        }
    }

    private function fail(array $errors): never
    {
        $_SESSION['profile_errors'] = $errors;
        header('Location: /account/profile');
        exit;
    }

    private function done(string $message): never
    {
        $_SESSION['account_flash'] = $message;
        header('Location: /account/profile');
        exit;
    }
}
