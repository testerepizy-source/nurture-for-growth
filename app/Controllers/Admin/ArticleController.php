<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use Core\Auth;
use App\Models\Article;
use App\Models\ArticleCategory;

class ArticleController extends Controller
{
    public function index(): void
    {
        $articles = Article::raw("SELECT a.*, c.name AS category_name FROM articles a LEFT JOIN article_categories c ON c.id = a.category_id ORDER BY a.id DESC");
        $this->view('admin.articles.index', ['articles' => $articles]);
    }

    public function create(): void
    {
        $categories = ArticleCategory::all('name ASC');
        $this->view('admin.articles.form', ['article' => null, 'categories' => $categories]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $title = $this->input('title');
        $thumbnailPath = $this->handleThumbnailUpload();
        if ($this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/admin/articles/create');
        }

        Article::create([
            'category_id' => (int) $this->input('category_id') ?: null,
            'author_id'   => Auth::id(),
            'type'        => $this->input('type', 'article'),
            'title'       => $title,
            'slug'        => $this->slugify($title),
            'excerpt'     => $this->input('excerpt'),
            'content'     => $this->input('content'),
            'media_url'   => $this->input('media_url'),
            'thumbnail'   => $thumbnailPath,
            'tag'         => $this->input('tag'),
            'is_published'=> (int) $this->input('is_published', 1),
        ]);
        audit('create', 'article', 'Menambahkan artikel: ' . $title);
        Session::flash('success', 'Artikel berhasil ditambahkan.');
        $this->redirect('/admin/articles');
    }

    public function edit(array $params): void
    {
        $article = Article::find((int) $params['id']);
        $categories = ArticleCategory::all('name ASC');
        $this->view('admin.articles.form', ['article' => $article, 'categories' => $categories]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $article = Article::find((int) $params['id']);
        $thumbnailPath = $this->handleThumbnailUpload();
        if ($this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/admin/articles/' . $params['id'] . '/edit');
        }

        $data = [
            'category_id' => (int) $this->input('category_id') ?: null,
            'type'        => $this->input('type', 'article'),
            'title'       => $this->input('title'),
            'excerpt'     => $this->input('excerpt'),
            'content'     => $this->input('content'),
            'media_url'   => $this->input('media_url'),
            'tag'         => $this->input('tag'),
            'is_published'=> (int) $this->input('is_published', 1),
        ];
        if ($thumbnailPath) {
            $data['thumbnail'] = $thumbnailPath;
            if (!empty($article['thumbnail']) && str_starts_with($article['thumbnail'], 'assets/uploads/articles/')) {
                $old = __DIR__ . '/../../../public/' . $article['thumbnail'];
                if (is_file($old)) @unlink($old);
            }
        }

        Article::update((int) $params['id'], $data);
        audit('update', 'article', 'Mengubah artikel ID ' . $params['id']);
        Session::flash('success', 'Artikel berhasil diperbarui.');
        $this->redirect('/admin/articles');
    }

    public function delete(array $params): void
    {
        Article::delete((int) $params['id']);
        audit('delete', 'article', 'Menghapus artikel ID ' . $params['id']);
        $this->redirect('/admin/articles');
    }

    private ?string $uploadError = null;

    private function handleThumbnailUpload(): ?string
    {
        if (empty($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES['thumbnail'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Gagal mengunggah foto. Silakan coba lagi.';
            return null;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->uploadError = 'Ukuran foto maksimal 2MB.';
            return null;
        }

        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowedMime[$mime])) {
            $this->uploadError = 'Format foto harus JPG, PNG, atau WEBP.';
            return null;
        }

        $dir = __DIR__ . '/../../../public/assets/uploads/articles';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $filename = 'article-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        $destination = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->uploadError = 'Gagal menyimpan foto ke server.';
            return null;
        }

        return 'assets/uploads/articles/' . $filename;
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-') . '-' . substr(md5(uniqid()), 0, 5);
    }
}
