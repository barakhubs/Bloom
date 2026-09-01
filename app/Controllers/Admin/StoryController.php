<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Models\Story;

class StoryController extends AdminController
{
    public function index(): void
    {
        $errors = $_SESSION['story_errors'] ?? [];
        $success = $_SESSION['story_success'] ?? false;
        unset($_SESSION['story_errors'], $_SESSION['story_success']);

        $this->view('admin/story/index', [
            'title' => 'Our Story Content - Bloom Beyond Borders Admin',
            'stats' => Story::stats(),
            'financeEntries' => Story::allFinanceEntries(),
            'boardLetter' => Story::boardLetter(),
            'errors' => $errors,
            'success' => $success,
            'csrfToken' => Csrf::token(),
        ]);
    }

    // --------------------------------------------------
    // Impact stats
    // --------------------------------------------------

    public function createStat(): void
    {
        $this->view('admin/story/stat_form', [
            'title' => 'Add Stat - Bloom Beyond Borders Admin',
            'stat' => null,
            'errors' => $_SESSION['stat_errors'] ?? [],
            'csrfToken' => Csrf::token(),
        ]);
        unset($_SESSION['stat_errors']);
    }

    public function storeStat(): void
    {
        $this->saveStat(null);
    }

    public function editStat(string $id): void
    {
        $stat = Story::findStat((int) $id);

        if ($stat === null) {
            (new ErrorController())->notFound();

            return;
        }

        $this->view('admin/story/stat_form', [
            'title' => 'Edit Stat - Bloom Beyond Borders Admin',
            'stat' => $stat,
            'errors' => $_SESSION['stat_errors'] ?? [],
            'csrfToken' => Csrf::token(),
        ]);
        unset($_SESSION['stat_errors']);
    }

    public function updateStat(string $id): void
    {
        $this->saveStat((int) $id);
    }

    public function destroyStat(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            Story::deleteStat((int) $id);
        }

        header('Location: /admin/story');
        exit;
    }

    // --------------------------------------------------
    // Financial allocation entries
    // --------------------------------------------------

    public function createFinance(): void
    {
        $this->view('admin/story/finance_form', [
            'title' => 'Add Financial Entry - Bloom Beyond Borders Admin',
            'entry' => null,
            'errors' => $_SESSION['finance_errors'] ?? [],
            'csrfToken' => Csrf::token(),
        ]);
        unset($_SESSION['finance_errors']);
    }

    public function storeFinance(): void
    {
        $this->saveFinance(null);
    }

    public function editFinance(string $id): void
    {
        $entry = Story::findFinanceEntry((int) $id);

        if ($entry === null) {
            (new ErrorController())->notFound();

            return;
        }

        $this->view('admin/story/finance_form', [
            'title' => 'Edit Financial Entry - Bloom Beyond Borders Admin',
            'entry' => $entry,
            'errors' => $_SESSION['finance_errors'] ?? [],
            'csrfToken' => Csrf::token(),
        ]);
        unset($_SESSION['finance_errors']);
    }

    public function updateFinance(string $id): void
    {
        $this->saveFinance((int) $id);
    }

    public function destroyFinance(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            Story::deleteFinanceEntry((int) $id);
        }

        header('Location: /admin/story');
        exit;
    }

    // --------------------------------------------------
    // Board letter (singleton)
    // --------------------------------------------------

    public function updateLetter(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/story');
            exit;
        }

        $authorName = trim((string) ($_POST['author_name'] ?? ''));
        $authorTitle = trim((string) ($_POST['author_title'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));

        if ($authorName === '' || $body === '') {
            $_SESSION['story_errors'] = ['letter' => 'Author name and letter body are required.'];
            header('Location: /admin/story');
            exit;
        }

        Story::updateBoardLetter($authorName, $authorTitle !== '' ? $authorTitle : null, $body);

        $_SESSION['story_success'] = true;
        header('Location: /admin/story');
        exit;
    }

    private function saveStat(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/story/stats/create' : "/admin/story/stats/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $label = trim((string) ($_POST['label'] ?? ''));
        $value = trim((string) ($_POST['value'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($label === '') {
            $errors['label'] = 'Label is required.';
        }
        if ($value === '') {
            $errors['value'] = 'Value is required.';
        }

        if (!empty($errors)) {
            $_SESSION['stat_errors'] = $errors;
            header('Location: ' . $redirectTo);
            exit;
        }

        if ($id === null) {
            Story::createStat($label, $value, $sortOrder);
        } else {
            Story::updateStat($id, $label, $value, $sortOrder);
        }

        header('Location: /admin/story');
        exit;
    }

    private function saveFinance(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/story/finance/create' : "/admin/story/finance/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $year = (int) ($_POST['year'] ?? 0);
        $category = trim((string) ($_POST['category'] ?? ''));
        $percentage = (float) ($_POST['percentage'] ?? 0);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($year < 2000 || $year > 2100) {
            $errors['year'] = 'Enter a valid year.';
        }
        if ($category === '') {
            $errors['category'] = 'Category is required.';
        }
        if ($percentage < 0 || $percentage > 100) {
            $errors['percentage'] = 'Percentage must be between 0 and 100.';
        }

        if (!empty($errors)) {
            $_SESSION['finance_errors'] = $errors;
            header('Location: ' . $redirectTo);
            exit;
        }

        if ($id === null) {
            Story::createFinanceEntry($year, $category, $percentage, $sortOrder);
        } else {
            Story::updateFinanceEntry($id, $year, $category, $percentage, $sortOrder);
        }

        header('Location: /admin/story');
        exit;
    }
}
