<?php
$methods = is_array($payment_methods) ? $payment_methods : [];
$defaultMethod = $selectedPaymentMethod ?? ($forceTransfer ? 'transfer' : ($methods[0]['code'] ?? 'transfer'));
$defaultMethod = is_string($defaultMethod) && $defaultMethod !== '' ? $defaultMethod : 'transfer';
$hasMethods = count($methods) > 0;
$bankChannels = \App\Models\PaymentChannel::activeBanks();
$qrisChannels = \App\Models\PaymentChannel::activeQris();
$canQris = !$forceTransfer && !empty($qrisChannels);
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <div class="badge bg-primary-subtle text-primary mb-2">Langkah 1/2</div>
                            <h2 class="fw-bold mb-1">Pilih metode pembayaran</h2>
                            <p class="text-muted mb-0">Pilih opsi yang paling aman dan nyaman untuk transaksi Anda.</p>
                        </div>
                        <div class="text-md-end">
                            <div class="small text-muted">Total tagihan</div>
                            <div class="h3 fw-bold text-success">Rp <?= number_format($amount ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <?php if (!empty($original_booking_description)): ?>
                        <div class="alert alert-light border mb-4">
                            <div class="small text-muted mb-1">Booking sebelumnya</div>
                            <div class="fw-semibold"><?= e($original_booking_description) ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="rounded-4 border p-3 h-100 bg-light-subtle">
                                <div class="small text-muted mb-1">Deskripsi transaksi</div>
                                <div class="fw-semibold"><?= e($payment_description ?? '') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rounded-4 border p-3 h-100 bg-light-subtle">
                                <div class="small text-muted mb-1">Keamanan</div>
                                <div class="fw-semibold">Pembayaran diproses aman dan terlacak dengan bukti transaksi.</div>
                            </div>
                        </div>
                    </div>

                    <?php if ($forceTransfer): ?>
                        <div class="alert alert-info mb-4">
                            <strong>Catatan:</strong> Untuk penjadwalan ulang, hanya metode transfer yang tersedia. Bukti transfer wajib diunggah agar proses dapat diteruskan.
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <?php $isActive = $defaultMethod === 'transfer'; ?>
                        <div class="col-md-6 <?= $canQris ? '' : 'col-lg-12' ?>">
                            <button type="button" class="payment-method-card w-100 text-start border-0 rounded-4 p-0 <?= $isActive ? 'selected' : '' ?>" data-method="transfer" aria-pressed="<?= $isActive ? 'true' : 'false' ?>" onclick="selectPaymentMethod('transfer')">
                                <div class="card h-100 border shadow-sm">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="fs-2">🏦</div>
                                            <?php if ($isActive): ?>
                                                <span class="badge bg-success-subtle text-success">Terpilih</span>
                                            <?php endif; ?>
                                        </div>
                                        <h6 class="fw-bold mb-2">Transfer Bank Manual</h6>
                                        <p class="small text-muted mb-0">Transfer ke rekening resmi klinik, lalu unggah bukti transfer untuk diverifikasi tim NFG.</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                        <?php if ($canQris): ?>
                        <div class="col-md-6">
                            <button type="button" class="payment-method-card w-100 text-start border-0 rounded-4 p-0 <?= $defaultMethod === 'qris' ? 'selected' : '' ?>" data-method="qris" aria-pressed="<?= $defaultMethod === 'qris' ? 'true' : 'false' ?>" onclick="selectPaymentMethod('qris')">
                                <div class="card h-100 border shadow-sm">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="fs-2">📱</div>
                                            <?php if ($defaultMethod === 'qris'): ?>
                                                <span class="badge bg-success-subtle text-success">Terpilih</span>
                                            <?php endif; ?>
                                        </div>
                                        <h6 class="fw-bold mb-2">QRIS</h6>
                                        <p class="small text-muted mb-0">Scan kode QR dengan e-wallet atau m-banking (GoPay, OVO, DANA, BCA, dll.). Bukti transaksi wajib diunggah.</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 1rem;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="small text-muted">Langkah 2/2</div>
                            <h5 class="fw-bold mb-0">Detail pembayaran</h5>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary">Siap</span>
                    </div>

                    <form id="paymentForm" method="POST" enctype="multipart/form-data" action="<?= base_url($form_action ?? '/payment/process') ?>">
                        <?= \Core\Csrf::field() ?>
                        <input type="hidden" name="payment_method" id="paymentMethodInput" value="<?= e($defaultMethod) ?>">

                        <div class="payment-panel rounded-4 border p-3 mb-3" data-method="transfer" style="display: <?= $defaultMethod === 'transfer' ? 'block' : 'none' ?>;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">🏦</span>
                                <div>
                                    <div class="fw-semibold">Transfer Bank</div>
                                    <div class="small text-muted">Transfer ke rekening resmi klinik</div>
                                </div>
                            </div>
                            <?php if (!empty($bankChannels)): ?>
                                <?php foreach ($bankChannels as $bank): ?>
                                    <div class="border rounded-3 p-2 px-3 small mb-2 bg-light-subtle">
                                        <div class="fw-semibold"><?= e($bank['bank_name'] ?: $bank['label']) ?><?php if (!empty($bank['account_name'])): ?> <span class="text-muted fw-normal">a.n. <?= e($bank['account_name']) ?></span><?php endif; ?></div>
                                        <?php if (!empty($bank['account_number'])): ?>
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <span class="font-monospace fw-bold"><?= e($bank['account_number']) ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 copy-btn-inline" data-copy="<?= e($bank['account_number']) ?>"><i class="fas fa-copy"></i></button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info py-2 px-3 small mb-3">
                                    <div><strong><?= e(\App\Models\Setting::get('payment_bank_name')) ?></strong> a.n. <?= e(\App\Models\Setting::get('payment_account_holder')) ?></div>
                                    <div><strong>No. Rek:</strong> <strong><?= e(\App\Models\Setting::get('payment_account_number')) ?></strong></div>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label fw-600">Upload bukti transfer *</label>
                                <input type="file" name="payment_proof" class="form-control" accept="image/jpeg,image/png,application/pdf">
                                <small class="text-muted">JPG, PNG, atau PDF maksimal 3MB.</small>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-600">Nama pengirim *</label>
                                <input type="text" name="sender_name" class="form-control" maxlength="100">
                            </div>
                        </div>

                        <div class="payment-panel rounded-4 border p-3 mb-3" data-method="qris" style="display: <?= $defaultMethod === 'qris' ? 'block' : 'none' ?>;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">📱</span>
                                <div>
                                    <div class="fw-semibold">QRIS</div>
                                    <div class="small text-muted">Scan dengan e-wallet atau m-banking apa pun</div>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <?php foreach ($qrisChannels as $qr): ?>
                                    <div class="col-6">
                                        <div class="border rounded-3 p-2 text-center h-100">
                                            <div class="small fw-semibold text-truncate mb-1"><?= e($qr['label']) ?></div>
                                            <?php if (!empty($qr['image_path'])): ?>
                                                <a href="<?= base_url($qr['image_path']) ?>" target="_blank"><img src="<?= base_url($qr['image_path']) ?>" alt="QRIS" class="img-fluid rounded-2 border bg-white" loading="lazy"></a>
                                                <a href="<?= base_url($qr['image_path']) ?>" download="QRIS.png" class="btn btn-sm btn-link py-0 small">Simpan</a>
                                            <?php else: ?>
                                                <div class="text-muted small">Gambar belum tersedia</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="alert alert-light border py-2 px-3 small">
                                <strong>Nominal:</strong> Rp <?= number_format($amount ?? 0, 0, ',', '.') ?> — pastikan sesuai sebelum membayar.
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-600">Upload bukti transaksi QRIS *</label>
                                <input type="file" name="payment_proof" class="form-control" accept="image/jpeg,image/png,application/pdf">
                                <small class="text-muted">JPG, PNG, atau PDF maksimal 3MB.</small>
                            </div>
                        </div>

                        <div class="payment-panel rounded-4 border p-3 mb-3" data-method="card" style="display: <?= $defaultMethod === 'card' ? 'block' : 'none' ?>;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">💳</span>
                                <div>
                                    <div class="fw-semibold">Kartu Kredit/Debit</div>
                                    <div class="small text-muted">Pembayaran cepat dan aman</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600">Nomor kartu *</label>
                                <input type="text" name="card_number" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-600">MM/YY *</label>
                                    <input type="text" name="expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-600">CVV *</label>
                                    <input type="text" name="cvv" class="form-control" placeholder="000" maxlength="3">
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-600">Nama pemilik kartu *</label>
                                <input type="text" name="cardholder_name" class="form-control" maxlength="100">
                            </div>
                        </div>

                        <div class="payment-panel rounded-4 border p-3 mb-3" data-method="e_wallet" style="display: <?= $defaultMethod === 'e_wallet' ? 'block' : 'none' ?>;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">📱</span>
                                <div>
                                    <div class="fw-semibold">E-Wallet</div>
                                    <div class="small text-muted">Bayar dari aplikasi dompet digital Anda</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600">Pilih e-wallet *</label>
                                <select name="ewallet_type" class="form-select">
                                    <option value="">-- Pilih e-wallet --</option>
                                    <option value="gcash">GCash</option>
                                    <option value="ovo">OVO</option>
                                    <option value="dana">DANA</option>
                                    <option value="linkaja">LinkAja</option>
                                    <option value="shopee">ShopeePay</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-600">Nomor e-wallet *</label>
                                <input type="text" name="ewallet_number" class="form-control" placeholder="+62..." maxlength="20">
                            </div>
                        </div>

                        <div class="payment-panel rounded-4 border p-3 mb-3" data-method="cash" style="display: <?= $defaultMethod === 'cash' ? 'block' : 'none' ?>;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">💵</span>
                                <div>
                                    <div class="fw-semibold">Tunai</div>
                                    <div class="small text-muted">Bayar di loket klinik</div>
                                </div>
                            </div>
                            <div class="alert alert-light border py-2 px-3 small mb-0">
                                <div><strong>Total:</strong> Rp <?= number_format($amount ?? 0, 0, ',', '.') ?></div>
                                <div><strong>Lokasi:</strong> Loket Klinik NFG</div>
                            </div>
                        </div>

                        <div class="payment-panel rounded-4 border p-3 mb-3" data-method="midtrans" style="display: <?= $defaultMethod === 'midtrans' ? 'block' : 'none' ?>;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="fs-5">🔒</span>
                                <div>
                                    <div class="fw-semibold">Midtrans</div>
                                    <div class="small text-muted">Pembayaran aman melalui Snap</div>
                                </div>
                            </div>
                            <div class="alert alert-info py-2 px-3 small mb-0">Setelah konfirmasi, Anda akan diarahkan ke halaman pembayaran Midtrans untuk memilih bank, kartu, e-wallet, QRIS, atau metode lain yang tersedia.</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-lock me-2"></i>Konfirmasi pembayaran
                            </button>
                            <a href="<?= base_url('/consultation') ?>" class="btn btn-outline-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal Popup -->
<div id="transferModal" class="transfer-modal">
    <div class="transfer-modal-content">
        <div class="transfer-modal-header">
            <div class="transfer-modal-title">
                <div class="transfer-icon">🏦</div>
                <div>
                    <h3>Transfer Bank</h3>
                    <p>Informasi rekening transfer</p>
                </div>
            </div>
            <button type="button" class="transfer-modal-close" onclick="closeTransferModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="transfer-modal-body">
            <!-- Bank Details Card -->
            <?php if (!empty($bankChannels)): foreach ($bankChannels as $__b): ?>
            <div class="bank-details-card">
                <div class="bank-header">
                    <div class="bank-logo">🏦</div>
                    <div>
                        <div class="bank-name"><?= e($__b['bank_name'] ?: $__b['label']) ?></div>
                        <div class="bank-subtitle">Rekening Resmi Pembayaran</div>
                    </div>
                </div>

                <?php if (!empty($__b['account_name'])): ?>
                <div class="bank-info-group">
                    <div class="bank-info-label">Atas Nama Rekening</div>
                    <div class="bank-info-value">
                        <span><?= e($__b['account_name']) ?></span>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('<?= e($__b['account_name']) ?>', this)" title="Copy">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($__b['account_number'])): ?>
                <div class="bank-info-group">
                    <div class="bank-info-label">Nomor Rekening</div>
                    <div class="bank-info-value account-number">
                        <span><?= e($__b['account_number']) ?></span>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('<?= e($__b['account_number']) ?>', this)" title="Copy">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bank-divider"></div>

                <div class="transfer-amount">
                    <div class="bank-info-label">Jumlah Transfer</div>
                    <div class="amount-value">Rp <?= number_format($amount ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="bank-details-card">
                <div class="bank-header">
                    <div class="bank-logo">🏦</div>
                    <div>
                        <div class="bank-name"><?= e(\App\Models\Setting::get('payment_bank_name')) ?></div>
                        <div class="bank-subtitle">Bank Resmi untuk Pembayaran</div>
                    </div>
                </div>
                <div class="bank-info-group">
                    <div class="bank-info-label">Atas Nama Rekening</div>
                    <div class="bank-info-value">
                        <span><?= e(\App\Models\Setting::get('payment_account_holder')) ?></span>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('<?= e(\App\Models\Setting::get('payment_account_holder')) ?>', this)" title="Copy"><i class="fas fa-copy"></i></button>
                    </div>
                </div>
                <div class="bank-info-group">
                    <div class="bank-info-label">Nomor Rekening</div>
                    <div class="bank-info-value account-number">
                        <span><?= e(\App\Models\Setting::get('payment_account_number')) ?></span>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('<?= e(\App\Models\Setting::get('payment_account_number')) ?>', this)" title="Copy"><i class="fas fa-copy"></i></button>
                    </div>
                </div>
                <div class="bank-divider"></div>
                <div class="transfer-amount">
                    <div class="bank-info-label">Jumlah Transfer</div>
                    <div class="amount-value">Rp <?= number_format($amount ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Countdown Timer -->
            <div class="payment-deadline">
                <div class="countdown-icon">⏱️</div>
                <div class="countdown-content">
                    <div class="countdown-label">Waktu Verifikasi</div>
                    <div class="countdown-display">
                        <span id="transferCountdown">24:00:00</span>
                        <span class="countdown-subtext">Dari waktu transfer</span>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="transfer-instructions">
                <h5 class="instructions-title">📋 Panduan Transfer</h5>
                <ol class="instructions-list">
                    <li>Buka aplikasi perbankan Anda</li>
                    <li>Pilih menu "Transfer" atau "Kirim Dana"</li>
                    <li>Masukkan nomor rekening di atas dengan benar</li>
                    <li>Input jumlah uang sesuai tagihan</li>
                    <li>Konfirmasi dan selesaikan transfer</li>
                    <li>Ambil screenshot atau foto bukti transfer</li>
                    <li>Upload bukti transfer di form di bawah</li>
                </ol>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Keamanan Terjamin</strong>
                    <p>Pembayaran Anda dienkripsi dan terlindungi. Pastikan Anda mentransfer ke nomor rekening resmi di atas saja.</p>
                </div>
            </div>
        </div>

        <div class="transfer-modal-footer">
            <button type="button" class="btn-understand" onclick="closeTransferModal()">Saya Sudah Mengerti</button>
        </div>
    </div>
</div>

<script>
function selectPaymentMethod(method, showModal = true) {
    const input = document.getElementById('paymentMethodInput');
    if (!input) return;

    input.value = method;

    document.querySelectorAll('.payment-method-card').forEach(card => {
        const isSelected = card.getAttribute('data-method') === method;
        card.classList.toggle('selected', isSelected);
        card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
    });

    document.querySelectorAll('.payment-panel').forEach(panel => {
        panel.style.display = panel.getAttribute('data-method') === method ? 'block' : 'none';
    });

    syncPaymentProofInput(method);

    // Show transfer modal if transfer is selected and allowed
    if (method === 'transfer' && showModal) {
        setTimeout(() => showTransferModal(), 300);
    }
}

function syncPaymentProofInput(method) {
    document.querySelectorAll('.payment-panel input[name="payment_proof"]').forEach(pf => {
        const panel = pf.closest('.payment-panel');
        if (!panel) return;
        const isActive = panel.getAttribute('data-method') === method;
        pf.disabled = !isActive;
        if (!isActive && pf.value) {
            pf.value = '';
        }
    });
}

function showTransferModal() {
    const modal = document.getElementById('transferModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        startCountdown();
    }
}

function closeTransferModal() {
    const modal = document.getElementById('transferModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function copyToClipboard(text, element) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = element;
        const originalText = btn.textContent;
        btn.textContent = '✓ Disalin!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('copied');
        }, 2000);
    });
}

