<?php $tagCount = 0; ?>
<div class="sme-wrap">
  <!-- Langkah alur -->
  <div class="sme-steps mx-auto">
    <span class="sme-step is-active"><i>1</i>Ceritakan Keluhan</span>
    <span class="sme-step-line"></span>
    <span class="sme-step"><i>2</i>Terima Rekomendasi</span>
    <span class="sme-step-line"></span>
    <span class="sme-step"><i>3</i>Booking Sesi</span>
  </div>

  <div class="sme-hero">
    <div class="sme-hero-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
    <h2 class="mb-2">Sistem Pencocokan Cerdas</h2>
    <p class="text-muted mx-auto mb-0" style="max-width:560px">Tidak perlu bingung harus pilih psikolog seperti apa — cukup ceritakan apa yang Anda rasakan, kami bantu carikan yang paling sesuai.</p>
  </div>

  <div class="disclaimer-box mx-auto mb-4" style="max-width:680px">
    <i class="fas fa-circle-info me-1"></i>
    Ini adalah alat bantu pencocokan berbasis tag &amp; kata kunci (rule-based, dapat ditelusuri), bukan diagnosis. Hasil akhir tetap dapat Anda sesuaikan sendiri.
  </div>

  <form method="POST" action="<?= base_url('/matching') ?>" class="mx-auto sme-form" style="max-width:680px">
    <?= \Core\Csrf::field() ?>

    <div class="card sme-card mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
        <label class="form-label fw-bold mb-0">Apa yang sedang Anda rasakan? <span class="text-muted small fw-normal">(pilih satu atau lebih)</span></label>
        <span class="sme-counter" id="smeCounter"><i class="fas fa-circle-check"></i> 0 dipilih</span>
      </div>
      <div class="sme-tag-grid">
        <?php foreach ($tags as $t): $tagCount++; ?>
          <label class="sme-tag-chip">
            <input type="checkbox" name="tags[]" value="<?= $t['id'] ?>">
            <span class="sme-tag-check"><i class="fas fa-check"></i></span>
            <i class="fas fa-tag sme-tag-icon" aria-hidden="true"></i>
            <span><?= e($t['name']) ?></span>
          </label>
        <?php endforeach; ?>
        <?php if ($tagCount === 0): ?>
          <p class="text-muted small mb-0">Belum ada daftar keluhan yang dikonfigurasi admin.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card sme-card mb-4">
      <label class="form-label fw-bold mb-2 d-flex justify-content-between align-items-center">
        <span>Atau ceritakan singkat kondisi Anda <span class="text-muted small fw-normal">(opsional)</span></span>
        <span class="sme-charcount" id="smeCharcount">0</span>
      </label>
      <textarea name="free_text" id="freeText" class="form-control sme-textarea" rows="4"
                placeholder="Contoh: Belakangan ini saya sulit tidur dan terus memikirkan masalah di rumah tangga..."></textarea>
      <div id="suggestedBox" class="sme-suggest mt-3" hidden></div>
    </div>

    <button type="submit" class="sme-submit-btn w-100"><i class="fas fa-magnifying-glass me-2"></i>Cari yang Paling Sesuai<i class="fas fa-arrow-right ms-2 sme-submit-arrow"></i></button>
    <div class="text-center mt-3">
      <a href="<?= base_url('/consultation/create') ?>" class="small text-muted text-decoration-none">Saya sudah tahu mau pilih siapa, lewati langkah ini <i class="fas fa-angle-right"></i></a>
    </div>
  </form>
</div>

<script>
let debounceTimer;
const freeTextEl = document.getElementById('freeText');
const suggestBox = document.getElementById('suggestedBox');
const counterEl = document.getElementById('smeCounter');
const charEl = document.getElementById('smeCharcount');

function refreshTagCount() {
  const n = document.querySelectorAll('.sme-tag-chip input:checked').length;
  counterEl.innerHTML = '<i class="fas fa-circle-check"></i> ' + n + ' dipilih';
  counterEl.classList.toggle('has-count', n > 0);
}
document.querySelectorAll('.sme-tag-chip input').forEach(cb => cb.addEventListener('change', refreshTagCount));

freeTextEl.addEventListener('input', function () {
  charEl.textContent = this.value.length;
  clearTimeout(debounceTimer);
  const text = this.value;
  if (text.trim().length < 5) { suggestBox.hidden = true; suggestBox.innerHTML = ''; return; }
  debounceTimer = setTimeout(() => {
    fetch(window.NFG_BASE_URL + '/matching/suggest-tags?text=' + encodeURIComponent(text))
      .then(r => r.json())
      .then(data => {
        if (!data.tags || !data.tags.length) { suggestBox.hidden = true; return; }
        window._suggestedTagIds = data.tags.map(t => t.id);
        suggestBox.innerHTML =
          '<div class="sme-suggest-label"><i class="fas fa-lightbulb me-1"></i>Kemungkinan terkait keluhan Anda:</div>' +
          '<div class="sme-suggest-tags">' +
            data.tags.map(t => '<button type="button" class="sme-suggest-chip" data-id="' + t.id + '">' + t.name + ' <i class="fas fa-plus"></i></button>').join('') +
          '</div>' +
          '<button type="button" class="sme-suggest-all" onclick="autoCheckTags()"><i class="fas fa-wand-magic-sparkles me-1"></i>Tandai semua otomatis</button>';
        suggestBox.hidden = false;
        suggestBox.querySelectorAll('.sme-suggest-chip').forEach(btn => {
          btn.addEventListener('click', () => { toggleSuggest(parseInt(btn.dataset.id)); });
        });
      });
  }, 600);
});

function toggleSuggest(id) {
  const cb = document.querySelector('.sme-tag-chip input[value="' + id + '"]');
  if (!cb) return;
  cb.checked = !cb.checked;
  refreshTagCount();
}
function autoCheckTags() {
  if (!window._suggestedTagIds) return;
  document.querySelectorAll('.sme-tag-chip input').forEach(cb => {
    if (window._suggestedTagIds.includes(parseInt(cb.value))) cb.checked = true;
  });
  refreshTagCount();
}
refreshTagCount();
</script>
