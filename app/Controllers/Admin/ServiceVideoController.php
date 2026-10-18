<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\ServiceVideo;

/**
 * Kelola Video Layanan yang tampil di halaman utama.
 * Sumber video: URL YouTube/Vimeo, URL file video langsung, atau unggahan MP4.
 */
class ServiceVideoController extends Controller
{
    private const MAX_VIDEOS = 12;
    private const MAX_VIDEO_SIZE = 32 * 1024 * 1024; // 32MB
    private const MAX_THUMB_SIZE = 3 * 1024 * 1024;  // 3MB
    private ?string $uploadError = null;

    public function index(): void
    {
        $videos = ServiceVideo::allOrdered();
        $canAddMore = count($videos) < self::MAX_VIDEOS;
        $this->view('admin.service_videos.index', compact('videos', 'canAddMore'));
    }

    public function create(): void
    {
        if (ServiceVideo::count() >= self::MAX_VIDEOS) {
            Session::flash('error', 'Maksimal ' . self::MAX_VIDEOS . ' video. Hapus salah satu video dahulu untuk menambah yang baru.');
            $this->redirect('/admin/service-videos');
            return;
        }
        $this->view('admin.service_videos.form', ['video' => null]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        if (ServiceVideo::count() >= self::MAX_VIDEOS) {
            Session::flash('error', 'Maksimal ' . self::MAX_VIDEOS . ' video.');
            $this->redirect('/admin/service-videos');
            return;
        }

        [$data, $error] = $this->validatedData(withFiles: true);
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/admin/service-videos/create');
            return;
        }

        $maxOrder = (int) (ServiceVideo::rawOne('SELECT MAX(order_no) AS m FROM service_videos')['m'] ?? 0);
        $data['order_no'] = $maxOrder + 1;
        $data['created_by'] = Auth::id();

        ServiceVideo::create($data);
        audit('create', 'service_video', 'Menambahkan video layanan: ' . $data['title']);
        Session::flash('success', 'Video layanan berhasil ditambahkan.');
        $this->redirect('/admin/service-videos');
    }

    public function edit(array $params): void
    {
        $video = ServiceVideo::find((int) $params['id']);
        if (!$video) { http_response_code(404); require __DIR__ . '/../../Views/errors/404.php'; return; }
        $this->view('admin.service_videos.form', ['video' => $video]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $video = ServiceVideo::find((int) $params['id']);
        if (!$video) { http_response_code(404); require __DIR__ . '/../../Views/errors/404.php'; return; }

        [$data, $error] = $this->validatedData(withFiles: true, existing: $video);
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/admin/service-videos/' . $video['id'] . '/edit');
            return;
        }

        // Hapus file lama jika diganti
        foreach (['video_path' => 'video', 'thumbnail_path' => 'thumb'] as $field => $kind) {
            if (!empty($data[$field]) && !empty($video[$field]) && $data[$field] !== $video[$field]) {
                $this->deleteFile($video[$field]);
            }
            if (!array_key_exists($field, $data)) {
                unset($data[$field]); // tidak ada file baru, pertahankan lama
            }
        }

        ServiceVideo::update((int) $video['id'], $data);
        audit('update', 'service_video', 'Mengubah video layanan ID ' . $video['id']);
        Session::flash('success', 'Video layanan berhasil diperbarui.');
        $this->redirect('/admin/service-videos');
    }

    public function delete(array $params): void
    {
        $this->requireCsrf();
        $video = ServiceVideo::find((int) $params['id']);
        if ($video) {
            if (!empty($video['video_path'])) $this->deleteFile($video['video_path']);
            if (!empty($video['thumbnail_path'])) $this->deleteFile($video['thumbnail_path']);
            ServiceVideo::delete((int) $video['id']);
            audit('delete', 'service_video', 'Menghapus video layanan ID ' . (int) $params['id']);
        }
        Session::flash('success', 'Video layanan berhasil dihapus.');
        $this->redirect('/admin/service-videos');
    }

    public function toggle(array $params): void
    {
        $this->requireCsrf();
        $video = ServiceVideo::find((int) $params['id']);
        if ($video) {
            ServiceVideo::update((int) $video['id'], ['is_active' => (int) !$video['is_active']]);
            audit('update', 'service_video', (($video['is_active'] ? 'Menonaktifkan' : 'Mengaktifkan')) . ' video layanan ID ' . $video['id']);
        }
        $this->redirect('/admin/service-videos');
    }

    public function moveUp(array $params): void
    {
        $this->requireCsrf();
        $this->swapOrder((int) $params['id'], -1);
        $this->redirect('/admin/service-videos');
    }

    public function moveDown(array $params): void
    {
        $this->requireCsrf();
        $this->swapOrder((int) $params['id'], 1);
        $this->redirect('/admin/service-videos');
    }

