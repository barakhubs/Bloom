<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Money;
use App\Core\Stripe;
use App\Models\Donation;
use App\Models\DonationScheme;
use Stripe\Exception\ApiErrorException;

class DonationController extends Controller
{
    /** Suggested amounts (cents) for a general, non-scheme donation. */
    private const GENERAL_PRESETS = [2500, 5000, 10000, 25000];

    private const MIN_CENTS = 100;
    private const MAX_CENTS = 5000000;

    public function index(): void
    {
        [$errors, $old] = $this->takeFlash();

        $this->view('donate/index', [
            'title' => 'Donate - Bloom Beyond Borders',
            'pageTitle' => 'Donate',
            'description' => 'Give to Bloom Beyond Borders: support a specific campaign or donate any amount to help children and women in Uganda and immigrant families in the USA.',
            'schemes' => (new DonationScheme())->active(),
            'presets' => self::GENERAL_PRESETS,
            'errors' => $errors,
            'old' => $old,
            'csrfToken' => Csrf::token(),
            'paymentsEnabled' => Stripe::isConfigured(),
        ]);
    }

    public function show(string $slug): void
    {
        $scheme = (new DonationScheme())->findActiveBySlug($slug);

        if ($scheme === null) {
            (new ErrorController())->notFound();

            return;
        }

        [$errors, $old] = $this->takeFlash();

        $this->view('donate/show', [
            'title' => $scheme['name'] . ' - Donate - Bloom Beyond Borders',
            'pageTitle' => $scheme['name'],
            'description' => $scheme['summary'] ?: 'Support ' . $scheme['name'] . ' with Bloom Beyond Borders.',
            'ogImage' => !empty($scheme['image_path']) ? '/' . ltrim($scheme['image_path'], '/') : null,
            'scheme' => $scheme,
            'presets' => DonationScheme::presets($scheme),
            'errors' => $errors,
            'old' => $old,
            'csrfToken' => Csrf::token(),
            'paymentsEnabled' => Stripe::isConfigured(),
        ]);
    }

    /**
     * Validates the amount server-side, records a pending donation, and sends
     * the visitor to Stripe Checkout. The amount sent to Stripe is the one
     * validated here - nothing price-related is taken from the client as-is.
     */
    public function checkout(): void
    {
        $slug = trim((string) ($_POST['scheme'] ?? ''));
        $scheme = $slug !== '' ? (new DonationScheme())->findActiveBySlug($slug) : null;
        $backTo = $scheme !== null ? '/donate/' . $scheme['slug'] : '/donate';

        $customInput = trim((string) ($_POST['amount'] ?? ''));
        $preset = (int) ($_POST['preset'] ?? 0);
        $message = trim((string) ($_POST['message'] ?? ''));
        $old = ['amount' => $customInput, 'preset' => $preset, 'message' => $message];

        // Honeypot tripped: silently go back as if nothing happened.
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            header('Location: ' . $backTo);
            exit;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->redirectWithErrors($backTo, ['general' => 'Your session expired. Please try again.'], $old);
        }

        if ($slug !== '' && $scheme === null) {
            (new ErrorController())->notFound();

            return;
        }

        $presets = $scheme !== null ? DonationScheme::presets($scheme) : self::GENERAL_PRESETS;
        $allowCustom = $scheme === null || (int) $scheme['allow_custom_amount'] === 1;

        $errors = [];
        $amountCents = null;

        if ($customInput !== '') {
            if (!$allowCustom) {
                $errors['amount'] = 'Please choose one of the amounts shown.';
            } else {
                $amountCents = Money::parse($customInput);
                if ($amountCents === null) {
                    $errors['amount'] = 'Please enter a valid amount, e.g. 40 or 40.50.';
                }
            }
        } elseif (in_array($preset, $presets, true)) {
            $amountCents = $preset;
        } else {
            $errors['amount'] = 'Please choose or enter an amount.';
        }

        if ($amountCents !== null && ($amountCents < self::MIN_CENTS || $amountCents > self::MAX_CENTS)) {
            $errors['amount'] = 'Donations must be between ' . Money::format(self::MIN_CENTS) . ' and ' . Money::format(self::MAX_CENTS) . '.';
        }

        if (mb_strlen($message) > 500) {
            $errors['message'] = 'Please keep your message under 500 characters.';
        }

        if (!empty($errors)) {
            $this->redirectWithErrors($backTo, $errors, $old);
        }

        $client = Stripe::client();

        if ($client === null) {
            $this->redirectWithErrors($backTo, ['general' => 'Online giving is temporarily unavailable. Please contact us to make a donation.'], $old);
        }

        $donations = new Donation();
        $donationId = $donations->createPending($scheme['id'] ?? null, $amountCents, Money::CURRENCY, $message !== '' ? $message : null);
        $label = $scheme !== null ? 'Donation: ' . $scheme['name'] : 'Donation to Bloom Beyond Borders';
        $metadata = ['type' => 'donation', 'donation_id' => (string) $donationId];

        try {
            $session = $client->checkout->sessions->create([
                'mode' => 'payment',
                'submit_type' => 'donate',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => Money::CURRENCY,
                        'unit_amount' => $amountCents,
                        'product_data' => ['name' => $label],
                    ],
                ]],
                'metadata' => $metadata,
                'payment_intent_data' => ['description' => $label, 'metadata' => $metadata],
                'success_url' => Stripe::url('/donate/thank-you?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => Stripe::url($backTo),
            ]);
        } catch (ApiErrorException $e) {
            error_log('Stripe: could not create donation checkout session - ' . $e->getMessage());
            $donations->deletePending($donationId);
            $this->redirectWithErrors($backTo, ['general' => 'We couldn\'t start the payment just now. Please try again in a moment.'], $old);
        }

        $donations->attachSession($donationId, $session->id);

        header('Location: ' . $session->url, true, 303);
        exit;
    }

    /** Display-only: the webhook is what actually marks the donation paid. */
    public function thankYou(): void
    {
        $sessionId = (string) ($_GET['session_id'] ?? '');
        $donation = preg_match('/^cs_[A-Za-z0-9_]+$/', $sessionId) ? (new Donation())->findBySession($sessionId) : null;

        if ($donation === null) {
            header('Location: /donate');
            exit;
        }

        $confirmed = $donation['status'] === 'paid';

        // The redirect usually beats the webhook by a second or two - ask Stripe
        // directly so the donor sees a confirmed thank-you rather than "processing".
        if (!$confirmed && $donation['status'] === 'pending' && ($client = Stripe::client()) !== null) {
            try {
                $confirmed = $client->checkout->sessions->retrieve($sessionId)->payment_status === 'paid';
            } catch (ApiErrorException $e) {
                error_log('Stripe: could not retrieve donation session - ' . $e->getMessage());
            }
        }

        $this->view('donate/thank-you', [
            'title' => 'Thank You - Bloom Beyond Borders',
            'pageTitle' => 'Thank You',
            'noindex' => true,
            'donation' => $donation,
            'confirmed' => $confirmed,
        ]);
    }

    /** @return array{0: array, 1: array} flashed [errors, old input] */
    private function takeFlash(): array
    {
        $errors = $_SESSION['donate_errors'] ?? [];
        $old = $_SESSION['donate_old'] ?? [];
        unset($_SESSION['donate_errors'], $_SESSION['donate_old']);

        return [$errors, $old];
    }

    private function redirectWithErrors(string $backTo, array $errors, array $old): never
    {
        $_SESSION['donate_errors'] = $errors;
        $_SESSION['donate_old'] = $old;
        header('Location: ' . $backTo);
        exit;
    }
}
