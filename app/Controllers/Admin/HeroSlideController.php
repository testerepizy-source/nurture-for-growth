<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\HeroSlide;

class HeroSlideController extends Controller
{
    private const MAX_SLIDES = 4;
    private ?string $uploadError = null;

    public function index(): void
    {
        $slides = HeroSlide::allOrdered();
        $canAddMore = count($slides) < self::MAX_SLIDES;
        $this->view('admin.hero_slides.index', compact('slides', 'canAddMore'));
    }

    public function create(): void
    {
        if (HeroSlide::count() >= self::MAX_SLIDES) {
            Session::flash('error', 'Maksimal ' . self::MAX_SLIDES . ' slide. Hapus salah satu slide dahulu untuk menambah yang baru.');
            $this->redirect('/admin/hero-slides');
        }
        $this->view('admin.hero_slides.form', ['slide' => null]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        if (HeroSlide::count() >= self::MAX_SLIDES) {
            Session::flash('error', 'Maksimal ' . self::MAX_SLIDES . ' slide. Hapus salah satu slide dahulu untuk menambah yang baru.');
            $this->redirect('/admin/hero-slides');
        }

        $imagePath = $this->handleCroppedImage();
        if ($this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/admin/hero-slides/create');
        }

        $maxOrder = (int) (HeroSlide::rawOne('SELECT MAX(order_no) AS m FROM hero_slides')['m'] ?? 0);

        HeroSlide::create([
            'badge_text'  => $this->input('badge_text'),
            'title'       => $this->input('title'),
            'subtitle'    => $this->input('subtitle'),
            'image_path'  => $imagePath,
            'cta_text'    => $this->input('cta_text', 'Mulai Sekarang'),
            'cta_link'    => $this->input('cta_link', '/register'),
            'color_theme' => $this->input('color_theme', 'teal'),
            'order_no'    => $maxOrder + 1,
            'is_active'   => (int) $this->input('is_active', 1),
        ]);

        audit('create', 'hero_slide', 'Menambahkan hero slide: ' . $this->input('title'));
        Session::flash('success', 'Hero slide berhasil ditambahkan.');
        $this->redirect('/admin/hero-slides');
    }

    public function edit(array $params): void
    {
        $slide = HeroSlide::find((int) $params['id']);
        if (!$slide) { http_response_code(404); require __DIR__ . '/../../Views/errors/404.php'; return; }
        $this->view('admin.hero_slides.form', ['slide' => $slide]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $slide = HeroSlide::find((int) $params['id']);
        if (!$slide) { http_response_code(404); require __DIR__ . '/../../Views/errors/404.php'; return; }

        $imagePath = $this->handleCroppedImage();
        if ($this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/admin/hero-slides/' . $slide['id'] . '/edit');
        }

        $data = [
            'badge_text'  => $this->input('badge_text'),
            'title'       => $this->input('title'),
            'subtitle'    => $this->input('subtitle'),
            'cta_text'    => $this->input('cta_text', 'Mulai Sekarang'),
            'cta_link'    => $this->input('cta_link', '/register'),
            'color_theme' => $this->input('color_theme', 'teal'),
            'is_active'   => (int) $this->input('is_active', 1),
        ];
        if ($imagePath) {
            $data['image_path'] = $imagePath;
            if (!empty($slide['image_path']) && str_starts_with($slide['image_path'], 'assets/uploads/hero/')) {
                $old = __DIR__ . '/../../../public/' . $slide['image_path'];
                if (is_file($old)) @unlink($old);
            }
        }

        HeroSlide::update($slide['id'], $data);
        audit('update', 'hero_slide', 'Mengubah hero slide ID ' . $slide['id']);
        Session::flash('success', 'Hero slide berhasil diperbarui.');
        $this->redirect('/admin/hero-slides');
    }

    public function delete(array $params): void
    {
        $this->requireCsrf();
        $slide = HeroSlide::find((int) $params['id']);
        if ($slide) {
            if (!empty($slide['image_path']) && str_starts_with($slide['image_path'], 'assets/uploads/hero/')) {
                $old = __DIR__ . '/../../../public/' . $slide['image_path'];
                if (is_file($old)) @unlink($old);
            }
            HeroSlide::delete($slide['id']);
            audit('delete', 'hero_slide', 'Menghapus hero slide ID ' . $slide['id']);
        }
        Session::flash('success', 'Hero slide berhasil dihapus.');
        $this->redirect('/admin/hero-slides');
    }

    public function moveUp(array $params): void
    {
        $this->requireCsrf();
        $this->swapOrder((int) $params['id'], -1);
        $this->redirect('/admin/hero-slides');
    }

    public function moveDown(array $params): void
    {
        $this->requireCsrf();
        $this->swapOrder((int) $params['id'], 1);
        $this->redirect('/admin/hero-slides');
    }

    private function swapOrder(int $id, int $direction): void
    {
        $slides = HeroSlide::allOrdered();
        $index = null;
        foreach ($slides as $i => $s) { if ((int) $s['id'] === $id) { $index = $i; break; } }
        if ($index === null) return;
        $swapIndex = $index + $direction;
        if ($swapIndex < 0 || $swapIndex >= count($slides)) return;

        $a = $slides[$index];
        $b = $slides[$swapIndex];
        HeroSlide::update($a['id'], ['order_no' => $b['order_no']]);
        HeroSlide::update($b['id'], ['order_no' => $a['order_no']]);
    }

    /**
     * Terima gambar hasil crop dari Cropper.js (dikirim sebagai data URL base64
     * lewat hidden input "cropped_image"), decode, validasi, dan simpan sebagai file.
     * Mengembalikan null jika tidak ada gambar baru yang di-crop (mis. saat edit
     * tanpa mengganti foto).
     */
    private function handleCroppedImage(): ?string
    {
        $dataUrl = $this->input('cropped_image');
        if (empty($dataUrl)) return null;

        if (!preg_match('/^data:image\/(jpeg|png|webp);base64,(.+)$/', $dataUrl, $m)) {
            $this->uploadError = 'Format gambar hasil crop tidak valid.';
            return null;
        }
        $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $binary = base64_decode($m[2]);
        if ($binary === false) {
            $this->uploadError = 'Gagal memproses gambar hasil crop.';
            return null;
        }
        if (strlen($binary) > 3 * 1024 * 1024) {
            $this->uploadError = 'Ukuran gambar maksimal 3MB setelah crop.';
            return null;
        }

        $dir = __DIR__ . '/../../../public/assets/uploads/hero';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $filename = 'hero-' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destination = $dir . '/' . $filename;

        if (file_put_contents($destination, $binary) === false) {
            $this->uploadError = 'Gagal menyimpan gambar ke server.';
            return null;
        }

        return 'assets/uploads/hero/' . $filename;
    }
}
