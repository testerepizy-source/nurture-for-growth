<?php
  $isSelfCare = stripos($assessment['title'], 'Self-Care') !== false;
  $selfCareSections = [1 => 'Physical Self-Care', 11 => 'Psychological / Emotional Self-Care', 22 => 'Social Self-Care', 32 => 'Spiritual Self-Care', 41 => 'Professional Self-Care'];
?>
<div class="assessment-page <?= $isSelfCare ? 'self-care-reference-page' : '' ?>">
  <div class="card border-0 shadow-sm overflow-hidden mb-4 assessment-hero">
    <div class="card-body p-4 p-lg-5">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div class="flex-grow-1">
          <div class="badge rounded-pill px-3 py-2 mb-3 fw-semibold" style="background:rgba(14,124,134,.12);color:var(--nfg-teal-700)">
            <i class="fas fa-heart-pulse me-1"></i>Self-care reflection
          </div>
          <h3 class="fw-bold mb-2"><?= e($assessment['title']) ?></h3>
          <?php if ($isSelfCare): ?>
            <div class="self-care-introduction">
              <p><strong>Self-care activities</strong> are the things you do to maintain good health and improve well-being. You’ll find that many of these activities are things you already do as part of your normal routine.</p>
              <p>In this assessment you will think about how frequently, or how well, you are performing different self-care activities. The goal of this assessment is to help you learn about your self-care needs by spotting patterns and recognizing areas of your life that need more attention.</p>
              <p>There are no right or wrong answers on this assessment. There may be activities that you have no interest in, and other activities may not be included. This list is not comprehensive, but serves as a starting point for thinking about your self-care needs.</p>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0"><?= e($assessment['description']) ?></p>
          <?php endif; ?>
        </div>
        <div class="text-end">
          <div class="small text-uppercase fw-semibold text-muted">Progress</div>
          <div class="display-6 fw-bold" id="progressCounter" style="color:var(--nfg-teal-700)">0/<?= count($assessment['questions']) ?></div>
        </div>
      </div>

      <div class="mt-4">
        <div class="progress rounded-pill" style="height:10px;background:rgba(14,124,134,.12)">
          <div class="progress-bar" id="progressBar" role="progressbar" style="width:0%;background:linear-gradient(90deg,var(--nfg-teal-600),var(--nfg-teal-700))"></div>
        </div>
        <div class="small text-muted mt-2">Pilih satu jawaban untuk setiap pernyataan agar hasil lebih akurat.</div>
      </div>
    </div>
  </div>

  <div class="disclaimer-box mb-4">
    <i class="fas fa-circle-info me-1"></i> Jawablah sesuai dengan apa yang Anda rasakan dalam 2 minggu terakhir. Hasil bukan merupakan diagnosis medis.
  </div>

  <?php if ($isSelfCare): ?>
    <div class="self-care-scale card border-0 shadow-sm mb-4">
      <div class="card-body p-3 p-lg-4">
        <div class="self-care-scale-heading"><i class="fas fa-scale-balanced"></i><div><h6>Skala Penilaian</h6><p>Pilih satu jawaban yang paling sesuai untuk setiap aktivitas.</p></div></div>
        <div class="self-care-scale-grid">
          <div><b>1</b><span>I do this poorly</span><small>I do this rarely or not at all</small></div>
          <div><b>2</b><span>I do this OK</span><small>I do this sometimes</small></div>
          <div><b>3</b><span>I do this well</span><small>I do this often</small></div>
          <div><b>★</b><span>I would like to improve at this</span><small>I would like to do this more frequently</small></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($assessment['time_limit_minutes']): ?>
    <div class="alert alert-info small py-2 mb-4" id="timerBox">
      <i class="fas fa-hourglass-half me-1"></i> Sisa waktu: <span id="timerDisplay"><?= (int) $assessment['time_limit_minutes'] ?>:00</span>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= base_url('/assessment/' . $assessment['id'] . '/submit') ?>" id="assessmentForm" class="<?= $isSelfCare ? 'self-care-reference-form' : '' ?>">
    <?= \Core\Csrf::field() ?>
    <?php foreach ($assessment['questions'] as $i => $q): ?>
      <?php if ($isSelfCare && isset($selfCareSections[(int) $q['order_no']])): ?>
        <div class="self-care-category"><span>1&nbsp;&nbsp;2&nbsp;&nbsp;3&nbsp;&nbsp;★</span><strong><?= e($selfCareSections[(int) $q['order_no']]) ?></strong></div>
      <?php endif; ?>
      <div class="<?= $isSelfCare ? 'self-care-question' : 'card border-0 shadow-sm mb-3 question-card' ?> question-card">
        <div class="<?= $isSelfCare ? 'self-care-question-inner' : 'card-body p-3 p-lg-4' ?>">
          <?php if (!$isSelfCare): ?>
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="question-number" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
              <div class="flex-grow-1"><label class="fw-semibold mb-0 d-block" for="question-<?= $q['id'] ?>"><?= e($q['question_text']) ?></label></div>
            </div>
          <?php endif; ?>
          <?php if ($isSelfCare): ?><div class="self-care-question-text"><?= e($q['question_text']) ?></div><?php endif; ?>

          <div class="<?= $isSelfCare ? 'self-care-options' : 'options-grid' ?>">
            <?php foreach ($q['options'] as $opt): ?>
              <label class="<?= $isSelfCare ? 'self-care-option' : 'option-card' ?>" for="opt<?= $opt['id'] ?>">
                <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>" id="opt<?= $opt['id'] ?>" required>
                <?php if ($isSelfCare): ?><span class="self-care-option-number"><?= (int) $opt['order_no'] === 4 ? '★' : (int) $opt['order_no'] ?></span><?php endif; ?>
                <span class="<?= $isSelfCare ? 'self-care-option-text' : '' ?>"><?= e($opt['option_text']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="d-flex gap-2 flex-wrap mt-4">
      <button type="submit" class="btn btn-primary px-4">Selesai &amp; Lihat Hasil</button>
      <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">Simpan Draft</button>
    </div>
  </form>
</div>

<style>
.assessment-page .assessment-hero {
  background: linear-gradient(135deg, rgba(14,124,134,.08), rgba(12,74,110,.04));
  border: 1px solid rgba(14,124,134,.12);
}
.assessment-page .question-number {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--nfg-teal-600), var(--nfg-teal-700));
  color: #fff;
  font-weight: 700;
  font-size: .95rem;
  flex-shrink: 0;
}
.assessment-page .options-grid {
  display: grid;
  gap: .75rem;
}
.assessment-page .option-card {
  display: flex;
  align-items: center;
  gap: .65rem;
  padding: .85rem 1rem;
  border: 1px solid #e7eef3;
  border-radius: .9rem;
  background: #fff;
  cursor: pointer;
  transition: all .2s ease;
}
.assessment-page .option-card:hover {
  border-color: rgba(14,124,134,.35);
  box-shadow: 0 8px 24px rgba(14,124,134,.08);
  transform: translateY(-1px);
}
.assessment-page .option-card input {
  margin-top: 0;
  accent-color: var(--nfg-teal-700);
}
.assessment-page .option-card:has(input:checked) {
  border-color: var(--nfg-teal-700);
  background: rgba(14,124,134,.06);
  box-shadow: inset 0 0 0 1px rgba(14,124,134,.12);
}
.assessment-page .self-care-scale{ border:1px solid rgba(14,124,134,.15); background:#fff; }
.assessment-page .self-care-scale-heading{ display:flex; align-items:flex-start; gap:11px; margin-bottom:16px; }
.assessment-page .self-care-scale-heading > i{ color:var(--nfg-teal-700); font-size:1.15rem; margin-top:2px; }
.assessment-page .self-care-scale-heading h6{ margin:0 0 3px; font-weight:800; }
.assessment-page .self-care-scale-heading p{ margin:0; color:#687983; font-size:.8rem; }
.assessment-page .self-care-scale-grid{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border:1px solid #dfe6e8; border-radius:10px; overflow:hidden; }
.assessment-page .self-care-scale-grid > div{ display:flex; flex-direction:column; gap:3px; padding:12px 14px; background:#f5f7f7; border-right:1px solid #dfe6e8; }
.assessment-page .self-care-scale-grid > div:last-child{ border-right:0; }
.assessment-page .self-care-scale-grid b{ color:var(--nfg-teal-700); font-size:1.1rem; }
.assessment-page .self-care-scale-grid span{ color:#152b32; font-size:.78rem; font-weight:700; line-height:1.35; }
.assessment-page .self-care-scale-grid small{ color:#687983; font-size:.7rem; line-height:1.35; }
.assessment-page .self-care-category{ display:flex; align-items:center; gap:16px; margin:30px 0 7px; padding:0 8px; color:#172b30; }
.assessment-page .self-care-category span{ font-weight:800; letter-spacing:.05em; }
.assessment-page .self-care-category strong{ font-size:1rem; }
.assessment-page .self-care-question{ margin:0; padding:0 10px; background:#fff; border:0; }
.assessment-page .self-care-question:nth-of-type(even){ background:#f1f3f3; }
.assessment-page .self-care-question-inner{ display:grid; grid-template-columns:minmax(260px,1fr) minmax(360px,1.55fr); align-items:center; gap:18px; min-height:46px; padding:6px 10px; }
.assessment-page .self-care-question-text{ color:#1e3035; font-size:.84rem; line-height:1.35; }
.assessment-page .self-care-options{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:7px; }
.assessment-page .self-care-option{ display:flex; align-items:center; justify-content:center; gap:5px; min-height:31px; margin:0; padding:4px 5px; border:1px solid #bac6c9; border-radius:5px; background:#fff; color:#273a3e; cursor:pointer; transition:all .16s ease; }
.assessment-page .self-care-option input{ position:absolute; opacity:0; pointer-events:none; }
.assessment-page .self-care-option-number{ display:none; }
.assessment-page .self-care-option-text{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.68rem; }
.assessment-page .self-care-option:has(input:checked){ background:var(--nfg-teal-700); border-color:var(--nfg-teal-700); color:#fff; box-shadow:0 3px 9px rgba(14,124,134,.2); }
.assessment-page .self-care-option:hover{ border-color:var(--nfg-teal-700); }
.self-care-reference-page{ max-width:760px; margin-inline:auto; color:#111; }
.self-care-reference-page .assessment-hero{ position:relative; background:#fff; border:0; box-shadow:none!important; margin-bottom:18px!important; padding-top:8px; text-align:center; }
.self-care-reference-page .assessment-hero::before{ content:''; display:block; width:56px; height:4px; margin:0 auto 16px; border-radius:99px; background:var(--nfg-teal-600); }
.self-care-reference-page .assessment-hero .card-body{ padding:0 0 12px!important; }
.self-care-reference-page .assessment-hero .badge,.self-care-reference-page .assessment-hero .text-end,.self-care-reference-page .assessment-hero > .card-body > .mt-4,.self-care-reference-page > .disclaimer-box,.self-care-reference-page > #timerBox{ display:none; }
.self-care-reference-page .assessment-hero h3{ margin:0 0 25px!important; color:#0b0b0b; font-size:1.65rem; font-weight:800; letter-spacing:-.01em; }
.self-care-introduction{ max-width:680px; margin:0 auto; padding:18px 22px 6px; border-left:3px solid rgba(14,124,134,.35); text-align:justify; color:#28383c; font-size:.84rem; line-height:1.65; background:linear-gradient(90deg,rgba(14,124,134,.045),transparent 72%); }
.self-care-introduction p{ margin:0 0 12px; }
.self-care-introduction p:last-child{ margin-bottom:0; }
.self-care-reference-page .self-care-scale{ margin:30px auto 34px!important; max-width:760px; border:0; box-shadow:0 8px 24px rgba(24,52,56,.08)!important; }
.self-care-reference-page .self-care-scale .card-body{ padding:0!important; }
.self-care-reference-page .self-care-scale-heading{ display:none; }
.self-care-reference-page .self-care-scale-grid{ display:block; border:0; border-radius:3px; }
.self-care-reference-page .self-care-scale-grid > div{ display:grid; grid-template-columns:28px minmax(0,1fr) minmax(0,1fr); align-items:center; column-gap:8px; padding:8px 12px; border-right:0; border-bottom:1px solid #d1dadd; background:#f3f6f6; }
.self-care-reference-page .self-care-scale-grid > div:last-child{ border-bottom:0; }
.self-care-reference-page .self-care-scale-grid b{ color:#111; font-size:.78rem; text-align:center; }
.self-care-reference-page .self-care-scale-grid span{ color:#111; font-size:.76rem; font-weight:400; }
.self-care-reference-page .self-care-scale-grid small{ color:#111; font-size:.76rem; text-align:right; }
.self-care-reference-page .self-care-category{ margin:36px 0 8px; padding:0 10px 9px; gap:14px; border-bottom:1px solid rgba(14,124,134,.22); }
.self-care-reference-page .self-care-category span{ color:var(--nfg-teal-700); font-size:.82rem; letter-spacing:.08em; }
.self-care-reference-page .self-care-category strong{ color:#172b30; font-size:.9rem; }
.self-care-reference-page .self-care-question{ padding:0 4px; background:#f3f5f5; border-radius:4px; }
.self-care-reference-page .self-care-question:nth-of-type(even){ background:#fff; }
.self-care-reference-page .self-care-question:hover{ background:#eaf5f3; }
.self-care-reference-page .self-care-question-inner{ grid-template-columns:126px minmax(0,1fr); gap:9px; min-height:38px; padding:4px 0; }
.self-care-reference-page .self-care-question-text{ font-size:.76rem; line-height:1.35; text-align:justify; }
.self-care-reference-page .self-care-options{ grid-column:1; grid-row:1; display:grid; grid-template-columns:repeat(4,29px); gap:3px; }
.self-care-reference-page .self-care-option{ width:29px; min-height:27px; height:27px; padding:0; border:2px solid #587278; border-radius:6px; background:#fff; }
.self-care-reference-page .self-care-option-number{ display:block; color:#111; font-size:0; }
.self-care-reference-page .self-care-option-number::after{ content:''; display:block; }
.self-care-reference-page .self-care-option-text{ display:none; }
.self-care-reference-page .self-care-option:has(input:checked){ background:var(--nfg-teal-700); border-color:var(--nfg-teal-700); box-shadow:0 3px 8px rgba(14,124,134,.22); color:#fff; }
.self-care-reference-page .self-care-option:has(input:checked) .self-care-option-number{ color:#fff; font-size:.75rem; }
.self-care-reference-page .self-care-option:has(input:checked) .self-care-option-number::after{ display:none; }
.self-care-reference-page .self-care-question-text{ grid-column:2; grid-row:1; }
.self-care-reference-page .self-care-question .mb-3{ display:none; }
.self-care-reference-page .self-care-reference-form{ margin-top:0; }
.self-care-reference-page .self-care-reference-form > .d-flex{ margin-top:26px!important; }
.self-care-reference-page .self-care-reference-form > .d-flex .btn{ font-size:.8rem; }
.self-care-reference-page .self-care-reference-form > .d-flex .btn-outline-secondary{ display:none; }
@media (max-width: 767.98px){
  .assessment-page .self-care-scale-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
  .assessment-page .self-care-scale-grid > div:nth-child(2){ border-right:0; }
  .assessment-page .self-care-scale-grid > div:nth-child(-n+2){ border-bottom:1px solid #dfe6e8; }
  .assessment-page .self-care-category{ gap:10px; margin-top:24px; }
  .assessment-page .self-care-category strong{ font-size:.88rem; }
  .assessment-page .self-care-question{ padding:0 4px; }
  .assessment-page .self-care-question-inner{ grid-template-columns:1fr; gap:7px; padding:10px 7px; }
  .assessment-page .self-care-options{ gap:5px; }
  .assessment-page .self-care-option{ min-height:38px; }
  .assessment-page .self-care-option-text{ display:none; }
  .assessment-page .self-care-option-number{ display:block; font-size:.84rem; font-weight:800; }
  .self-care-reference-page{ max-width:100%; }
  .self-care-reference-page .assessment-hero h3{ font-size:1.3rem; }
  .self-care-reference-page .self-care-introduction{ font-size:.8rem; padding-inline:16px; }
  .self-care-reference-page .self-care-scale{ max-width:100%; }
  .self-care-reference-page .self-care-scale-grid{ grid-template-columns:1fr 1.5fr 1.8fr; }
  .self-care-reference-page .self-care-question-inner{ grid-template-columns:116px minmax(0,1fr); }
}
@media (min-width: 768px) {
  .assessment-page .options-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>

<script>
function saveDraft() {
  Swal ? Swal.fire('Draft disimpan sementara di browser Anda.', '', 'success') : alert('Draft disimpan sementara.');
}

function updateProgress() {
  const questionCards = document.querySelectorAll('.question-card');
  let answered = 0;
  questionCards.forEach(function (card) {
    if (card.querySelector('input[type="radio"]:checked')) {
      answered++;
    }
  });
  const total = questionCards.length;
  const progress = total ? Math.round((answered / total) * 100) : 0;
  const counter = document.getElementById('progressCounter');
  const bar = document.getElementById('progressBar');
  if (counter) counter.textContent = answered + '/' + total;
  if (bar) bar.style.width = progress + '%';
}

const questionInputs = document.querySelectorAll('.question-card input[type="radio"]');
questionInputs.forEach(function (input) {
  input.addEventListener('change', updateProgress);
});
updateProgress();

<?php if ($assessment['time_limit_minutes']): ?>
let seconds = <?= (int) $assessment['time_limit_minutes'] * 60 ?>;
const timerDisplay = document.getElementById('timerDisplay');
const timer = setInterval(() => {
  seconds--;
  const m = Math.floor(seconds / 60), s = seconds % 60;
  timerDisplay.textContent = m + ':' + String(s).padStart(2, '0');
  if (seconds <= 0) { clearInterval(timer); document.getElementById('assessmentForm').requestSubmit(); }
}, 1000);
<?php endif; ?>
</script>
