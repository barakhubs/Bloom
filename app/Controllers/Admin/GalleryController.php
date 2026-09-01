<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Upload;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use PDO;
use RuntimeException;

class GalleryController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/gallery/index', [
            'title' => 'Gallery - Bloom Beyond Borders Admin',
            'albums' => (new GalleryAlbum())->allWithImageCounts(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function createAlbum(): void
    {
        $errors = $_SESSION['album_errors'] ?? [];
        unset($_SESSION['album_errors']);

        $this->view('admin/gallery/album_form', [
            'title' => 'Add Album - Bloom Beyond Borders Admin',
            'album' => null,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function storeAlbum(): void
    {
        $this->saveAlbum(null);
    }

    public function editAlbum(string $id): void
    {
        $album = (new GalleryAlbum())->find((int) $id);

        if ($album === null) {
            (new ErrorController())->notFound();

            return;
        }

        $errors = $_SESSION['album_errors'] ?? [];
        unset($_SESSION['album_errors']);

        $this->view('admin/gallery/album_form', [
            'title' => 'Edit Album - Bloom Beyond Borders Admin',
            'album' => $album,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function updateAlbum(string $id): void
    {
        $this->saveAlbum((int) $id);
    }

    public function destroyAlbum(string $id): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/gallery');
            exit;
        }

        $albumId = (int) $id;
        $imageModel = new GalleryImage();
        foreach ($imageModel->forAlbum($albumId) as $image) {
            Upload::delete($image['image_path']);
        }

        // gallery_images rows are removed automatically via ON DELETE CASCADE.
        (new GalleryAlbum())->delete($albumId);

        header('Location: /admin/gallery');
        exit;
    }

    public function showAlbum(string $id): void
    {
        $album = (new GalleryAlbum())->find((int) $id);

        if ($album === null) {
            (new ErrorController())->notFound();

            return;
        }

        $errors = $_SESSION['image_errors'] ?? [];
        unset($_SESSION['image_errors']);

        $this->view('admin/gallery/show', [
            'title' => htmlspecialchars($album['name']) . ' - Bloom Beyond Borders Admin',
            'album' => $album,
            'images' => (new GalleryImage())->forAlbum((int) $album['id']),
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function storeImage(string $albumId): void
    {
        $album = (new GalleryAlbum())->find((int) $albumId);

        if ($album === null) {
            (new ErrorController())->notFound();

            return;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/gallery/' . $albumId);
            exit;
        }

        $caption = trim((string) ($_POST['caption'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        $imagePath = null;
        try {
            $imagePath = Upload::store($_FILES['image'] ?? [], 'gallery', ['jpg', 'jpeg', 'png', 'webp']);
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }

        if ($imagePath === null && !isset($errors['image'])) {
            $errors['image'] = 'An image file is required.';
        }

        if (!empty($errors)) {
            $_SESSION['image_errors'] = $errors;
            header('Location: /admin/gallery/' . $albumId);
            exit;
        }

        (new GalleryImage())->create((int) $albumId, (string) $imagePath, $caption !== '' ? $caption : null, $sortOrder);

        header('Location: /admin/gallery/' . $albumId);
        exit;
    }

    public function editImage(string $albumId, string $imageId): void
    {
        $image = (new GalleryImage())->find((int) $imageId);

        if ($image === null || (int) $image['album_id'] !== (int) $albumId) {
            (new ErrorController())->notFound();

            return;
        }

        $this->view('admin/gallery/image_form', [
            'title' => 'Edit Image - Bloom Beyond Borders Admin',
            'albumId' => (int) $albumId,
            'image' => $image,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function updateImage(string $albumId, string $imageId): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/gallery/' . $albumId);
            exit;
        }

        $image = (new GalleryImage())->find((int) $imageId);
        if ($image === null || (int) $image['album_id'] !== (int) $albumId) {
            (new ErrorController())->notFound();

            return;
        }

        $caption = trim((string) ($_POST['caption'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        (new GalleryImage())->update((int) $imageId, $caption !== '' ? $caption : null, $sortOrder);

        header('Location: /admin/gallery/' . $albumId);
        exit;
    }

    public function destroyImage(string $albumId, string $imageId): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/gallery/' . $albumId);
            exit;
        }

        $model = new GalleryImage();
        $image = $model->find((int) $imageId);

        if ($image !== null && (int) $image['album_id'] === (int) $albumId) {
            $model->delete((int) $imageId);
            Upload::delete($image['image_path']);
        }

        header('Location: /admin/gallery/' . $albumId);
        exit;
    }

    private function saveAlbum(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/gallery/create' : "/admin/gallery/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        if (!empty($errors)) {
            $_SESSION['album_errors'] = $errors;
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new GalleryAlbum();
        $slug = $this->uniqueSlug($this->slugify($name), $id);

        if ($id === null) {
            $model->create($name, $slug, $sortOrder);
        } else {
            $model->update($id, $name, $slug, $sortOrder);
        }

        header('Location: /admin/gallery');
        exit;
    }

    private function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'album';
    }

    /**
     * Appends -2, -3, ... if the slug collides with another album's (excluding
     * the album currently being edited) - gallery_albums.slug is UNIQUE.
     */
    private function uniqueSlug(string $baseSlug, ?int $excludeId): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (true) {
            $stmt = $this->db()->prepare(
                'SELECT id FROM gallery_albums WHERE slug = ? AND id != ?'
            );
            $stmt->execute([$slug, $excludeId ?? 0]);

            if ($stmt->fetch() === false) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }

    private function db(): PDO
    {
        return Database::connection();
    }
}
