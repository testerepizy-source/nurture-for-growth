<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\User;
use App\Models\ServicePromo;
use App\Services\PromoService;

/**
 * CRUD promo/diskon layanan konseling.
 * - Psikolog/Psikiater : hanya mengelola promo untuk layanan mereka sendiri.
 * - Admin/Super Admin  : dapat membuat promo untuk semua provider atau provider tertentu.
 */
class PromoController extends Controller
{
    private function isAdmin(): bool
    {
        return in_array(Auth::roleName(), ['admin', 'super_admin'], true);
    }

    private function baseRoute(): string
    {
        return $this->isAdmin() ? '/admin/promos' : '/promo';
    }

    public function index(): void
    {
        $isAdmin = $this->isAdmin();
        if ($isAdmin) {
            $promos = ServicePromo::withProvider();
            $providers = User::raw(
                "SELECT u.id, u.name, r.name AS role_name
                 FROM users u JOIN roles r ON r.id = u.role_id
                 WHERE r.name IN ('psikolog','psikiater') ORDER BY u.name"
            );
        } else {
            $promos = ServicePromo::raw(
                "SELECT sp.*, u.name AS provider_name
                 FROM service_promos sp LEFT JOIN users u ON u.id = sp.provider_id
                 WHERE sp.provider_id = ? OR sp.scope = 'all'
                 ORDER BY sp.is_active DESC, sp.start_date DESC, sp.id DESC",
                [Auth::id()]
            );
            $providers = [];
        }

        $editingId = (int) $this->input('edit', 0);
        $promo = $editingId ? ServicePromo::find($editingId) : null;
        if ($promo && !$isAdmin && (int) $promo['provider_id'] !== (int) Auth::id()) {
            Session::flash('error', 'Anda tidak berhak mengedit promo ini.');
            $this->redirect($this->baseRoute());
            return;
        }

        $this->view('promo.index', compact('promos', 'promo', 'isAdmin', 'providers'));
    }

    public function store(): void
    {
        $this->requireCsrf();
        [$data, $error] = $this->validatedData();
        if ($error) {
            Session::flash('error', $error);
            $this->redirect($this->baseRoute());
            return;
        }
        $data['created_by'] = Auth::id();
        ServicePromo::create($data);
        audit('create', 'service_promo', 'Membuat promo "' . $data['name'] . '"');
        Session::flash('success', 'Promo berhasil dibuat dan otomatis aktif sesuai periodenya.');
        $this->redirect($this->baseRoute());
    }

    public function update($id): void
    {
        $this->requireCsrf();
        $promo = ServicePromo::find((int) $id);
        if (!$promo || (!$this->isAdmin() && (int) $promo['provider_id'] !== (int) Auth::id())) {
            Session::flash('error', 'Promo tidak ditemukan.');
            $this->redirect($this->baseRoute());
            return;
        }
        [$data, $error] = $this->validatedData();
        if ($error) {
            Session::flash('error', $error);
            $this->redirect($this->baseRoute() . '?edit=' . (int) $id);
            return;
        }
        ServicePromo::update((int) $id, $data);
        audit('update', 'service_promo', 'Memperbarui promo "' . $data['name'] . '"');
        Session::flash('success', 'Promo berhasil diperbarui.');
        $this->redirect($this->baseRoute());
    }

    public function toggle($id): void
    {
        $promo = ServicePromo::find((int) $id);
        if (!$promo || (!$this->isAdmin() && (int) $promo['provider_id'] !== (int) Auth::id())) {
            Session::flash('error', 'Promo tidak ditemukan.');
            $this->redirect($this->baseRoute());
            return;
        }
        ServicePromo::update((int) $id, ['is_active' => (int) !$promo['is_active']]);
        audit('update', 'service_promo', (($promo['is_active'] ? 'Menonaktifkan' : 'Mengaktifkan')) . ' promo "' . $promo['name'] . '"');
        Session::flash('success', $promo['is_active'] ? 'Promo dinonaktifkan.' : 'Promo diaktifkan.');
        $this->redirect($this->baseRoute());
    }

    public function delete($id): void
    {
        $promo = ServicePromo::find((int) $id);
        if (!$promo || (!$this->isAdmin() && (int) $promo['provider_id'] !== (int) Auth::id())) {
            Session::flash('error', 'Promo tidak ditemukan.');
            $this->redirect($this->baseRoute());
            return;
        }
        ServicePromo::delete((int) $id);
        audit('delete', 'service_promo', 'Menghapus promo "' . $promo['name'] . '"');
        Session::flash('success', 'Promo berhasil dihapus.');
        $this->redirect($this->baseRoute());
    }

    /** @return array{0: array, 1: ?string} data valid / pesan error */
    private function validatedData(): array
    {
        $isAdmin = $this->isAdmin();

        $name = trim((string) $this->input('name', ''));
        $description = trim((string) $this->input('description', '')) ?: null;
        $discountType = $this->input('discount_type', 'percent') === 'fixed' ? 'fixed' : 'percent';
        $discountValue = (int) $this->input('discount_value', 0);
        $startMonth = trim((string) $this->input('start_month', ''));
        $endMonth = trim((string) $this->input('end_month', ''));

        if ($name === '') return [[], 'Nama promo wajib diisi.'];
        if ($discountValue <= 0) return [[], 'Nilai diskon harus lebih dari 0.'];
        if ($discountType === 'percent' && $discountValue > 100) return [[], 'Diskon persen maksimal 100%.'];
        if (!preg_match('/^\d{4}-\d{2}$/', $startMonth) || !preg_match('/^\d{4}-\d{2}$/', $endMonth)) {
            return [[], 'Bulan mulai dan bulan selesai wajib dipilih.'];
        }

        $startDate = $startMonth . '-01';
        $endDate = date('Y-m-t', strtotime($endMonth . '-01'));
        if ($startDate > $endDate) return [[], 'Bulan mulai tidak boleh setelah bulan selesai.'];

        // Cegah tumpang tindih periode pada cakupan yang sama.
        $scope = 'provider';
        $providerId = (int) Auth::id();
        if ($isAdmin) {
            $scope = $this->input('scope', 'all') === 'all' ? 'all' : 'provider';
            if ($scope === 'all') {
                $providerId = null;
            } else {
                $providerId = (int) $this->input('provider_id', 0);
                $roleRow = User::rawOne(
                    "SELECT r.name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?",
                    [$providerId]
                );
                if (!$providerId || !in_array(($roleRow['name'] ?? ''), ['psikolog', 'psikiater'], true)) {
                    return [[], 'Pilih tenaga profesional yang valid.'];
                }
            }
        }

        if (ServicePromo::rawOne(
            "SELECT id FROM service_promos
             WHERE is_active = 1 AND scope = ? AND provider_id <=> ?
               AND start_date <= ? AND end_date >= ?
               LIMIT 1",
            [$scope, $providerId, $endDate, $startDate]
        )) {
            return [[], 'Sudah ada promo aktif yang tumpang tindih dengan periode tersebut.'];
        }

        return [[
            'name' => mb_substr($name, 0, 120),
            'description' => $description ? mb_substr($description, 0, 255) : null,
            'scope' => $scope,
            'provider_id' => $providerId,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => 1,
        ], null];
    }
}
