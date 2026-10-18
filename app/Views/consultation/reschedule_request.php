<?php
// Consultation Reschedule Management View
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Ubah Jadwal Konsultasi</h2>
            <p class="text-muted mb-0">Pilih waktu baru yang cocok dan kirim permintaan agar psikolog dapat meninjau serta menyetujui perubahan.</p>
        </div>
    </div>

    <?php if (!empty($consultation)): ?>
        <!-- Current Consultation Details -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Detail Konsultasi Saat Ini</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="status-card p-3 h-100">
                            <div class="text-muted small">Psikolog</div>
                            <div class="fw-bold mt-1"><?= e($consultation['provider_name']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="status-card p-3 h-100">
                            <div class="text-muted small">Jadwal Saat Ini</div>
                            <div class="fw-bold mt-1"><?= format_datetime($consultation['schedule_at']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="status-card p-3 h-100">
                            <div class="text-muted small">Durasi</div>
                            <div class="fw-bold mt-1"><?= $consultation['duration_minutes'] ?> menit</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="status-card p-3 h-100">
                            <div class="text-muted small">Metode</div>
                            <div class="fw-bold mt-1"><?= ucfirst(str_replace('_', ' ', $consultation['method'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reschedule Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2"></i>Permintaan Penjadwalan Ulang</h6>
            </div>
            <div class="card-body">
                <div class="progress-tracker mb-4">
                    <div class="tracker-line"></div>
                    <div class="tracker-step tracker-active">
                        <div class="tracker-icon bg-primary text-white"><i class="fas fa-calendar-alt"></i></div>
                        <div class="tracker-text">
                            <div class="fw-bold">Pilih Slot</div>
                            <div class="small text-muted">Tentukan jadwal baru</div>
                        </div>
                    </div>
                    <div class="tracker-step">
                        <div class="tracker-icon bg-light text-primary"><i class="fas fa-pencil-alt"></i></div>
                        <div class="tracker-text">
                            <div class="fw-bold">Isi Alasan</div>
                            <div class="small text-muted">Jelaskan perubahan singkat</div>
                        </div>
                    </div>
                    <div class="tracker-step">
                        <div class="tracker-icon bg-light text-info"><i class="fas fa-check-circle"></i></div>
                        <div class="tracker-text">
                            <div class="fw-bold">Tunggu Persetujuan</div>
                            <div class="small text-muted">Psikolog akan meninjau permintaan</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="step-card p-3 h-100">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="step-pill bg-primary text-white fw-bold">1</div>
                                <div>
                                    <div class="small text-muted">Pilih Waktu</div>
                                    <div class="fw-bold">Pilih slot atau masukkan tanggal baru</div>
                                </div>
                            </div>
                            <p class="small text-muted mb-0">Gunakan slot yang direkomendasikan agar lebih cepat disetujui.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="step-card p-3 h-100">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="step-pill bg-success text-white fw-bold">2</div>
                                <div>
                                    <div class="small text-muted">Isi Alasan</div>
                                    <div class="fw-bold">Jelaskan perubahan secara singkat</div>
                                </div>
                            </div>
                            <p class="small text-muted mb-0">Contoh: mendadak ada keperluan kerja, keluarga, atau acara.</p>
                        </div>
                    </div>
                </div>

                <!-- <div class="alert alert-info mb-3 d-flex align-items-center gap-3">
                    <div class="display-6 text-primary"><i class="fas fa-arrow-right"></i></div>
                    <div>
                        <div class="fw-bold mb-1">Apa yang terjadi setelah dikirim?</div>
                        <div class="small text-muted">Psikolog akan meninjau permintaan, lalu sistem akan mengirim notifikasi apabila disetujui. Biaya tambahan akan muncul setelah permintaan diproses.</div>
                    </div>
                </div> -->

                <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/reschedule/request') ?>">
                    <?= \Core\Csrf::field() ?>

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Ketentuan biaya reschedule:</strong>
                        <?php if ($reschedule_fee > 0): ?>
                          Kurang dari 24 jam sebelum sesi &mdash; biaya Rp <?= number_format($reschedule_fee, 0, ',', '.') ?>.
                        <?php else: ?>
                          Setidaknya 24 jam sebelum sesi &mdash; tanpa biaya.
                        <?php endif; ?>
                        <br>
                        <small class="text-muted">Sesi Anda dimulai dalam sekitar <?= number_format($hours_until_session ?? 0, 1, ',', '.') ?> jam. Biaya hanya diproses bila permintaan disetujui.</small>
                    </div>

                    <!-- Available Slots -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Slot Praktik yang Direkomendasikan</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($availableSlots)): ?>
                    <p class="small text-muted mb-3">Klik salah satu slot untuk mengisi otomatis tanggal dan waktu baru.</p>
                    <div class="row g-2">
                        <?php foreach ($availableSlots as $slot): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-3 text-center cursor-pointer slot-option" 
                                     onclick="selectSlot('<?= $slot['datetime'] ?>', this)">
                                    <div class="fw-bold"><?= format_date($slot['date']) ?></div>
                                    <div class="text-muted small"><?= $slot['time'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        Belum ada slot praktik otomatis tersedia. Silakan pilih tanggal dan waktu lain yang cocok pada kolom di atas.
                    </div>
                <?php endif; ?>
            </div>
        </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Waktu Baru *</label>
                        <input type="datetime-local" name="new_schedule_at" class="form-control" required placeholder="Pilih tanggal dan waktu baru">
                        <small class="text-muted">Klik slot yang disarankan di bawah atau pilih waktu lain yang cocok.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Alasan Penjadwalan Ulang</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Contoh: Saya ada keperluan mendadak pada jadwal sebelumnya..." maxlength="500"></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Informasi Penting</h6>
                        <ul class="small mb-0 ps-3">
                            <li>Psikolog akan meninjau jadwal baru sebelum perubahan disetujui.</li>
                            <li>Reschedule minimal 24 jam sebelum sesi gratis; kurang dari 24 jam dikenakan Rp100.000.</li>
                            <li>Pastikan memilih waktu yang wajar dan sesuai praktik psikolog.</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>Kirim Permintaan
                        </button>
                        <a href="<?= base_url('/consultation/' . $consultation['id']) ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        

    <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            Konsultasi tidak ditemukan atau tidak valid untuk penjadwalan ulang.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const firstSlot = document.querySelector('.slot-option');
    if (firstSlot) {
        firstSlot.click();
    }
});

function selectSlot(datetime, element) {
    // Mark as selected
    document.querySelectorAll('.slot-option').forEach(e => e.classList.remove('border-primary', 'bg-light-primary'));
    element.classList.add('border-primary', 'bg-light-primary');
    
    // Fill the datetime input
    const input = document.querySelector('input[name="new_schedule_at"]');
    if (input) {
        input.value = datetime;
    }
}
</script>

<style>
.status-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
}

.step-card {
    background: #f8f9fa;
    border-radius: 1rem;
    border: 1px solid rgba(13, 110, 253, 0.12);
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.05);
}

.step-pill {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: grid;
    place-items: center;
}

.slot-option {
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid #ddd !important;
}

.progress-tracker {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.tracker-line {
    position: absolute;
    top: 50%;
    left: 5%;
    right: 5%;
    height: 3px;
    background: rgba(13, 110, 253, 0.12);
    transform: translateY(-50%);
    z-index: 0;
}

.tracker-step {
    position: relative;
    z-index: 1;
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0.5rem;
    border-radius: 1rem;
    background: rgba(255,255,255,0.9);
    border: 1px solid transparent;
}

.tracker-step:not(:last-child) {
    margin-right: 0.5rem;
}

.tracker-step.tracker-active {
    border-color: rgba(13, 110, 253, 0.16);
    background: rgba(13, 110, 253, 0.06);
}

.tracker-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 1.1rem;
}

.tracker-text {
    min-width: 0;
}

.tracker-text .fw-bold {
    font-size: 0.95rem;
}

.tracker-text .small {
    display: block;
    line-height: 1.35;
}

.slot-option:hover {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.05);
}

.slot-option.border-primary {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.1);
}

.cursor-pointer {
    cursor: pointer;
}
</style>
