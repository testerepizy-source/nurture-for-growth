<section class="container py-5">
<div class="row g-4">
  <div class="col-lg-8">
    <?php if (!empty($article['thumbnail'])): ?>
      <img src="<?= base_url($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" class="article-hero-img">
    <?php endif; ?>

    <span class="article-cat-pill mb-2 d-inline-block"><?= e($article['category_name'] ?? ucfirst($article['type'])) ?></span>
    <h2 class="section-title mb-3"><?= e($article['title']) ?></h2>

    <div class="article-meta-row">
    <span><?= svg_icon('user',16) ?> Admin</span>
    <span><?= svg_icon('calendar',16) ?> <?= format_date($article['created_at'], 'd M, Y') ?></span>
    <span><?= svg_icon('eye',16) ?> <?= (int) $article['views'] ?> kali dilihat</span>
    </div>

    <div class="article-body">
      <?= nl2br(e($article['content'])) ?>
    </div>

    <?php if (!empty($article['excerpt'])): ?>
    <div class="article-pull-quote">
      <p>&ldquo;<?= e($article['excerpt']) ?>&rdquo;</p>
      <span class="quote-mark">&rdquo;</span>
    </div>
    <?php endif; ?>

    <!-- <div class="article-sidebar-card" style="border-color:#f3c2c2;background:#fdeaea;">
      <h6 class="text-danger mb-2"><i class="fas fa-phone me-1"></i>Kontak Bantuan Darurat</h6>
      <p class="small text-muted mb-2">Jika Anda atau seseorang yang Anda kenal berada dalam krisis, segera hubungi:</p>
      <ul class="small mb-0">
        <?php foreach ($contacts as $c): ?>
          <li><strong><?= e($c['name']) ?></strong>: <?= e($c['phone']) ?> <?php if ($c['description']): ?><span class="text-muted">(<?= e($c['description']) ?>)</span><?php endif; ?></li>
        <?php endforeach; ?>
      </ul>
    </div> -->

    <a href="<?= base_url('/education') ?>" class="btn btn-outline-secondary btn-sm">&larr; Kembali ke Artikel</a>
  </div>

  <div class="col-lg-4">
    <div class="article-sidebar-card">
      <form method="GET" action="<?= base_url('/education') ?>" class="article-sidebar-search">
        <input type="text" name="q" class="form-control" placeholder="Cari Artikel">
        <button type="submit"><?= svg_icon('search',16) ?></button>
      </form>
    </div>

    <?php if (!empty($recent)): ?>
    <div class="article-sidebar-card">
    <h6><?= svg_icon('thumbtack',18) ?> Artikel Terbaru</h6>
      <?php foreach ($recent as $r): ?>
        <a href="<?= base_url('/education/' . $r['slug']) ?>" class="article-sidebar-recent text-decoration-none text-reset">
          <?php if (!empty($r['thumbnail'])): ?>
            <img src="<?= base_url($r['thumbnail']) ?>" alt="">
          <?php else: ?>
            <span class="thumb-fallback"><?= svg_icon('book-open',20) ?></span>
          <?php endif; ?>
          <div>
            <h6><?= e($r['title']) ?></h6>
            <span><?= format_date($r['created_at'], 'd M, Y') ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
    <div class="article-sidebar-card">
    <h6><?= svg_icon('thumbtack',18) ?> Kategori</h6>
      <?php foreach ($categories as $c): ?>
        <a href="<?= base_url('/education') ?>" class="article-sidebar-cat text-decoration-none text-reset">
          <span><?= e($c['name']) ?></span><span class="text-muted">(<?= (int) $c['total'] ?>)</span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($tags)): ?>
    <div class="article-sidebar-card">
    <h6><?= svg_icon('thumbtack',18) ?> Tag Populer</h6>
      <?php foreach ($tags as $t): ?>
        <span class="article-tag-pill"><?= e($t) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
</section>