function startCountdown() {
    const countdownEl = document.getElementById('transferCountdown');
    if (!countdownEl) return;

    let timeLeft = 24 * 60 * 60; // 24 hours in seconds
    
    const updateDisplay = () => {
        const hours = Math.floor(timeLeft / 3600);
        const minutes = Math.floor((timeLeft % 3600) / 60);
        const seconds = timeLeft % 60;
        
        countdownEl.textContent = 
            String(hours).padStart(2, '0') + ':' + 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');
        
        if (timeLeft > 0) {
            timeLeft--;
            setTimeout(updateDisplay, 1000);
        }
    };
    
    updateDisplay();
}

function validatePaymentForm(event) {
    const form = event.target;
    const method = document.getElementById('paymentMethodInput')?.value || '';
    const panel = document.querySelector('.payment-panel[data-method="' + method + '"]');
    if (!panel) return true;

    const requiredFields = panel.querySelectorAll('input[required], select[required]');
    for (const field of requiredFields) {
        if (!field.checkValidity()) {
            field.reportValidity();
            event.preventDefault();
            return false;
        }
    }

    if (method === 'transfer' || method === 'qris') {
        const fileInput = panel.querySelector('input[name="payment_proof"]');
        if (!fileInput || !fileInput.value) {
            fileInput?.focus();
            alert(method === 'qris' ? 'Unggah bukti transaksi QRIS sebelum melanjutkan.' : 'Unggah bukti transfer sebelum melanjutkan.');
            event.preventDefault();
            return false;
        }
    }

    return true;
}

