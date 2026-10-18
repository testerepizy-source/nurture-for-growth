<h4 class="fw-bold mb-1">Booking Konseling Baru</h4>
<?php if ($isMatched && $selectedProvider): ?>
  <p class="small text-success mb-3"><i class="fas fa-wand-magic-sparkles me-1"></i>Psikolog di bawah sudah dipilihkan otomatis oleh sistem pencocokan cerdas berdasarkan keluhan Anda. Anda tetap bisa <a href="#step1" onclick="goStep(1)">mengganti pilihan</a>.</p>
<?php elseif (!$selectedProvider): ?>
  <p class="small text-muted mb-3">Belum yakin harus pilih siapa? <a href="<?= base_url('/matching') ?>">Coba sistem pencocokan cerdas kami &rarr;</a></p>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card p-3">
      <!-- Wizard Stepper -->
      <ol class="booking-stepper" id="bookingStepper">
        <li class="booking-step" data-step="1"><span class="booking-step-dot">1</span><div class="booking-step-label"><span class="booking-step-title">Pilih Psikolog</span><span class="booking-step-sub">Tenaga profesional</span></div></li>
        <li class="booking-step" data-step="2"><span class="booking-step-dot">2</span><div class="booking-step-label"><span class="booking-step-title">Jadwal &amp; Metode</span><span class="booking-step-sub">Hari, jam, cara sesi</span></div></li>
        <li class="booking-step" data-step="3"><span class="booking-step-dot">3</span><div class="booking-step-label"><span class="booking-step-title">Detail &amp; Bayar</span><span class="booking-step-sub">Alasan &amp; pembayaran</span></div></li>
      </ol>

      <form id="consultationForm" class="consultation-form" method="POST" action="<?= base_url('/consultation') ?>" enctype="multipart/form-data">
        <?= \Core\Csrf::field() ?>
        <input type="hidden" name="from_matching" value="<?= $isMatched ? '1' : '0' ?>">
        <input type="hidden" name="provider_id" id="providerIdInput" value="<?= (int) $preselectedProviderId ?>">

        <!-- ============ STEP 1 : PILIH PSIKOLOG ============ -->
        <section class="wizard-step" id="step1">
          <div class="form-section-header">
            <div>
              <h5 class="section-title">Pilih Tenaga Profesional</h5>
              <p class="small text-muted mb-0">Ketik untuk mencari nama atau spesialisasi. Klik kartu untuk memilih.</p>
            </div>
          </div>
          <div class="mb-3">
            <input type="text" id="providerSearch" class="form-control" placeholder="Cari nama atau spesialisasi&hellip;">
          </div>
          <div class="provider-grid" id="providerGrid">
            <?php foreach ($providerCards as $card): ?>
              <?php
                $priceFromText = isset($card['price_from']) && $card['price_from'] > 0 ? 'Rp' . number_format((int) $card['price_from'], 0, ',', '.') : 'Cek tarif';
                $cardReview = $card['review'] ?? ['total' => 0, 'average' => 0];
              ?>
              <button type="button" class="provider-card" data-id="<?= (int) $card['id'] ?>" data-name="<?= e($card['name']) ?>" data-role="<?= e($card['role']) ?>" data-spec="<?= e($card['specialization']) ?>" data-photo="<?= e($card['photo'] ?? '') ?>" data-price="<?= (int) ($card['price_from'] ?? 0) ?>">
                <span class="provider-card-photo">
                  <?php if (!empty($card['photo'])): ?><img src="<?= base_url($card['photo']) ?>" alt="<?= e($card['name']) ?>"><?php else: ?><span class="avatar-circle"><?= e(mb_substr($card['name'], 0, 1)) ?></span><?php endif; ?>
                </span>
                <span class="provider-card-body">
                  <span class="provider-card-role"><?= e($card['role']) ?></span>
                  <span class="provider-card-name"><?= e($card['name']) ?></span>
                  <span class="provider-card-spec"><?= e($card['specialization'] ?: 'Konseling umum') ?></span>
                  <span class="provider-card-meta">
                    <span class="provider-card-stars"><?php if ($cardReview['total'] > 0): ?><i class="fas fa-star"></i> <?= number_format((float) $cardReview['average'], 1, ',', '.') ?> <em>(<?= (int) $cardReview['total'] ?>)</em><?php else: ?><em class="text-muted">Belum ada ulasan</em><?php endif; ?></span>
                    <span class="provider-card-price"><strong><?= $priceFromText ?></strong>/sesi</span>
                  </span>
                </span>
                <span class="provider-card-check"><i class="fas fa-check"></i></span>
              </button>
            <?php endforeach; ?>
            <?php if (empty($providerCards)): ?>
              <p class="text-muted small mb-0">Belum ada tenaga profesional terdaftar.</p>
            <?php endif; ?>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-primary" id="btnStep1Next" disabled>Lanjut ke Jadwal &amp; Metode <i class="fas fa-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ============ STEP 2 : JADWAL & METODE ============ -->
        <section class="wizard-step" id="step2" style="display:none;">
          <div class="form-section-header">
            <div>
              <h5 class="section-title">Jadwal &amp; Metode</h5>
              <p class="small text-muted mb-0">Pilih cara konsultasi, lalu tentukan hari dan jam yang tersedia.</p>
            </div>
          </div>

          <label class="form-label small fw-semibold">Metode konsultasi</label>
          <div class="method-tiles mb-3" id="methodTiles">
            <?php $methodOptions = [
              ['value' => 'video', 'icon' => 'fa-video', 'label' => 'Video Call', 'hint' => 'Telekonseling via Google Meet'],
              ['value' => 'voice', 'icon' => 'fa-phone', 'label' => 'Voice Call', 'hint' => 'Telepon tanpa video'],
              ['value' => 'tatap_muka', 'icon' => 'fa-handshake', 'label' => 'Tatap Muka', 'hint' => 'Bertemu langsung di klinik'],
            ]; ?>
            <?php foreach ($methodOptions as $m): ?>
              <button type="button" class="method-tile" data-method="<?= $m['value'] ?>">
                <span class="method-tile-icon"><i class="fas <?= $m['icon'] ?>"></i></span>
                <span>
                  <span class="method-tile-name"><?= $m['label'] ?></span>
                  <span class="method-tile-hint"><?= $m['hint'] ?></span>
                </span>
                <span class="method-tile-price" data-price-key="<?= $m['value'] === 'tatap_muka' ? 'tatap_muka' : 'online' ?>">-</span>
                <span class="method-tile-check"><i class="fas fa-check"></i></span>
              </button>
            <?php endforeach; ?>
          </div>
          <select name="method" id="methodSelect" class="d-none">
            <option value="video">Video Call (Telekonseling)</option>
            <option value="voice">Voice Call</option>
            <option value="tatap_muka">Tatap Muka</option>
          </select>

          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <label class="form-label small" for="scheduleDateSelect">Hari tersedia</label>
              <select id="scheduleDateSelect" class="form-select" disabled>
                <option value="">Pilih psikolog terlebih dahulu</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small" for="scheduleTimeSelect">Jam tersedia</label>
              <select id="scheduleTimeSelect" class="form-select" disabled>
                <option value="">Pilih hari terlebih dahulu</option>
              </select>
              <input type="hidden" name="schedule_at" id="scheduleAtInput" value="">
              <input type="hidden" name="duration_minutes" id="durationMinutesInput" value="">
            </div>
            <div class="col-12" id="step2EmptySlot" style="display:none;">
              <div class="alert alert-warning small mb-0"><i class="fas fa-calendar-xmark me-1"></i> Belum ada slot praktik yang tersedia untuk metode ini. Pilih metode lain atau cek kembali jadwal psikolog.</div>
            </div>
            <div class="col-12" id="slotSummaryPanel" style="display:none;">
              <div class="slot-summary-panel">
                <div class="slot-summary-top">
                  <span class="slot-summary-badge">Slot Terpilih</span>
                  <span class="slot-summary-status">Durasi: <strong id="slotDurationLabel">-</strong></span>
                </div>
                <div class="slot-summary-body">
                  <div><strong>Waktu mulai:</strong> <span id="slotStartLabel">-</span></div>
                  <div><strong>Waktu selesai:</strong> <span id="slotEndLabel">-</span></div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="goStep(1)"><i class="fas fa-arrow-left me-1"></i> Kembali</button>
            <button type="button" class="btn btn-primary" id="btnStep2Next" disabled>Lanjut ke Detail &amp; Bayar <i class="fas fa-arrow-right ms-1"></i></button>
          </div>
        </section>

        <!-- ============ STEP 3 : DETAIL & BAYAR ============ -->
        <section class="wizard-step" id="step3" style="display:none;">
          <div class="form-section-header">
            <div>
              <h5 class="section-title">Detail Sesi &amp; Pembayaran</h5>
              <p class="small text-muted mb-0">Lengkapi informasi singkat agar psikolog memahami kebutuhan Anda.</p>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small">Jenis Client</label>
            <select name="session_type" id="sessionTypeSelect" class="form-select" onchange="updatePriceEstimate(); updatePatientRoleVisibility(); updateRecap();">
              <option value="individual">Individu</option>
              <option value="couple">Pasangan</option>
              <option value="family">Keluarga</option>
            </select>
            <div class="form-text small text-muted">Pilih tipe client agar psikolog memahami konteks sesi booking.</div>
          </div>
          <div class="mb-3" id="patientRoleGroup" style="display:none;">
            <label class="form-label small">Peran Client</label>
            <select name="patient_role" id="patientRoleSelect" class="form-select">
              <option value="self">Individu</option>
              <option value="husband">Suami</option>
              <option value="wife">Istri</option>
              <option value="father">Ayah</option>
              <option value="mother">Ibu</option>
              <option value="child">Anak</option>
            </select>
            <div class="form-text small text-muted">Sesuaikan peran client untuk konteks sesi.</div>
          </div>
          <div class="mb-3">
            <label class="form-label small">Alasan / Keluhan Singkat</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Tulis singkat tujuan konsultasi atau keluhan Anda"></textarea>
          </div>

          <div class="mb-3 p-3 rounded" style="background:rgba(10, 61, 119, 0.08);border:1px solid rgba(6, 80, 87, 0.12);">
            <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3">
              <div>
                <div class="small fw-semibold mb-2">Pernah melakukan sesi konsul sebelumnya?</div>
                <div class="text-muted small">Jika iya, unggah dokumen riwayat sesi untuk membantu psikolog memahami kondisi Anda.</div>
              </div>
              <div class="d-flex gap-3 align-items-center">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="previous_session" id="previousSessionNo" value="no" checked onchange="updatePreviousSessionDocumentVisibility()">
                  <label class="form-check-label" for="previousSessionNo">Tidak</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="previous_session" id="previousSessionYes" value="yes" onchange="updatePreviousSessionDocumentVisibility()">
                  <label class="form-check-label" for="previousSessionYes">Ya</label>
                </div>
              </div>
            </div>
            <div id="previousSessionDocumentGroup" class="mb-2 mt-2" style="display:none">
              <label class="form-label small">Unggah Dokumen Riwayat Sesi Sebelumnya</label>
              <input type="file" name="previous_session_document" class="form-control" accept="image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
              <p class="text-muted small mt-1 mb-0">JPG, PNG, PDF, DOC, atau DOCX. Maks 10MB.</p>
            </div>
          </div>

          <div class="form-section-card">
            <div id="promoActiveNote" class="small mb-2" style="display:none;color:#0a7d33;"></div>
            <p class="small fw-bold mb-2">Estimasi Biaya</p>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="small text-muted">Total yang dibayar</span>
              <span>
                <span class="text-muted text-decoration-line-through small me-1" id="priceOriginal" style="display:none"></span>
                <span class="fw-bold" id="priceEstimate" style="font-size:1.2rem;color:var(--nfg-teal-700)">-</span>
              </span>
            </div>
            <?php $pcBanks = \App\Models\PaymentChannel::activeBanks(); $pcQris = \App\Models\PaymentChannel::activeQris(); ?>
            <input type="hidden" name="payment_method" id="paymentMethodSelect" value="<?= !empty($pcBanks) ? 'transfer' : (!empty($pcQris) ? 'qris' : 'transfer') ?>">
            <p class="small text-muted mb-2">Metode pembayaran</p>
            <?php if (!empty($pcBanks)): ?>
            <label class="d-block border rounded-3 p-3 mb-2 payment-choice">
              <span class="d-flex gap-2 align-items-start">
                <input class="form-check-input mt-1" type="radio" name="payment_choice" value="transfer" checked onchange="setBookingPaymentMethod('transfer')" style="flex-shrink:0;">
                <span>
                  <span class="fw-semibold d-block"><i class="fas fa-building-columns me-1"></i>Transfer Bank Manual</span>
                  <span class="small text-muted"><?php
                    $bankNames = array_values(array_filter(array_map(fn($b) => ($b['bank_name'] ?: $b['label']), $pcBanks)));
                    echo e(implode(', ', array_slice($bankNames, 0, 4)) . (count($bankNames) > 4 ? ', dll.' : ''));
                  ?> — unggah bukti transfer setelah booking dibuat.</span>
                </span>
              </span>
            </label>
            <?php endif; ?>
            <?php if (!empty($pcQris)): ?>
            <label class="d-block border rounded-3 p-3 mb-2 payment-choice">
              <span class="d-flex gap-2 align-items-start">
                <input class="form-check-input mt-1" type="radio" name="payment_choice" value="qris" <?= empty($pcBanks) ? 'checked' : '' ?> onchange="setBookingPaymentMethod('qris')" style="flex-shrink:0;">
                <span>
                  <span class="fw-semibold d-block"><i class="fas fa-qrcode me-1"></i>QRIS</span>
                  <span class="small text-muted">Scan via e-wallet/m-banking (GoPay, OVO, DANA, BCA, dll.) — unggah bukti transaksi setelah booking dibuat.</span>
                </span>
              </span>
            </label>
            <?php endif; ?>
            <div class="alert alert-info mb-0 py-2 small">
              Setelah booking dikirim, Anda akan diarahkan ke halaman instruksi pembayaran untuk melakukan pembayaran dan mengunggah buktinya.
            </div>
            <div class="mt-3 p-3 rounded" style="background:#f4faf7;border:1px solid #cfe5db;">
              <div class="small fw-semibold mb-1"><i class="fas fa-list-check me-1"></i> Setelah Anda menekan Kirim:</div>
              <ol class="small mb-0 ps-3" style="line-height:1.7;">
                <li>Booking berstatus <strong>Menunggu</strong> persetujuan psikolog (maksimal 24 jam).</li>
                <li>Anda diarahkan ke instruksi pembayaran &amp; unggah bukti.</li>
                <li>Saat disetujui, Anda menerima notifikasi + link konsultasi otomatis.</li>
              </ol>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="goStep(2)"><i class="fas fa-arrow-left me-1"></i> Kembali</button>
            <button class="btn btn-primary" type="submit">Kirim Permintaan Booking</button>
          </div>
        </section>
      </form>
    </div>
  </div>

  <!-- ============ SIDEBAR : RINGKASAN BOOKING ============ -->
  <div class="col-lg-5">
    <div class="booking-summary">
      <div class="booking-summary-head">
        <div class="booking-summary-provider">
          <div class="booking-summary-photo" id="sumPhotoWrap"><span class="avatar-circle" id="sumPhotoFallback">?</span><img id="sumPhoto" src="" alt="" style="display:none;"></div>
          <div>
            <div class="small fw-bold" style="text-transform:uppercase;letter-spacing:.03em;" id="sumProviderRole">-</div>
            <h5 class="mb-1" style="font-weight:800;" id="sumProviderName">Belum ada psikolog dipilih</h5>
            <div class="small text-muted" id="sumProviderSpec">&nbsp;</div>
          </div>
        </div>
        <a href="#step1" class="small fw-bold" style="color:var(--nfg-teal-700)" id="sumChangeLink" onclick="goStep(1)">Ganti</a>
      </div>
      <hr class="my-3">
      <div class="booking-summary-rows">
        <div class="booking-summary-row"><span class="label">Jadwal</span><span class="value" id="sumSchedule">Pilih jadwal</span></div>
        <div class="booking-summary-row"><span class="label">Metode</span><span class="value" id="sumMethod">Pilih metode</span></div>
        <div class="booking-summary-row"><span class="label">Durasi</span><span class="value" id="sumDuration">-</span></div>
        <div class="booking-summary-row"><span class="label">Jenis Sesi</span><span class="value" id="sumSessionType">Individu</span></div>
      </div>
      <div class="booking-summary-total mt-3">
        <div class="small text-muted" id="sumPromoNote">&nbsp;</div>
        <div class="d-flex justify-content-between align-items-end">
          <span class="small fw-bold">Perkiraan Biaya</span>
          <span class="text-end">
            <span class="text-muted text-decoration-line-through small d-block" id="sumPriceOriginal" style="display:none"></span>
            <span class="fw-bold" style="font-size:1.25rem;color:var(--nfg-teal-700)" id="sumPrice">-</span>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  $tierTables = [];
  foreach (array_unique(array_column($providerCards, 'tier')) as $tt) {
      $tierTables[$tt] = \App\Services\PriceCalculatorService::tableFor($tt);
  }
