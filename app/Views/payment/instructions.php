<?php
/** @var string $referenceType  @var string $description  @var int $amount */
/** @var array $banks  @var array $qrisChannels  @var string $formAction  @var string $backUrl  @var string $selectedMethod */
$banks = is_array($banks ?? null) ? $banks : [];
$qrisChannels = is_array($qrisChannels ?? null) ? $qrisChannels : [];
$hasChannels = !empty($banks) || !empty($qrisChannels);
$amountFormatted = 'Rp ' . number_format((int)($amount ?? 0), 0, ',', '.');
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-2"><i class="fas fa-file-invoice-dollar me-1"></i>Pembayaran Manual</span>
                            <h2 class="fw-bold mb-1">Selesaikan pembayaran Anda</h2>
                            <p class="text-muted mb-0"><?= e($description ?? '') ?></p>
                        </div>
                        <div class="text-md-end">
                            <div class="small text-muted">Total tagihan</div>
                            <div class="h3 fw-bold text-success mb-0"><?= $amountFormatted ?></div>
                        </div>
                    </div>
                    <hr class="my-4">

                    <?php if (!$hasChannels): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-circle-exclamation me-1"></i> Belum ada rekening/QRIS pembayaran yang tersedia saat ini.
                            Silakan hubungi admin klinik untuk menyelesaikan pembayaran.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($banks)): ?>
                        <h5 class="fw-bold mb-3"><i class="fas fa-building-columns text-primary me-2"></i>Transfer Bank</h5>
                        <?php foreach ($banks as $bank): ?>
                            <div class="rounded-4 border p-3 mb-3 bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-semibold">
                                        <?= e($bank['bank_name'] ?: $bank['label']) ?>
                                        <?php if (!empty($bank['account_name'])): ?>
                                            <span class="text-muted small d-block">a.n. <?= e($bank['account_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($bank['image_path'])): ?>
                                        <img src="<?= base_url($bank['image_path']) ?>" alt="<?= e($bank['label']) ?>" style="height:34px;object-fit:contain;" loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($bank['account_number'])): ?>
                                    <div class="d-flex align-items-center justify-content-between bg-white border rounded-3 px-3 py-2">
                                        <span class="font-monospace fw-bold" style="letter-spacing:.08em;"><?= e($bank['account_number']) ?></span>
                                        <button type="button" class="btn btn-sm btn-outline-primary copy-btn-inline" data-copy="<?= e($bank['account_number']) ?>">
                                            <i class="fas fa-copy me-1"></i>Salin
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($bank['instructions'])): ?>
                                    <p class="small text-muted mb-0 mt-2"><i class="fas fa-circle-info me-1"></i><?= e($bank['instructions']) ?></p>
                                <?php endif; ?>
                                <p class="small mb-0 mt-2">Nominal transfer: <strong class="text-success"><?= $amountFormatted ?></strong> (transfer tepat hingga 3 digit terakhir)</p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($qrisChannels)): ?>
                        <h5 class="fw-bold mt-4 mb-3"><i class="fas fa-qrcode text-success me-2"></i>QRIS — Scan untuk Bayar</h5>
                        <div class="row g-3">
                            <?php foreach ($qrisChannels as $qr): ?>
                                <div class="col-sm-6 col-md-5">
                                    <div class="rounded-4 border p-3 text-center bg-light-subtle h-100 d-flex flex-column">
                                        <div class="fw-semibold mb-2"><?= e($qr['label']) ?></div>
                                        <?php if (!empty($qr['image_path'])): ?>
                                            <a href="<?= base_url($qr['image_path']) ?>" target="_blank" title="Klik untuk perbesar" class="d-block mx-auto" style="max-width:220px;">
                                                <img src="<?= base_url($qr['image_path']) ?>" alt="QRIS <?= e($qr['label']) ?>" class="img-fluid rounded-3 border bg-white" style="width:100%;object-fit:contain;" loading="lazy">
                                            </a>
                                            <a href="<?= base_url($qr['image_path']) ?>" download="QRIS-<?= preg_replace('/[^A-Za-z0-9]+/', '-', (string)$qr['label']) ?>.png" class="btn btn-sm btn-outline-secondary mt-2">
                                                <i class="fas fa-download me-1"></i>Simpan QRIS
                                            </a>
                                        <?php else: ?>
                                            <div class="text-muted small my-auto">Gambar QRIS belum tersedia.</div>
                                        <?php endif; ?>
                                        <?php if (!empty($qr['instructions'])): ?>
                                            <p class="small text-muted mb-0 mt-2"><?= e($qr['instructions']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="alert alert-light border small mt-3 mb-0">
                            <strong>Cara bayar via QRIS:</strong> buka aplikasi e-wallet/m-banking (GoPay, OVO, DANA, ShopeePay, BCA, dll.) → pilih menu Scan QR → arahkan ke kode QR di atas → pastikan nominal sesuai tagihan → selesaikan pembayaran → simpan bukti transaksi.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 1rem;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="small text-muted">Setelah membayar</div>
                            <h5 class="fw-bold mb-0">Unggah bukti pembayaran</h5>
                        </div>
                        <span class="badge bg-warning-subtle text-warning-emphasis">Wajib</span>
                    </div>

                    <form id="manualPaymentForm" method="POST" enctype="multipart/form-data" action="<?= e($formAction ?? '') ?>">
                        <?= \Core\Csrf::field() ?>
                        <input type="hidden" name="payment_method" value="<?= e(($selectedMethod ?? '') ?: (!empty($qrisChannels) && empty($banks) ? 'qris' : 'transfer')) ?>">

                        <div class="alert alert-info py-2 px-3 small">
                            Pembayaran akan diverifikasi oleh tim NFG setelah bukti diunggah. Status booking menjadi <strong>Menunggu Verifikasi</strong>.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Bukti pembayaran *</label>
                            <input type="file" name="payment_proof" class="form-control" accept="image/jpeg,image/png,application/pdf" required>
                            <div class="form-text">Screenshot/foto bukti transfer atau bukti transaksi QRIS. Format JPG, PNG, atau PDF maksimal 3MB.</div>
                            <img id="proofPreview" src="" alt="" class="img-fluid rounded-3 border mt-2 d-none" style="max-height:180px;">
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Bukti Pembayaran
                        </button>
                        <a href="<?= e($backUrl ?? base_url('/consultation')) ?>" class="btn btn-outline-secondary w-100">Nanti Saja</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.copy-btn-inline').forEach(function (btn) {
    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(function () {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Tersalin';
            setTimeout(function () { btn.innerHTML = original; }, 2000);
        });
    });
});

const proofInput = document.querySelector('#manualPaymentForm input[name="payment_proof"]');
const proofPreview = document.getElementById('proofPreview');
if (proofInput && proofPreview) {
    proofInput.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (file && file.type.startsWith('image/')) {
            proofPreview.src = URL.createObjectURL(file);
            proofPreview.classList.remove('d-none');
        } else {
            proofPreview.classList.add('d-none');
            proofPreview.src = '';
        }
    });
}

document.getElementById('manualPaymentForm')?.addEventListener('submit', function () {
    const btn = this.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengunggah...'; }
});
</script>