document.querySelectorAll('.copy-btn-inline').forEach(function (btn) {
    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(function () {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(function () { btn.innerHTML = original; }, 2000);
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const selectedMethod = document.getElementById('paymentMethodInput')?.value || 'transfer';
    // Initialize selection but do not open modal automatically on page load
    selectPaymentMethod(selectedMethod, false);
    const form = document.getElementById('paymentForm');
    if (form) {
        form.addEventListener('submit', validatePaymentForm);
    }

    // Close modal when clicking outside
    const modal = document.getElementById('transferModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeTransferModal();
            }
        });
    }
});
</script>

<style>
.payment-method-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.payment-method-card:hover { transform: translateY(-2px); }
.payment-method-card.selected .card { border-color: #198754 !important; box-shadow: 0 0.5rem 1.2rem rgba(25, 135, 84, 0.16) !important; }
.payment-panel { background: linear-gradient(135deg, rgba(255,255,255,0.96), rgba(248,250,252,0.95)); }

/* Transfer Modal Styles */
.transfer-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.3s ease;
}

.transfer-modal.show {
    display: flex;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(2rem); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.transfer-modal-content {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

.transfer-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 2rem 2rem 1rem;
    border-bottom: 2px solid #f0f0f0;
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.05), rgba(52, 211, 153, 0.05));
}