?>

<script>
const providers = <?= json_encode($providerCards, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const rateTable = <?= json_encode($rateTable) ?>;
const tierTables = <?= json_encode($tierTables, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const activePromos = <?= json_encode(array_map(fn($p) => [
    'name' => $p['name'],
    'start_date' => $p['start_date'],
    'end_date' => $p['end_date'],
    'discount_type' => $p['discount_type'],
    'discount_value' => (int) $p['discount_value'],
], $activePromos ?? []), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const API_SLOTS_URL = <?= json_encode(base_url('/schedule/api-slots')) ?>;
const initialSlots = <?= json_encode($availableSlots, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const preSelectedId = <?= (int) $preselectedProviderId ?>;

const slotCache = {};
if (preSelectedId > 0 && Array.isArray(initialSlots)) slotCache[preSelectedId] = initialSlots;

let selectedProviderId = 0;
let selectedProviderTier = 'associate';

function bestPromoFor(basePrice, dateStr) {
  if (!dateStr) return null;
  let best = null, bestAmt = 0;
  activePromos.forEach(function(p) {
    if (dateStr < p.start_date || dateStr > p.end_date) return;
    const amt = p.discount_type === 'percent'
      ? Math.round(basePrice * Math.min(p.discount_value, 100) / 100)
      : Math.min(p.discount_value, basePrice);
    if (amt > bestAmt) { bestAmt = amt; best = p; }
  });
  return best ? { promo: best, amount: bestAmt } : null;
}

function providerById(id) {
  return providers.find(p => p.id === id) || null;
}

function updatePriceEstimate() {
  const method = document.getElementById('methodSelect')?.value || 'video';
  const sessionType = document.getElementById('sessionTypeSelect')?.value || 'individual';
  const onlineOrOffline = method === 'tatap_muka' ? 'tatap_muka' : 'online';
  const table = tierTables[selectedProviderTier] || rateTable;
  const price = table[sessionType]?.[onlineOrOffline] || 0;

  const selDate = document.getElementById('scheduleDateSelect')?.value || '';
  const match = bestPromoFor(price, selDate);
  let finalPrice = price;
  const originalEl = document.getElementById('priceOriginal');
  const noteEl = document.getElementById('promoActiveNote');
  const estEl = document.getElementById('priceEstimate');
  if (match) {
    finalPrice = Math.max(0, price - match.amount);
    if (originalEl) { originalEl.style.display = 'inline'; originalEl.textContent = 'Rp' + price.toLocaleString('id-ID'); }
    if (noteEl) {
      noteEl.style.display = 'block';
      noteEl.innerHTML = '<i class="fas fa-tag me-1"></i>Promo <strong>' + match.promo.name + '</strong> berlaku untuk tanggal ini (hemat Rp' + match.amount.toLocaleString('id-ID') + ').';
    }
  } else {
    if (originalEl) { originalEl.style.display = 'none'; originalEl.textContent = ''; }
    if (noteEl) { noteEl.style.display = 'none'; noteEl.innerHTML = ''; }
  }

  if (estEl) estEl.textContent = finalPrice > 0 ? 'Rp' + finalPrice.toLocaleString('id-ID') : '-';
}

function updatePatientRoleVisibility() {
  const sessionType = document.getElementById('sessionTypeSelect')?.value || 'individual';
  const roleGroup = document.getElementById('patientRoleGroup');
  const roleSelect = document.getElementById('patientRoleSelect');
  if (!roleGroup || !roleSelect) return;

  if (sessionType === 'individual') {
    roleGroup.style.display = 'none';
    roleSelect.required = false;
    roleSelect.value = 'self';
  } else if (sessionType === 'couple') {
    roleGroup.style.display = 'block';
    roleSelect.required = true;
    roleSelect.innerHTML = '<option value="husband">Suami</option><option value="wife">Istri</option>';
    if (!['husband','wife'].includes(roleSelect.value)) roleSelect.value = 'husband';
  } else if (sessionType === 'family') {
    roleGroup.style.display = 'block';
    roleSelect.required = true;
    roleSelect.innerHTML = '<option value="father">Ayah</option><option value="mother">Ibu</option><option value="child">Anak</option>';
    if (!['father','mother','child'].includes(roleSelect.value)) roleSelect.value = 'father';
  }
}

function updatePreviousSessionDocumentVisibility() {
  const hasPrevious = document.querySelector('input[name="previous_session"]:checked')?.value === 'yes';
  const group = document.getElementById('previousSessionDocumentGroup');
  if (group) group.style.display = hasPrevious ? 'block' : 'none';
}

function modeForMethod(method) {
  return method === 'tatap_muka' ? 'offline' : 'online';
}

function slotsForMethod(slots, method) {
  const mode = modeForMethod(method);
  return (slots || []).filter(function(s) {
    return !s.mode || s.mode === 'both' || s.mode === mode;
  });
}

const dateSelect = document.getElementById('scheduleDateSelect');
const timeSelect = document.getElementById('scheduleTimeSelect');
const scheduleInput = document.getElementById('scheduleAtInput');
const durationInput = document.getElementById('durationMinutesInput');
const methodSelect = document.getElementById('methodSelect');
const step2EmptySlot = document.getElementById('step2EmptySlot');
const slotSummaryPanel = document.getElementById('slotSummaryPanel');

function resetTimeSlot() {
  scheduleInput.value = '';
  if (durationInput) durationInput.value = '';
  updateSlotDurationLabel(null);
  updateRecap();
}

function updateSlotDurationLabel(duration) {
  const label = document.getElementById('slotDurationLabel');
  if (label) label.textContent = duration ? duration + ' menit' : '-';
}

function populateTimeSlots(selectedDate, slots) {
  timeSelect.innerHTML = '<option value="">Pilih jam</option>';
  resetTimeSlot();
  slots.filter(slot => slot.date === selectedDate).forEach(function(slot) {
    const option = document.createElement('option');
    option.value = slot.datetime;
    option.textContent = slot.time;
    option.dataset.duration = slot.duration_minutes || 0;
    timeSelect.appendChild(option);
  });
  timeSelect.disabled = !selectedDate;
  const nextBtn = document.getElementById('btnStep2Next');
  if (nextBtn) nextBtn.disabled = true;
  updatePriceEstimate();
  updateRecap();
}

function populateDateOptions(slots) {
  const dates = {};
  slots.forEach(function(slot) { dates[slot.date] = slot.date_label; });
  dateSelect.innerHTML = '<option value="">Pilih hari</option>';
  Object.keys(dates).forEach(function(date) {
    const option = document.createElement('option');
    option.value = date;
    option.textContent = dates[date];
    dateSelect.appendChild(option);
  });
  dateSelect.disabled = slots.length === 0;
  timeSelect.disabled = slots.length === 0;
  if (step2EmptySlot) step2EmptySlot.style.display = slots.length === 0 ? 'block' : 'none';
  const chosen = Object.keys(dates)[0] || '';
  dateSelect.value = chosen;
  populateTimeSlots(chosen, slots);
}

function renderSlotControls(providerId) {
  const slots = slotCache[providerId] || [];
  populateDateOptions(slotsForMethod(slots, methodSelect.value));
  updateMethodPriceHints(providerId);
}

function loadSlots(providerId) {
  if (slotCache[providerId]) {
    renderSlotControls(providerId);
    return;
  }
  dateSelect.innerHTML = '<option value="">Memuat jadwal&hellip;</option>';
  dateSelect.disabled = true;
  timeSelect.disabled = true;
  fetch(API_SLOTS_URL + '?provider_id=' + providerId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.ok) {
        slotCache[providerId] = data.slots || [];
      } else {
        slotCache[providerId] = [];
      }
      renderSlotControls(providerId);
    })
    .catch(function() {
      slotCache[providerId] = [];
      renderSlotControls(providerId);
    });
}

function updateMethodPriceHints(providerId) {
  const p = providerById(providerId);
  const tier = p ? p.tier : selectedProviderTier;
  const table = tierTables[tier] || rateTable;
  document.querySelectorAll('.method-tile').forEach(function(tile) {
    const key = tile.dataset.priceKey;
    const price = table['individual']?.[key] || 0;
    const el = tile.querySelector('.method-tile-price');
    if (el) el.textContent = price > 0 ? 'Rp' + price.toLocaleString('id-ID') : 'Cek tarif';
  });
}

function selectProvider(providerId) {
  selectedProviderId = providerId;
  const input = document.getElementById('providerIdInput');
  if (input) input.value = providerId;

  document.querySelectorAll('.provider-card').forEach(function(card) {
    const isSel = parseInt(card.dataset.id, 10) === providerId;
    card.classList.toggle('selected', isSel);
    card.setAttribute('aria-pressed', isSel ? 'true' : 'false');
  });

  const p = providerById(providerId);
  if (p) selectedProviderTier = p.tier;
  updateSidebarProvider(p);
  const btn1 = document.getElementById('btnStep1Next');
  if (btn1) btn1.disabled = false;

  resetTimeSlot();
  renderSlotControls(providerId);
  loadSlots(providerId);
}

function updateSidebarProvider(p) {
  const photo = document.getElementById('sumPhoto');
  const fallback = document.getElementById('sumPhotoFallback');
  if (p) {
    document.getElementById('sumProviderName').textContent = p.name;
    document.getElementById('sumProviderRole').textContent = p.role;
    document.getElementById('sumProviderSpec').textContent = p.specialization || 'Konseling umum';
    if (p.photo) {
      photo.src = p.photo.indexOf('://') !== -1 || p.photo.startsWith('/') ? p.photo : '<?= base_url('/') ?>' + p.photo;
      photo.style.display = '';
      if (fallback) fallback.style.display = 'none';
    } else {
      photo.style.display = 'none';
      if (fallback) { fallback.style.display = ''; fallback.textContent = (p.name || '?').charAt(0); }
    }
    updateMethodPriceHints(p.id);
  } else {
    document.getElementById('sumProviderName').textContent = 'Belum ada psikolog dipilih';
    document.getElementById('sumProviderRole').textContent = '-';
    document.getElementById('sumProviderSpec').textContent = '';
  }
}

const methodLabels = { video: 'Video Call', voice: 'Voice Call', tatap_muka: 'Tatap Muka' };
const sessionLabels = { individual: 'Individu', couple: 'Pasangan', family: 'Keluarga' };

function updateRecap() {
  const selDate = dateSelect?.value || '';
  const selTime = timeSelect?.selectedOptions[0]?.textContent || '';
  const scheduleEl = document.getElementById('sumSchedule');
  if (scheduleEl) {
    scheduleEl.textContent = (selDate && selTime) ? selDate + ' · ' + selTime : 'Pilih jadwal';
  }
  const methodEl = document.getElementById('sumMethod');
  if (methodEl) methodEl.textContent = methodLabels[methodSelect.value] || 'Pilih metode';
  const durEl = document.getElementById('sumDuration');
  if (durEl) durEl.textContent = durationInput?.value ? durationInput.value + ' menit' : '-';
  const stEl = document.getElementById('sumSessionType');
  if (stEl) stEl.textContent = sessionLabels[document.getElementById('sessionTypeSelect')?.value] || 'Individu';
  const priceEl = document.getElementById('sumPrice');
  const estEl = document.getElementById('priceEstimate');
  if (priceEl && estEl) priceEl.textContent = estEl.textContent;
  const sumOrigEl = document.getElementById('sumPriceOriginal');
  const origEl = document.getElementById('priceOriginal');
  if (sumOrigEl && origEl) {
    if (origEl.style.display !== 'none' && origEl.textContent) {
      sumOrigEl.style.display = 'block';
      sumOrigEl.textContent = origEl.textContent;
    } else {
      sumOrigEl.style.display = 'none';
      sumOrigEl.textContent = '';
    }
  }
  const promoEl = document.getElementById('sumPromoNote');
  const noteEl = document.getElementById('promoActiveNote');
  if (promoEl) promoEl.innerHTML = noteEl && noteEl.style.display !== 'none' ? noteEl.innerHTML : '&nbsp;';

  const scheduleFilled = !!scheduleInput.value;
  const btn2 = document.getElementById('btnStep2Next');
  if (btn2) btn2.disabled = !scheduleFilled;
}

function goStep(n) {
  [1, 2, 3].forEach(function(s) {
    const el = document.getElementById('step' + s);
    if (el) el.style.display = (s === n) ? '' : 'none';
  });
  document.querySelectorAll('.booking-step').forEach(function(li) {
    const s = parseInt(li.dataset.step, 10);
    li.classList.toggle('active', s === n);
    li.classList.toggle('done', s < n);
  });
  if (n === 1) {
    window.location.hash = 'step1';
    const first = document.getElementById('providerSearch');
    if (first) first.focus();
  }
  updateRecap();
}

function markMethodTile() {
  const val = methodSelect.value;
  document.querySelectorAll('.method-tile').forEach(function(tile) {
    const on = tile.dataset.method === val;
    tile.classList.toggle('selected', on);
    tile.setAttribute('aria-pressed', on ? 'true' : 'false');
  });
}

document.getElementById('btnStep1Next')?.addEventListener('click', function() {
  if (selectedProviderId) goStep(2);
});
document.getElementById('btnStep2Next')?.addEventListener('click', function() {
  if (scheduleInput && scheduleInput.value) goStep(3);
});

document.getElementById('providerSearch')?.addEventListener('input', function() {
  const q = this.value.trim().toLowerCase();
  document.querySelectorAll('.provider-card').forEach(function(card) {
    const text = (card.dataset.name + ' ' + card.dataset.role + ' ' + card.dataset.spec).toLowerCase();
    card.style.display = text.indexOf(q) !== -1 ? '' : 'none';
  });
});

document.querySelectorAll('.provider-card').forEach(function(card) {
  card.addEventListener('click', function() {
    selectProvider(parseInt(this.dataset.id, 10));
    const nextBtn = document.getElementById('btnStep1Next');
    if (nextBtn) nextBtn.disabled = false;
    nextBtn?.focus();
  });
});

document.querySelectorAll('.method-tile').forEach(function(tile) {
  tile.addEventListener('click', function() {
    methodSelect.value = this.dataset.method;
    markMethodTile();
    const slots = slotCache[selectedProviderId] || [];
    populateDateOptions(slotsForMethod(slots, methodSelect.value));
    updatePriceEstimate();
    updateRecap();
  });
});

dateSelect?.addEventListener('change', function() {
  const slots = slotCache[selectedProviderId] || [];
  populateTimeSlots(this.value, slotsForMethod(slots, methodSelect.value));
});
timeSelect?.addEventListener('change', function() {
  scheduleInput.value = this.value;
  const duration = Number(this.selectedOptions[0]?.dataset?.duration || 0);
  if (durationInput) durationInput.value = duration;
  updateSlotDurationLabel(duration);
  const slotStartLabel = document.getElementById('slotStartLabel');
  const slotEndLabel = document.getElementById('slotEndLabel');
  if (slotStartLabel && slotEndLabel && slotSummaryPanel && this.value) {
    const slotStart = new Date(this.value);
    const slotEnd = new Date(slotStart.getTime() + duration * 60000);
    slotStartLabel.textContent = slotStart.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    slotEndLabel.textContent = slotEnd.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    slotSummaryPanel.style.display = duration > 0 ? 'block' : 'none';
  }
  updatePriceEstimate();
  updateRecap();
});

document.querySelectorAll('input[name="previous_session"]').forEach(function(r) {
  r.addEventListener('change', updatePreviousSessionDocumentVisibility);
});

document.querySelectorAll('.booking-step').forEach(function(li) {
  li.addEventListener('click', function() {
    const n = parseInt(this.dataset.step, 10);
    if (n === 1) { goStep(1); return; }
    if (n === 2) {
      if (selectedProviderId) { goStep(2); return; }
      Swal.fire({ icon: 'warning', title: 'Pilih psikolog terlebih dahulu', text: 'Langkah pertama adalah memilih tenaga profesional.' });
      return;
    }
    if (n === 3) {
      if (selectedProviderId && scheduleInput && scheduleInput.value) { goStep(3); return; }
      goStep(selectedProviderId ? 2 : 1);
      Swal.fire({ icon: 'warning', title: 'Lengkapi jadwal dulu', text: 'Pilih hari dan jam sesi sebelum melihat ringkasan akhir.' });
    }
  });
});

function formatRp(n) { return 'Rp' + (n || 0).toLocaleString('id-ID'); }

document.getElementById('consultationForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  if (selectedProviderId && scheduleInput && !scheduleInput.value) {
    goStep(2);
    Swal.fire({ icon: 'warning', title: 'Jadwal belum dipilih', text: 'Silakan pilih hari dan jam sesi.' });
    return;
  }
  const p = providerById(selectedProviderId);
  const scheduleLabel = document.getElementById('sumSchedule')?.textContent || '-';
  const methodLabel = document.getElementById('sumMethod')?.textContent || '-';
  const est = document.getElementById('priceEstimate')?.textContent || '-';
  const html =
    '<div style="text-align:left;line-height:1.8;">' +
    '<div><strong>Psikolog:</strong> ' + (p ? p.name : '-') + '</div>' +
    '<div><strong>Jadwal:</strong> ' + scheduleLabel + '</div>' +
    '<div><strong>Metode:</strong> ' + methodLabel + '</div>' +
    '<div><strong>Perkiraan biaya:</strong> ' + est + '</div>' +
    '</div>' +
    '<p class="small text-muted mt-2 mb-0">Status akan menjadi <strong>Menunggu</strong> hingga psikolog menyetujui sesi Anda.</p>';

  const submitBtn = form.querySelector('button[type="submit"]');
  Swal.fire({
    title: 'Periksa Kembali Booking Anda',
    html: html,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Kirim Permintaan',
    cancelButtonText: 'Koreksi Dulu'
  }).then(function(result) {
    if (result.isConfirmed) {
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';
      }
      form.submit();
    }
  });
});

