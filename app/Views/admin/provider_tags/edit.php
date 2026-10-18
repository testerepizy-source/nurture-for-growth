<h4 class="fw-bold mb-3">Tag Spesialisasi: <?= e($provider['name']) ?></h4>
<div class="card p-3" style="max-width:560px">
  <form method="POST" action="<?= base_url('/admin/provider-tags/' . $provider['id'] . '/update') ?>">
    <?= \Core\Csrf::field() ?>
    <p class="text-muted small">Pilih keluhan/area yang menjadi fokus penanganan tenaga profesional ini. Tag ini dipakai oleh sistem pencocokan cerdas untuk mencocokkan dengan keluhan client.</p>
    <div class="d-flex flex-wrap gap-2 mb-3">
      <?php foreach ($allTags as $t): ?>
        <label class="tag-chip">
          <input type="checkbox" name="tags[]" value="<?= $t['id'] ?>" class="d-none tag-chip-input" <?= in_array($t['id'], $assignedIds) ? 'checked' : '' ?>>
          <span><?= e($t['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-primary btn-sm" type="submit">Simpan Tag Spesialisasi</button>
    <a href="<?= base_url('/admin/psychologists') ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
  </form>
</div>