    private function swapOrder(int $id, int $direction): void
    {
        $videos = ServiceVideo::allOrdered();
        $index = null;
        foreach ($videos as $i => $v) { if ((int) $v['id'] === $id) { $index = $i; break; } }
        if ($index === null) return;
        $swapIndex = $index + $direction;
        if ($swapIndex < 0 || $swapIndex >= count($videos)) return;

        $a = $videos[$index];
        $b = $videos[$swapIndex];

        // Hindari nilai order yang sama persis sebelum ditukar.
        if ((int) $a['order_no'] === (int) $b['order_no']) {
            $nudged = (int) $a['order_no'] + ($direction > 0 ? 1 : -1);
            ServiceVideo::update((int) $b['id'], ['order_no' => $nudged]);
            $b['order_no'] = (string) $nudged;
        }

        ServiceVideo::update((int) $a['id'], ['order_no' => (int) $b['order_no']]);
        ServiceVideo::update((int) $b['id'], ['order_no' => (int) $a['order_no']]);
    }

    /** @return array{0: array, 1: ?string} */
    private function validatedData(bool $withFiles = false, ?array $existing = null): array
    {
        $title = trim((string) $this->input('title', ''));
        if ($title === '') return [[], 'Judul video wajib diisi.'];

        $videoUrl = trim((string) $this->input('video_url', ''));

        $videoPath = null;
        $thumbPath = null;
        if ($withFiles) {
            if (!empty($_FILES['video_file']['name'])) {
                $videoPath = $this->handleVideoUpload();
                if ($this->uploadError) return [[], $this->uploadError];
                $videoUrl = ''; // file unggahan menggantikan URL
            } elseif ($videoUrl === '' && empty($existing['video_url']) && empty($existing['video_path'])) {
                return [[], 'Isi URL video atau unggah file MP4.'];
            }

            if (!empty($_FILES['thumbnail_file']['name'])) {
                $thumbPath = $this->handleThumbnailUpload();
                if ($this->uploadError) return [[], $this->uploadError];
            }
        }

        if ($videoUrl !== '') {
            $isEmbeddable = ServiceVideo::youtubeId($videoUrl) || ServiceVideo::vimeoId($videoUrl);
            $isDirectFile = (bool) preg_match('#^https?://.+\.(mp4|webm|ogg)(\?.*)?$#i', $videoUrl);
            if (!$isEmbeddable && !$isDirectFile) {
                return [[], 'URL video harus dari YouTube, Vimeo, atau tautan langsung file MP4/WebM.'];
            }
        }

        $data = [
            'title'       => mb_substr($title, 0, 150),
            'badge_text'  => mb_substr(trim((string) $this->input('badge_text', '')), 0, 50) ?: null,
            'description' => mb_substr(trim((string) $this->input('description', '')), 0, 500) ?: null,
            'is_active'   => $this->input('is_active', '1') === '1' ? 1 : 0,
        ];

        if ($withFiles && $videoPath !== null) {
            // Unggahan file menggantikan sumber video lama.
            $data['video_url'] = null;
            $data['video_path'] = $videoPath;
        } elseif ($videoUrl !== '') {
            $data['video_url'] = mb_substr($videoUrl, 0, 255);
        } elseif (!$existing) {
            return [[], 'Isi URL video atau unggah file MP4.'];
        } else {
            // Edit tanpa mengubah sumber video: pertahankan nilai lama.
        }

        if ($withFiles && $thumbPath !== null) {
            $data['thumbnail_path'] = $thumbPath;
        }
        return [$data, null];
    }

    private function handleVideoUpload(): ?string
    {
        $file = $_FILES['video_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = $file['error'] === UPLOAD_ERR_INI_SIZE
                ? 'Ukuran video melebihi batas server (maks 32MB). Gunakan YouTube untuk video panjang.'
                : 'Gagal mengunggah video. Silakan coba lagi.';
            return null;
        }
        if ($file['size'] > self::MAX_VIDEO_SIZE) {
            $this->uploadError = 'Ukuran video maksimal 32MB. Gunakan YouTube/Vimeo untuk video panjang.';
            return null;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp4', 'webm'], true)) {
            $this->uploadError = 'Format video harus MP4 atau WebM.';
            return null;
        }
        return $this->saveUploaded($file['tmp_name'], 'video-' . bin2hex(random_bytes(6)) . '.' . $ext);
    }

    private function handleThumbnailUpload(): ?string
    {
        $file = $_FILES['thumbnail_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Gagal mengunggah thumbnail.';
            return null;
        }
        if ($file['size'] > self::MAX_THUMB_SIZE) {
            $this->uploadError = 'Ukuran thumbnail maksimal 3MB.';
            return null;
        }
        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowedMime[$mime])) {
            $this->uploadError = 'Format thumbnail harus JPG, PNG, atau WebP.';
            return null;
        }
        return $this->saveUploaded($file['tmp_name'], 'vthumb-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime]);
    }

    private function saveUploaded(string $tmpName, string $filename): ?string
    {
        $dir = __DIR__ . '/../../../public/assets/uploads/videos';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        if (!move_uploaded_file($tmpName, $dir . '/' . $filename)) {
            $this->uploadError = 'Gagal menyimpan file ke server.';
            return null;
        }
        return 'assets/uploads/videos/' . $filename;
    }

    private function deleteFile(string $relativePath): void
    {
        if (!str_starts_with($relativePath, 'assets/uploads/videos/')) return;
        $path = __DIR__ . '/../../../public/' . $relativePath;
        if (is_file($path)) @unlink($path);
    }
}
