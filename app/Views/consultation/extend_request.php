<?php
// Consultation Extension View
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Permintaan Perpanjangan Durasi</h2>
            <p class="text-muted mb-0">Ajukan perpanjangan durasi untuk sesi konsultasi ini.</p>
        </div>
    </div>

    <?php if (!empty($consultation)): ?>
        <!-- Current Consultation Details -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Detail Konsultasi</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="small text-muted mb-1">Dengan</p>
                        <h6 class="fw-bold"><?= e($consultation['provider_name']) ?></h6>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-muted mb-1">Jadwal</p>
                        <h6 class="fw-bold"><?= format_datetime($consultation['schedule_at']) ?></h6>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-muted mb-1">Durasi Saat Ini</p>
                        <h6 class="fw-bold"><?= $consultation['duration_minutes'] ?> menit</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-muted mb-1">Metode</p>
                        <h6 class="fw-bold"><?= ucfirst(str_replace('_', ' ', $consultation['method'])) ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extension Request Form -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="fw-bold mb-0"><i class="fas fa-hourglass-end me-2"></i>Permintaan Perpanjangan</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/extend/request') ?>">
                    <?= \Core\Csrf::field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Tambah Durasi (Menit) *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decreaseDuration()">−</button>
                                <input type="number" name="extension_minutes" id="extension_minutes" class="form-control text-center" 
                                       value="15" min="15" max="180" step="15" required onchange="updatePrice()">
                                <button type="button" class="btn btn-outline-secondary" onclick="increaseDuration()">+</button>
                            </div>
                            <small class="text-muted">Kelipatan 15 menit</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Durasi Baru</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="new_duration" value="<?= $consultation['duration_minutes'] + 15 ?> menit" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Harga untuk <?= $consultation['duration_minutes'] ?> menit:</p>
                                <h6 class="fw-bold">Rp <?= number_format($consultation['price_amount'] ?? 0, 0, ',', '.') ?></h6>
                            </div>
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Biaya Tambahan:</p>
                                <h6 class="fw-bold text-success" id="extension_price">
                                    Rp <?= number_format($extension_price ?? 0, 0, ',', '.') ?>
                                </h6>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Alasan Perpanjangan</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Jelaskan mengapa Anda ingin memperpanjang sesi..." maxlength="500"></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Informasi Penting</h6>
                        <ul class="small mb-0 ps-3">
                            <li>Permintaan perpanjangan akan dikirim ke client untuk disetujui dan dibayar.</li>
                            <li>Biaya tambahan akan dihitung berdasarkan durasi perpanjangan.</li>
                            <li>Perpanjangan hanya dapat dilakukan jika client menyelesaikan pembayaran tambahan.</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Kirim Permintaan
                        </button>
                        <a href="<?= base_url('/consultation/' . $consultation['id']) ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Extension Options Guide -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Opsi Perpanjangan yang Tersedia</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Durasi Total</th>
                            <th>Tambahan Menit</th>
                            <th>Biaya Tambahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $currentDuration = (int)$consultation['duration_minutes'];
                        $basePrice = (int)($consultation['price_amount'] ?? 0);
                        $pricePerMin = $basePrice > 0 ? $basePrice / $currentDuration : 0;
                        
                        for ($i = 15; $i <= 90; $i += 15):
                            $additionalPrice = (int)ceil($pricePerMin * $i);
                        ?>
                            <tr>
                                <td><strong><?= $currentDuration + $i ?> menit</strong></td>
                                <td><?= $i ?> menit</td>
                                <td>Rp <?= number_format($additionalPrice, 0, ',', '.') ?></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            Konsultasi tidak ditemukan atau tidak dapat diperpanjang.
        </div>
    <?php endif; ?>
</div>

<script>
const pricePerMinute = <?= $price_per_minute ?? 0 ?>;
const currentDuration = <?= $consultation['duration_minutes'] ?? 30 ?>;

function updatePrice() {
    const extensionMinutes = parseInt(document.getElementById('extension_minutes').value) || 0;
    const newDuration = currentDuration + extensionMinutes;
    const additionalPrice = Math.ceil(pricePerMinute * extensionMinutes);
    
    document.getElementById('new_duration').value = newDuration + ' menit';
    document.getElementById('extension_price').textContent = 'Rp ' + additionalPrice.toLocaleString('id-ID');
}

function increaseDuration() {
    const input = document.getElementById('extension_minutes');
    const current = parseInt(input.value) || 15;
    input.value = Math.min(current + 15, 180);
    updatePrice();
}

function decreaseDuration() {
    const input = document.getElementById('extension_minutes');
    const current = parseInt(input.value) || 15;
    input.value = Math.max(current - 15, 15);
    updatePrice();
}
</script>
