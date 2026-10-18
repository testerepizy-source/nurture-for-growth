<section class="container py-5">
  <div class="text-center mb-5">
    <div class="section-eyebrow justify-content-center mb-2">Artikel</div>
    <h2 class="section-title">Wawasan Kesehatan Mental</h2>
  </div>

  <?php if (!empty($keyword)): ?>
    <p class="text-muted small mb-3">Hasil pencarian untuk: "<?= e($keyword) ?>" (<?= count($articles) ?> artikel)</p>
  <?php endif; ?>

  <div class="row g-4">
    <?php if (empty($articles)): ?>
      <p class="text-muted">Belum ada artikel<?= !empty($keyword) ? ' yang cocok dengan pencarian.' : '.' ?></p>
    <?php endif; ?>
    <?php foreach ($articles as $a): ?>
      <div class="col-md-6 col-lg-4">
        <a href="<?= base_url('/education/' . $a['slug']) ?>" class="article-card d-block text-decoration-none">
          <div class="article-card-photo">
            <?php if (!empty($a['thumbnail'])): ?>
              <img src="<?= base_url($a['thumbnail']) ?>" alt="<?= e($a['title']) ?>">
            <?php else: ?>
              <i class="fas fa-book-open"></i>
            <?php endif; ?>
            <span class="article-date-pill"><?= format_date($a['created_at'], 'd M, Y') ?></span>
          </div>
          <div class="article-card-body">
            <h6><?= e($a['title']) ?></h6>
            <div class="article-card-meta">
              <div class="article-card-meta-left">
                <span class="article-cat-pill"><?= e($a['category_name'] ?? ucfirst($a['type'])) ?></span>
              </div>
              <span class="article-arrow-btn"><i class="fas fa-arrow-up-right"></i></span>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>
