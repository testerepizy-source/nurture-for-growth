<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Journal Saya</h4>
  <a href="<?= base_url('/journal/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Tulis Jurnal</a>
</div>

<form method="GET" action="<?= base_url('/journal') ?>" class="mb-3">
  <div class="input-group input-group-sm" style="max-width:320px">
    <input type="text" name="q" class="form-control" placeholder="Cari jurnal..." value="<?= e($search) ?>">
    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
  </div>
</form>

<div class="row g-3">
  <?php if (empty($journals)): ?>
    <p class="text-muted">Belum ada jurnal. Mulai tulis jurnal pertama Anda!</p>
  <?php endif; ?>
  <?php foreach ($journals as $j): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card p-3 h-100">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <h6 class="fw-bold mb-0"><?= e($j['title']) ?></h6>
          <div class="d-flex gap-1">
            <?php if ($j['is_private']): ?><i class="fas fa-lock text-muted" title="Privat"></i><?php else: ?><i class="fas fa-lock-open text-success" title="Dapat diakses tenaga profesional"></i><?php endif; ?>
            <a href="<?= base_url('/journal/' . $j['id'] . '/favorite') ?>" title="Favorit">
              <i class="fa<?= $j['is_favorite'] ? 's' : 'r' ?> fa-star text-warning"></i>
            </a>
          </div>
        </div>
        <div class="small text-muted mb-2"><?= format_date($j['journal_date']) ?><?php if ($j['category']): ?> &middot; <?= e($j['category']) ?><?php endif; ?></div>
        <p class="small text-truncate-3 flex-grow-1"><?= e(mb_substr($j['content'], 0, 140)) ?>...</p>
        <?php if ($j['tag']): ?><div class="mb-2"><span class="badge bg-primary-soft text-primary"><?= e($j['tag']) ?></span></div><?php endif; ?>
        <div class="d-flex gap-2">
          <a href="<?= base_url('/journal/' . $j['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary flex-grow-1">Ubah</a>
          <form method="POST" action="<?= base_url('/journal/' . $j['id'] . '/delete') ?>" id="delJournal<?= $j['id'] ?>">
            <?= \Core\Csrf::field() ?>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('delJournal<?= $j['id'] ?>')"><i class="fas fa-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
