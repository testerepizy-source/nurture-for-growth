<section class="container py-5">
<div class="text-center mb-5">
  <div class="section-eyebrow justify-content-center mb-2">FAQ</div>
  <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
</div>
<?php foreach ($grouped as $category => $items): ?>
  <h6 class="fw-bold mb-3" style="color:#00c79c"><?= e($category) ?></h6>
  <div class="accordion mb-4 mx-auto" style="max-width:760px" id="acc<?= md5($category) ?>">
    <?php foreach ($items as $i => $f): ?>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f<?= $f['id'] ?>"><?= e($f['question']) ?></button>
        </h2>
        <div id="f<?= $f['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#acc<?= md5($category) ?>">
          <div class="accordion-body small text-muted"><?= e($f['answer']) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>
</section>