.transfer-modal-title {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.transfer-icon {
    font-size: 2.5rem;
    line-height: 1;
}

.transfer-modal-title h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.transfer-modal-title p {
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.transfer-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.transfer-modal-close:hover {
    background: #f0f0f0;
    color: #1f2937;
}

.transfer-modal-body {
    padding: 2rem;
}

.bank-details-card {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 2px solid #86efac;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.bank-header {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid rgba(255, 255, 255, 0.5);
}

.bank-logo {
    font-size: 2rem;
    line-height: 1;
}

.bank-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #16a34a;
    margin: 0;
}

.bank-subtitle {
    font-size: 0.875rem;
    color: #4b5563;
    margin: 0.25rem 0 0;
}

.bank-info-group {
    margin-bottom: 1.25rem;
}

.bank-info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.bank-info-value {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    word-break: break-all;
}

.account-number {
    font-family: 'Courier New', monospace;
    letter-spacing: 0.1em;
}

.copy-btn {
    background: #dbeafe;
    border: none;
    color: #0284c7;
    width: 2rem;
    height: 2rem;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    flex-shrink: 0;
    margin-left: 0.5rem;
}

.copy-btn:hover {
    background: #bfdbfe;
    transform: scale(1.05);
}

.copy-btn.copied {
    background: #dcfce7;
    color: #16a34a;
}

.bank-divider {
    height: 2px;
    background: rgba(255, 255, 255, 0.5);
    margin: 1.5rem 0;
}

.transfer-amount {
    text-align: center;
}

.amount-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #16a34a;
    margin-top: 0.5rem;
}