document.addEventListener('DOMContentLoaded', function() {
  updatePriceEstimate();
  updatePatientRoleVisibility();
  updatePreviousSessionDocumentVisibility();
  markMethodTile();

  if (preSelectedId > 0) {
    selectProvider(preSelectedId);
    goStep(2);
    const nextBtn2 = document.getElementById('btnStep2Next');
    if (nextBtn2) nextBtn2.disabled = !(scheduleInput && scheduleInput.value);
  } else {
    goStep(1);
  }
});
</script>

<style>
.booking-stepper { list-style:none; display:flex; align-items:flex-start; gap:0; margin:0 0 1.25rem; padding:0; position:relative; }
.booking-stepper::before { content:''; position:absolute; top:18px; left:18px; right:18px; height:2px; background:#e2e9e5; z-index:0; }
.booking-step { position:relative; z-index:1; flex:1; text-align:center; }
.booking-step-dot { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; background:#eef3f0; color:#8aa096; font-weight:800; border:2px solid #d9e3dc; transition:all .2s; }
.booking-step.active .booking-step-dot { background:var(--nfg-teal-600,#0a7d5a); border-color:var(--nfg-teal-600,#0a7d5a); color:#fff; }
.booking-step.done .booking-step-dot { background:#0a7d33; border-color:#0a7d33; color:#fff; }
.booking-step-label { margin-top:.5rem; }
.booking-step-title { display:block; font-size:.9rem; font-weight:800; color:var(--ink,#25313c); }
.booking-step.active .booking-step-title { color:var(--nfg-teal-700,#0a7d5a); }
.booking-step-sub { display:block; font-size:.75rem; color:#8aa096; }
.booking-step { cursor:pointer; }

.provider-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:.65rem; max-height:420px; overflow-y:auto; padding:2px; }
.provider-card { position:relative; display:flex; gap:.75rem; text-align:left; border:1.5px solid #e2e9e5; border-radius:12px; padding:.75rem; background:#fff; cursor:pointer; transition:border-color .15s, box-shadow .15s, transform .1s; }
.provider-card:hover { border-color:#b8d4c6; box-shadow:0 4px 14px rgba(15,32,48,.08); }
.provider-card.selected { border-color:var(--nfg-teal-600,#0a7d5a); background:#f0faf5; box-shadow:0 2px 10px rgba(10,125,90,.12); }
.provider-card-photo { flex-shrink:0; width:52px; height:52px; }
.provider-card-photo img { width:52px; height:52px; border-radius:50%; object-fit:cover; }
.provider-card-photo .avatar-circle { width:52px; height:52px; }
.provider-card-body { flex:1; min-width:0; display:flex; flex-direction:column; }
.provider-card-role { font-size:.7rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:var(--nfg-teal-600,#0a7d5a); }
.provider-card-name { font-size:.95rem; font-weight:800; color:var(--ink,#25313c); line-height:1.2; }
.provider-card-spec { font-size:.78rem; color:#667085; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.provider-card-meta { display:flex; justify-content:space-between; align-items:center; margin-top:auto; }
.provider-card-stars { font-size:.78rem; color:#f59e0b; }
.provider-card-stars em { font-style:normal; color:#8aa096; }
.provider-card-price { font-size:.78rem; color:#0a7d33; }
.provider-card-check { position:absolute; top:.55rem; right:.55rem; width:22px; height:22px; border-radius:50%; background:#d9e3dc; color:#fff; display:none; align-items:center; justify-content:center; font-size:.7rem; }
.provider-card.selected .provider-card-check { display:inline-flex; background:var(--nfg-teal-600,#0a7d5a); }

.method-tiles { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:.6rem; }
.method-tile { position:relative; display:flex; gap:.7rem; align-items:center; border:1.5px solid #e2e9e5; border-radius:12px; padding:.7rem .8rem; background:#fff; cursor:pointer; text-align:left; transition:border-color .15s, box-shadow .15s; }
.method-tile:hover { border-color:#b8d4c6; }
.method-tile.selected { border-color:var(--nfg-teal-600,#0a7d5a); background:#f0faf5; box-shadow:0 2px 10px rgba(10,125,90,.12); }
.method-tile-icon { flex-shrink:0; width:38px; height:38px; border-radius:10px; background:#e8f4f0; color:var(--nfg-teal-700,#0a7d5a); display:flex; align-items:center; justify-content:center; font-size:1rem; }
.method-tile-name { display:block; font-weight:800; font-size:.9rem; color:var(--ink,#25313c); }
.method-tile-hint { display:block; font-size:.72rem; color:#8aa096; }
.method-tile-price { display:block; font-size:.78rem; font-weight:700; color:#0a7d33; margin-top:.15rem; }
.method-tile-check { position:absolute; top:.45rem; right:.45rem; width:20px; height:20px; border-radius:50%; background:#d9e3dc; color:#fff; display:none; align-items:center; justify-content:center; font-size:.65rem; }
.method-tile.selected .method-tile-check { display:inline-flex; background:var(--nfg-teal-600,#0a7d5a); }

.booking-summary { position:sticky; top:1rem; background:#fff; border:1px solid #e2e9e5; border-radius:16px; padding:1.15rem; box-shadow:0 8px 24px rgba(15,32,48,.06); }
.booking-summary-head { display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem; }
.booking-summary-provider { display:flex; gap:.75rem; min-width:0; }
.booking-summary-photo { flex-shrink:0; width:52px; height:52px; }
.booking-summary-photo img { width:52px; height:52px; border-radius:50%; object-fit:cover; }
.booking-summary-photo .avatar-circle { width:52px; height:52px; font-size:1.3rem; }
.booking-summary-rows .booking-summary-row { display:flex; justify-content:space-between; gap:1rem; padding:.45rem 0; border-bottom:1px dashed #e2e9e5; }
.booking-summary-row .label { font-size:.8rem; color:#8aa096; }
.booking-summary-row .value { font-size:.85rem; font-weight:700; color:var(--ink,#25313c); text-align:right; }
.booking-summary-total { background:#f4faf7; border:1px solid #cfe5db; border-radius:12px; padding:.85rem 1rem; }
.wizard-step { min-height:120px; }
@media (max-width:767px) {
  .booking-step-sub { display:none; }
  .booking-step-title { font-size:.78rem; }
}
</style>