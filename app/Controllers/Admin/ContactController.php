<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Csrf;
use App\Models\ContactSubmission;

class ContactController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/contact/index', [
            'title' => 'Contact Submissions - Bloom Beyond Borders Admin',
            'submissions' => (new ContactSubmission())->all(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function destroy(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            (new ContactSubmission())->delete((int) $id);
        }

        header('Location: /admin/contact');
        exit;
    }
}
