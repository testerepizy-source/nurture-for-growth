<div class="card p-4" style="max-width:720px">
  <h5 class="fw-bold"><?= e($journal['title']) ?></h5>
  <p class="text-muted small"><?= format_date($journal['journal_date']) ?></p>
  <p style="white-space:pre-line"><?= e($journal['content']) ?></p>
  <div class="disclaimer-box mt-3"><i class="fas fa-lock me-1"></i>Anda mengakses jurnal ini karena client telah memberikan izin eksplisit.</div>
</div>
