<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\PaymentChannel;

/**
 * Kelola kanal pembayaran manual (Transfer Bank & QRIS) yang ditampilkan ke client.
 */
class PaymentChannelController extends Controller
{
    private const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB
    private ?string $uploadError = null;

    public function index(): void
    {
        $channels = PaymentChannel::allOrdered();
        $this->view('admin.payment_channels.index', compact('channels'));
    }

    public function create(): void
    {
        $this->view('admin.payment_channels.form', ['channel' => null]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        [$data, $error] = $this->validatedData();
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/admin/payment-channels/create');
            return;
        }
        $maxOrder = (int) (PaymentChannel::rawOne('SELECT MAX(sort_order) AS m FROM payment_channels')['m'] ?? 0);
        $data['sort_order'] = $maxOrder + 1;

        PaymentChannel::create($data);
        audit('create', 'payment_channel', 'Menambahkan kanal pembayaran: ' . $data['label']);
        Session::flash('success', 'Kanal pembayaran berhasil ditambahkan.');
        $this->redirect('/admin/payment-channels');
    }

    public function edit(array $params): void
    {
        $channel = PaymentChannel::find((int) $params['id']);
        if (!$channel) { http_response_code(404); require __DIR__ . '/../../Views/errors/404.php'; return; }
        $this->view('admin.payment_channels.form', ['channel' => $channel]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $channel = PaymentChannel::find((int) $params['id']);
        if (!$channel) { http_response_code(404); require __DIR__ . '/../../Views/errors/404.php'; return; }

        [$data, $error] = $this->validatedData($channel);
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/admin/payment-channels/' . $channel['id'] . '/edit');
            return;
        }

        if (!empty($data['image_path']) && !empty($channel['image_path']) && $data['image_path'] !== $channel['image_path']) {
            $this->deleteImage($channel['image_path']);
        } elseif (!array_key_exists('image_path', $data)) {
            unset($data['image_path']); // pertahankan gambar lama saat edit tanpa ganti file
        }

        PaymentChannel::update((int) $channel['id'], $data);
        audit('update', 'payment_channel', 'Mengubah kanal pembayaran ID ' . $channel['id']);
        Session::flash('success', 'Kanal pembayaran berhasil diperbarui.');
        $this->redirect('/admin/payment-channels');
    }

    public function toggle(array $params): void
    {
        $this->requireCsrf();
        $channel = PaymentChannel::find((int) $params['id']);
        if ($channel) {
            PaymentChannel::update((int) $channel['id'], ['is_active' => (int) !$channel['is_active']]);
            audit('update', 'payment_channel', (($channel['is_active'] ? 'Menonaktifkan' : 'Mengaktifkan')) . ' kanal pembayaran ID ' . $channel['id']);
        }
        $this->redirect('/admin/payment-channels');
    }

    public function delete(array $params): void
    {
        $this->requireCsrf();
        $channel = PaymentChannel::find((int) $params['id']);
        if ($channel) {
            if (!empty($channel['image_path'])) $this->deleteImage($channel['image_path']);
            PaymentChannel::delete((int) $channel['id']);
            audit('delete', 'payment_channel', 'Menghapus kanal pembayaran ID ' . (int) $params['id']);
        }
        Session::flash('success', 'Kanal pembayaran berhasil dihapus.');
        $this->redirect('/admin/payment-channels');
    }

    public function moveUp(array $params): void
    {
        $this->requireCsrf();
        $this->swapOrder((int) $params['id'], -1);
        $this->redirect('/admin/payment-channels');
    }

    public function moveDown(array $params): void
    {
        $this->requireCsrf();
        $this->swapOrder((int) $params['id'], 1);
        $this->redirect('/admin/payment-channels');
    }

    private function swapOrder(int $id, int $direction): void
    {
        $channel = PaymentChannel::find($id);
        if (!$channel) return;
        // Hanya tukar urutan dengan kanal bertipe sama
        $sameType = array_values(array_filter(
            PaymentChannel::allOrdered(),
            fn($c) => $c['type'] === $channel['type']
        ));
        $index = null;
        foreach ($sameType as $i => $c) { if ((int) $c['id'] === $id) { $index = $i; break; } }
        if ($index === null) return;
        $swapIndex = $index + $direction;
        if ($swapIndex < 0 || $swapIndex >= count($sameType)) return;

        $a = $sameType[$index];
        $b = $sameType[$swapIndex];
        if ((int) $a['sort_order'] === (int) $b['sort_order']) {
            $nudged = (int) $a['sort_order'] + ($direction > 0 ? 1 : -1);
            PaymentChannel::update((int) $b['id'], ['sort_order' => $nudged]);
            $b['sort_order'] = (string) $nudged;
        }
        PaymentChannel::update((int) $a['id'], ['sort_order' => (int) $b['sort_order']]);
        PaymentChannel::update((int) $b['id'], ['sort_order' => (int) $a['sort_order']]);
    }

    /** @return array{0: array, 1: ?string} */
    private function validatedData(?array $existing = null): array
    {
        $type = $this->input('type', 'bank') === 'qris' ? 'qris' : 'bank';
        $label = trim((string) $this->input('label', ''));
        if ($label === '') return [[], 'Nama/Label wajib diisi.'];

        $bankName = trim((string) $this->input('bank_name', ''));
        $accountNumber = trim((string) $this->input('account_number', ''));
        $accountName = trim((string) $this->input('account_name', ''));

        if ($type === 'bank') {
            if ($bankName === '' || $accountNumber === '') {
                return [[], 'Nama bank dan nomor rekening wajib diisi untuk kanal Transfer Bank.'];
            }
        }

        $imagePath = null;
        if (!empty($_FILES['image_file']['name'])) {
            $imagePath = $this->handleImageUpload($type);
            if ($this->uploadError) return [[], $this->uploadError];
        }

        $data = [
            'type'          => $type,
            'label'         => mb_substr($label, 0, 100),
            'bank_name'     => $type === 'bank' ? (mb_substr($bankName, 0, 80) ?: null) : null,
            'account_number'=> $type === 'bank' ? (mb_substr($accountNumber, 0, 60) ?: null) : null,
            'account_name'  => mb_substr($accountName, 0, 100) ?: null,
            'instructions'  => trim((string) $this->input('instructions', '')) ?: null,
            // Checkbox hanya terkirim saat dicentang
            'is_active'     => $this->input('is_active', '0') === '1' ? 1 : 0,
        ];

        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        } elseif (!$existing) {
            if ($type === 'qris') return [[], 'Gambar QRIS wajib diunggah untuk kanal QRIS.'];
            $data['image_path'] = null;
        }

        return [$data, null];
    }

    private function handleImageUpload(string $prefix): ?string
    {
        $file = $_FILES['image_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Gagal mengunggah gambar.';
            return null;
        }
        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            $this->uploadError = 'Ukuran gambar maksimal 2MB.';
            return null;
        }
        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowedMime[$mime])) {
            $this->uploadError = 'Format gambar harus JPG, PNG, atau WebP.';
            return null;
        }
        $dir = __DIR__ . '/../../../public/assets/uploads/payments';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $filename = $prefix === 'qris' ? 'qris-' : 'bank-';
        $filename .= bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            $this->uploadError = 'Gagal menyimpan gambar ke server.';
            return null;
        }
        return 'assets/uploads/payments/' . $filename;
    }

    private function deleteImage(string $relativePath): void
    {
        if (!str_starts_with($relativePath, 'assets/uploads/payments/')) return;
        $path = __DIR__ . '/../../../public/' . $relativePath;
        if (is_file($path)) @unlink($path);
    }
}