.payment-deadline {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 2px solid #fcd34d;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.countdown-icon {
    font-size: 2.5rem;
    line-height: 1;
}

.countdown-content {
    flex: 1;
}

.countdown-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.countdown-display {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

#transferCountdown {
    font-size: 2rem;
    font-weight: 700;
    color: #d97706;
    font-family: 'Courier New', monospace;
}

.countdown-subtext {
    font-size: 0.875rem;
    color: #6b7280;
}

.transfer-instructions {
    background: #f9fafb;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
}

.instructions-title {
    margin: 0 0 1rem;
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
}

.instructions-list {
    margin: 0;
    padding-left: 1.5rem;
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.8;
}

.instructions-list li {
    margin-bottom: 0.5rem;
}

.security-notice {
    display: flex;
    gap: 1rem;
    background: #eff6ff;
    border: 2px solid #bfdbfe;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.security-notice i {
    font-size: 1.5rem;
    color: #0284c7;
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.security-notice strong {
    display: block;
    color: #0284c7;
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}

.security-notice p {
    margin: 0;
    color: #0369a1;
    font-size: 0.875rem;
    line-height: 1.5;
}

.transfer-modal-footer {
    padding: 1.5rem 2rem;
    border-top: 2px solid #f0f0f0;
    background: #f9fafb;
    display: flex;
    gap: 1rem;
}

.btn-understand {
    flex: 1;
    padding: 1rem;
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.btn-understand:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.4);
}

.btn-understand:active {
    transform: translateY(0);
}

/* Mobile Responsive */
@media (max-width: 640px) {
    .transfer-modal-content {
        max-width: 100%;
        border-radius: 16px 16px 0 0;
    }

    .transfer-modal-header {
        padding: 1.5rem 1rem 1rem;
    }

    .transfer-modal-body {
        padding: 1.5rem;
    }

    .transfer-modal-footer {
        padding: 1rem;
    }

    .transfer-modal-title {
        flex-wrap: wrap;
    }

    .payment-deadline {
        flex-direction: column;
        text-align: center;
    }

    #transferCountdown {
        font-size: 1.75rem;
    }

    .bank-details-card {
        padding: 1rem;
    }
}
</style>
    .payment-method-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .payment-method-card:hover {
        border-color: #28a745;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
    }

    .payment-details {
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
