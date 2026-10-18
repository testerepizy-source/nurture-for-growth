<?php
$pageTitle = 'Konseling, Asesmen & Terapi Psikologis Tepercaya';
$themeColor = ['teal' => 'var(--teal)', 'pink' => 'var(--pink)', 'orange' => 'var(--orange)'];
$themeInk   = ['teal' => 'var(--teal-ink)', 'pink' => 'var(--pink)', 'orange' => 'var(--orange)'];
$fallbackHeroImgs = ['hero-3a52c6bf85ad.jpg', 'hero-6bda855b9993.jpg', 'hero-6c09b5bf5789.jpg', 'hero-be7e3bf28b48.jpg'];
if (empty($heroSlides)) {
    $heroSlides = [[
        'badge_text' => 'NFG · NURTURE FOR GROWTH', 'title' => 'Layanan Psikologis untuk <em>Setiap Tahap Kehidupan</em>',
        'subtitle' => 'Konseling, pemeriksaan psikologis, terapi, serta program edukasi bagi anak, remaja, dewasa, pasangan, keluarga, sekolah, dan komunitas.',
        'cta_text' => 'Mulai Sekarang', 'cta_link' => '/register', 'color_theme' => 'teal', 'image_path' => null,
    ]];
}

// Weighted aggregate rating across all providers with published reviews (for the trust band).
$sumRating = 0; $sumCount = 0;
foreach ($reviewSummaries as $rs) { $sumRating += $rs['average'] * $rs['total']; $sumCount += $rs['total']; }
$overallAvg = $sumCount > 0 ? round($sumRating / $sumCount, 1) : null;
?>

<!-- ======================= HERO ======================= -->
<section class="hero-section">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-copy">
        <?php foreach ($heroSlides as $i => $slide):
          $theme = $slide['color_theme'] ?? 'teal';
          $slideImage = !empty($slide['image_path'])
            ? base_url($slide['image_path'])
            : base_url('assets/uploads/hero/' . $fallbackHeroImgs[$i % count($fallbackHeroImgs)]);
        ?>
        <div data-hero-slide data-hero-image="<?= e($slideImage) ?>" style="<?= $i > 0 ? 'display:none' : '' ?>">
          <div class="section-eyebrow" style="color:<?= $themeInk[$theme] ?? 'var(--teal-ink)' ?>">
            <span class="dot-cluster"><span></span><span></span><span></span></span>
            <?= e($slide['badge_text'] ?? 'NFG · NURTURE FOR GROWTH') ?>
          </div>
          <h1 class="hero-title"><?= $slide['title'] /* trusted admin-authored HTML for <em> accent */ ?></h1>
          <p class="hero-lede"><?= e($slide['subtitle'] ?? '') ?></p>
          <div class="hero-cta-row">
            <a href="<?= base_url($slide['cta_link'] ?? '/register') ?>" class="btn-hero">
              <?= e($slide['cta_text'] ?? 'Mulai Sekarang') ?> <span class="icon-box"
                style="background:<?= $themeColor[$theme] ?? 'var(--teal)' ?>"><i class="fas fa-arrow-right"></i></span>
            </a>
            <a href="#layanan" class="btn-outline-price">Lihat Layanan</a>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if (count($heroSlides) > 1): ?>
        <div class="d-flex gap-2 mb-4" aria-hidden="true">
          <?php foreach ($heroSlides as $i => $slide): ?>
          <button type="button" data-hero-dot class="<?= $i === 0 ? 'active' : '' ?>"
            style="width:26px;height:4px;border-radius:2px;border:0;background:<?= $i === 0 ? 'var(--ink)' : 'var(--sand-line)' ?>"
            onclick="this.closest('body') && document.querySelectorAll('[data-hero-dot]').forEach(function(d){d.style.background='var(--sand-line)'}); this.style.background='var(--ink)'"></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="hero-trust">
          <div class="avatar-stack">
            <?php if (!empty($trustedAvatars)): foreach (array_slice($trustedAvatars, 0, 3) as $t): ?>
            <?php if (!empty($t['photo'])): ?>
            <img src="<?= base_url($t['photo']) ?>" alt="<?= e($t['name']) ?>">
            <?php else: ?>
            <span class="avatar-circle-sm"><?= e(mb_substr($t['name'], 0, 1)) ?></span>
            <?php endif; ?>
            <?php endforeach; else: ?>
            <span class="avatar-circle-sm">N</span><span class="avatar-circle-sm">F</span><span
              class="avatar-circle-sm">G</span>
            <?php endif; ?>
          </div>

          <!-- <div class="hero-trust-text">
            <strong><?= (int) $stats['providers'] ?>+ psikolog</strong>
            siap mendampingi Anda<?= $overallAvg ? ' &middot; ' . e((string) $overallAvg) . ' &#9733; dari client kami' : '' ?>
          </div> -->

          <div class="hero-trust-text">
            <strong>Psikolog Profesional</strong>
            Pendampingan berbasis kebutuhan
            Anda<?= $overallAvg ? ' &middot; ' . e((string) $overallAvg) . ' &#9733; dari client kami' : '' ?>
          </div>

        </div>
      </div>

      <div class="hero-visual">
        <div class="blot blot-teal blot-shape blot-1"></div>
        <div class="blot blot-pink blot-shape-alt blot-2"></div>
        <div class="hero-photo-frame">
          <?php $heroImg = !empty($heroSlides[0]['image_path']) ? base_url($heroSlides[0]['image_path']) : base_url('assets/uploads/hero/' . $fallbackHeroImgs[0]); ?>
          <img src="<?= e($heroImg) ?>" alt="Sesi konseling psikologis">
        </div>
        <!-- <div class="hero-floater hero-floater-1">
          <span class="fi-icon" style="background:var(--teal)"><i class="fas fa-calendar-check"></i></span>
          <div><strong><?= (int) $stats['providers'] ?>+</strong><span>Sesi terlaksana</span></div>
        </div> -->

        <div class="hero-floater hero-floater-1">
          <span class="fi-icon" style="background:var(--teal)"><i class="fas fa-calendar-check"></i></span>
          <div><strong>Layanan Online dan Tatap Muka</strong><span>Psikolog Profesional</span></div>
        </div>

        <div class="hero-floater hero-floater-2">
          <span class="fi-icon" style="background:var(--pink)"><i class="fas fa-shield-heart"></i></span>
          <div><strong>Rahasia</strong><span>&amp; aman terjaga</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ======================= PARTNER MARQUEE ======================= -->
<section class="partner-strip">
  <div class="container">
    <div class="partner-strip-label">Dipercaya oleh institusi, komunitas &amp; perusahaan</div>
    <div class="marquee">
      <div class="marquee-track" data-duplicate>
        <?php foreach (['institusi-1','institusi-2','institusi-3','institusi-4','institusi-5','institusi-6','komunitas-1','komunitas-2','komunitas-3','perusahaan-1','perusahaan-2','perusahaan-3'] as $p): ?>
        <img src="<?= asset('img/partners/' . $p . '.png') ?>"
          alt="<?= e(ucfirst(explode('-', $p)[0])) ?> <?= e(explode('-', $p)[1]) ?>" loading="lazy">
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


<!-- ======================= CONCERN TAGS / LAYANAN ======================= -->
<!-- <section class="concern-band" id="layanan">
  <div class="blot blot-orange blot-shape" style="width:260px;height:260px;top:-80px;right:-80px;opacity:.25"></div>
  <div class="container" style="position:relative;z-index:1">
    <div class="text-center mb-2 reveal">
      <div class="section-eyebrow justify-content-center">Kami Membantu Anda Menghadapi</div>
      <h2 class="section-title">Apa pun Keluhannya, Kami <em class="accent-italic">Mendengarkan</em></h2>
    </div>
    <?php
      $tagIcons = ['fa-brain','fa-heart-circle-check','fa-people-arrows','fa-child-reaching','fa-briefcase','fa-face-sad-tear','fa-comments','fa-house-user','fa-graduation-cap','fa-hand-holding-heart'];
      $tagColors = ['var(--teal)', 'var(--pink)', 'var(--orange)'];
    ?>
    <div class="concern-grid">
      <?php foreach ($tags as $i => $tag): ?>
      <a href="<?= base_url('/register') ?>" class="concern-card reveal reveal-d<?= ($i % 4) + 1 ?>">
        <span class="ci" style="background:<?= $tagColors[$i % 3] ?>"><i
            class="fas <?= $tagIcons[$i % count($tagIcons)] ?>"></i></span>
        <div>
          <h6><?= e($tag['name']) ?></h6>
          <p><?= e($tag['description'] ?? 'Ditangani oleh psikolog berpengalaman') ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section> -->


<!-- ======================= PROVIDERS ======================= -->
<?php if (!empty($providers)): ?>
<section class="providers-band" id="tim-klinisi">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-2 gap-3 reveal providers-head">
      <div>
        <div class="section-eyebrow">Tim Psikolog Kami</div>
        <h2 class="section-title mb-0">Psikolog <em class="accent-italic">Berlisensi</em></h2>
        <p class="providers-head-note mb-0">Digiarkan praktik profesional &amp; pengalaman menangani klien.</p>
      </div>
      <div class="providers-head-aside">
        <span class="providers-count"><i class="fas fa-user-doctor"></i> <?= count($providers) ?> psikolog</span>
        <a href="<?= base_url('/klinisi') ?>" class="btn-ghost-arrow">Lihat semua psikolog <i
            class="fas fa-arrow-up-right"></i></a>
      </div>
    </div>
    <div class="providers-carousel reveal">
      <div class="swiper providers-swiper" id="providersSwiper">
        <div class="swiper-wrapper">
          <?php foreach (array_slice($providers, 0, 8) as $p):
            $rs = $reviewSummaries[$p['user_id']] ?? null;
            $hasRating = $rs && (int) $rs['total'] > 0;
            $tags = array_slice($providerTags[(int) $p['user_id']] ?? [], 0, 2);
          ?>
          <div class="swiper-slide">
            <a href="<?= base_url('/providers/' . $p['user_id']) ?>" class="provider-card provider-card--pro">
              <div class="provider-card-photo">
                <?php if (!empty($p['photo'])): ?>
                <img src="<?= base_url($p['photo']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                <?php else: ?>
                <span class="avatar-circle"><?= e(mb_substr($p['name'], 0, 1)) ?></span>
                <?php endif; ?>
                <span class="provider-role-pill"><i class="fas fa-shield-halved"></i> <?= e($p['role_label']) ?></span>
                <?php if ($hasRating): ?>
                <span class="provider-card-score"><i class="fas fa-star"></i> <?= e((string) $rs['average']) ?></span>
                <?php endif; ?>
                <span class="provider-card-overlay">
                  <span class="provider-card-name"><?= e($p['name']) ?></span>
                  <span class="provider-card-exp"><?= (int) ($p['years_experience'] ?? 0) ?>+ tahun pengalaman</span>
                </span>
              </div>
              <div class="provider-card-body">
                <?php if ($tags): ?>
                <div class="provider-card-tags">
                  <?php foreach ($tags as $t): ?>
                  <span><?= e($t) ?></span>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="provider-card-tags provider-card-tags--empty">Lihat profil &amp; jam praktik</div>
                <?php endif; ?>
                <div class="provider-card-foot">
                  <span class="provider-card-link">Lihat Profil <i class="fas fa-arrow-right"></i></span>
                  <?php if ($hasRating): ?>
                  <span class="provider-card-total"><?= (int) $rs['total'] ?> ulasan</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="provider-card-stack">
                <h6 class="provider-card-stack-name"><?= e($p['name']) ?></h6>
                <p class="provider-card-stack-exp"><?= (int) ($p['years_experience'] ?? 0) ?>+ tahun pengalaman</p>
                <?php if ($hasRating): ?>
                <div class="provider-card-stack-rating">
                  <span class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa<?= $i <= round((float) $rs['average']) ? 's' : 'r' ?> fa-star"></i>
                    <?php endfor; ?>
                  </span>
                  <b><?= e((string) $rs['average']) ?></b>
                  <span class="count">&middot; <?= (int) $rs['total'] ?> ulasan</span>
                </div>
                <?php endif; ?>
                <span class="provider-card-stack-link">Lihat Profil <i class="fas fa-arrow-right"></i></span>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="providers-nav">
        <button type="button" class="providers-arrow" id="providersPrev" aria-label="Psikolog sebelumnya"><i
            class="fas fa-arrow-left"></i></button>
        <div class="providers-dots" id="providersDots"></div>
        <button type="button" class="providers-arrow" id="providersNext" aria-label="Psikolog berikutnya"><i
            class="fas fa-arrow-right"></i></button>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>



<!-- ======================= ABOUT NFG ======================= -->
<!-- <section class="about-band" id="tentang">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-5 reveal">
        <div class="section-eyebrow">Tentang Nurture for Growth</div>
        <h2 class="section-title">Layanan Psikologis untuk <em class="accent-italic">Setiap Tahap Kehidupan</em></h2>
        <p class="section-sub">Nurture for Growth (NFG) adalah penyedia layanan psikologi profesional yang mendampingi
          individu dan keluarga dalam setiap tahap kehidupan.</p>
        <p class="about-copy">Kami menyediakan layanan konseling, pemeriksaan psikologis, terapi, serta program edukasi
          bagi anak, remaja, dewasa, pasangan, keluarga, sekolah, dan komunitas.</p>
        <div class="promise-card"><span>Janji Kami</span><strong>Mendampingi Manusia untuk Tumbuh.</strong></div>
      </div>
      <div class="col-lg-7 reveal reveal-d1">
        <div class="philosophy-card">
          <span class="section-eyebrow">Filosofi Kami</span>
          <blockquote>Pertumbuhan tidak dapat dipaksakan; ia hanya dapat dipupuk.</blockquote>
          <p>Kami percaya bahwa pertumbuhan merupakan proses yang terjadi ketika seseorang merasa aman, dipahami,
            didukung, dan memiliki kesempatan untuk berkembang.</p>
          <p>Melalui pendekatan yang profesional, evidence-based, dan berpusat pada kebutuhan setiap individu, NFG hadir
            untuk membantu perjalanan menuju kehidupan yang lebih sehat, tangguh, dan bermakna.</p>
        </div>
        
        <div class="vision-mission" id="visionMission">
          <button type="button" class="vision-mission-toggle" id="visionMissionToggle" aria-expanded="false"
            aria-controls="visionMissionBody">
            <span class="vision-mission-toggle-label">Visi &amp; Misi</span>
            <span class="vision-mission-toggle-hint">Lihat</span>
            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
          </button>
          <div class="vision-mission-body" id="visionMissionBody">
            <div class="vision-mission-grid">
              <div><span class="mini-label">Visi</span>
                <p>Menjadi mitra terpercaya dalam perjalanan pertumbuhan setiap individu dan keluarga melalui layanan
                  psikologi yang profesional, bermakna, dan berkelanjutan.</p>
              </div>
              <div><span class="mini-label">Misi</span>
                <ul>
                  <li>Layanan profesional, evidence-based, dan berpusat pada kebutuhan klien.</li>
                  <li>Mendampingi pertumbuhan melalui konseling, asesmen, terapi, dan edukasi.</li>
                  <li>Bekerja sama dengan keluarga, sekolah, tenaga kesehatan, dan komunitas.</li>
                  <li>Meningkatkan literasi kesehatan mental melalui edukasi dan pelatihan.</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section> -->

<!-- ======================= SERVICE CATALOG ======================= -->
<section class="service-catalog-band" id="layanan-lengkap">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <div class="section-eyebrow justify-content-center">Layanan Kami</div>
      <h2 class="section-title">Pendampingan yang Tumbuh Bersama <em class="accent-italic">Kebutuhan Anda</em></h2>
    </div>
    <?php
      $serviceCatalog = [
        ['number'=>'01','title'=>'Konseling','icon'=>'fa-comments','copy'=>'Pendampingan psikologis untuk individu, pasangan, dan keluarga menghadapi tantangan kehidupan.', 'items'=>['Konseling Anak','Konseling Remaja','Konseling Dewasa','Konseling Pasangan','Konseling Keluarga','Konsultasi Parenting']],
        ['number'=>'02','title'=>'Asesmen Psikologis','icon'=>'fa-clipboard-check','copy'=>'Pemeriksaan untuk memahami kemampuan, potensi, perkembangan, kondisi psikologis, serta dinamika hubungan dan keluarga.', 'items'=>['Asesmen Kesiapan Sekolah','Asesmen Perkembangan','Asesmen Inteligensi (IQ)','Asesmen Kepribadian','Asesmen Klinis','Asesmen Minat & Bakat','Asesmen Pranikah (Prepare-Enrich)','Asesmen Pasangan (Prepare-Enrich)','Asesmen Dinamika Keluarga','Asesmen Hubungan Orang Tua & Anak']],
        ['number'=>'03','title'=>'Terapi Psikologis','icon'=>'fa-seedling','copy'=>'Pendampingan terapeutik yang disesuaikan dengan usia, kebutuhan, dan tujuan setiap klien.', 'items'=>['Terapi Seni','Terapi Musik','Terapi Bermain','Pendampingan Parenting']],
        ['number'=>'04','title'=>'Pendidikan & Pengembangan','icon'=>'fa-chalkboard-user','copy'=>'Program edukasi dan pengembangan untuk keluarga, sekolah, profesional, dan komunitas.', 'items'=>['Seminar & Lokakarya','Webinar','Kelas Parenting','Pelatihan Sekolah','Program Pengembangan Profesional']],
      ];
    ?>
    <div class="service-tabs reveal">
      <div class="service-tablist" role="tablist" aria-label="Katalog layanan">
        <?php foreach ($serviceCatalog as $i => $service): ?>
        <button type="button" class="service-tab<?= $i === 0 ? ' active' : '' ?>" role="tab"
          aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-service-tab>
          <span class="service-tab-icon"><i class="fas <?= $service['icon'] ?>"></i></span>
          <span class="service-tab-title"><?= e($service['title']) ?></span>
          <span class="service-tab-count"><?= count($service['items']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>
      <div class="service-panels">
        <?php foreach ($serviceCatalog as $i => $service): ?>
        <div class="service-panel<?= $i === 0 ? ' active' : '' ?> service-panel-<?= $i + 1 ?>" role="tabpanel">
          <div class="service-panel-lead"><i class="fas <?= $service['icon'] ?>"></i></div>
          <div class="service-panel-body">
            <span class="service-number"><?= $service['number'] ?></span>
            <h3><?= e($service['title']) ?></h3>
            <p class="service-panel-copy"><?= e($service['copy']) ?></p>
            <ul class="service-panel-items">
              <?php foreach ($service['items'] as $item): ?>
              <li><i class="fas fa-circle-check"></i><?= e($item) ?></li>
              <?php endforeach; ?>
            </ul>
            <!-- <a href="#tarif" class="btn btn-primary btn-pill">Lihat Tarif <i class="fas fa-arrow-right"></i></a> -->
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<script>
  (function () {
    var root = document.getElementById('layanan-lengkap');
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[data-service-tab]'));
    var panels = Array.prototype.slice.call(root.querySelectorAll('.service-panel'));
    if (!tabs.length || tabs.length !== panels.length) return;
    tabs.forEach(function (tab, idx) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t, i) {
          t.classList.toggle('active', i === idx);
          t.setAttribute('aria-selected', i === idx ? 'true' : 'false');
          panels[i].classList.toggle('active', i === idx);
        });
      });
    });
  })();
</script>

<!-- ======================= RATE CARD ======================= -->
<section class="rate-band" id="tarif">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 reveal">
      <div>
        <div class="section-eyebrow">Daftar Tarif</div>
        <h2 class="section-title mb-0">Tarif yang <em class="accent-italic">Transparan</em></h2>
        <?php if (!empty($promos)): ?>
        <p class="rate-promo-flag mb-0 mt-2"><i class="fas fa-tags"></i> Promo berjalan tersedia &mdash; diskon otomatis
          dipotong saat memesan sesi yang masuk periode promo.</p>
        <?php endif; ?>
      </div>
      <p class="rate-note mb-0">Biaya akhir dapat disesuaikan dengan kebutuhan sesi dan pemeriksaan.</p>
    </div>
    <?php if (!empty($promos)): ?>
    <div class="rate-promo-strip reveal">
      <?php foreach ($promos as $pr):
        $discountLabel = $pr['discount_type'] === 'percent'
          ? (int) $pr['discount_value'] . '%'
          : 'Rp' . number_format((int) $pr['discount_value'], 0, ',', '.');
      ?>
      <div class="rate-promo-chip">
        <span class="rate-promo-icon"><i class="fas fa-tags"></i></span>
        <div class="rate-promo-body">
          <strong><?= e($pr['name']) ?></strong>
          <small>Diskon <em><?= e($discountLabel) ?></em></small>
        </div>
        <div class="rate-promo-meta">
          <span><i class="fas fa-calendar-check"></i> <?= format_date($pr['start_date'], 'd M Y') ?> &ndash;
            <?= format_date($pr['end_date'], 'd M Y') ?></span>
          <!-- <span><i class="fas fa-user-md"></i> <?= $pr['scope'] === 'all' ? 'Semua tenaga profesional' : e($pr['provider_name']) ?></span> -->
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="row g-4">
      <div class="col-lg-4 reveal">
        <div class="rate-card">
          <div class="rate-card-heading">
            <div class="rate-tier"><span class="rate-tier-icon"><i class="fas fa-user-md"></i></span>
              <div>
                <h3>Associate Psychologist</h3><small>Perawatan profesional</small>
              </div>
            </div><span class="rate-badge">Pilihan populer</span>
          </div>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Layanan</th>
                  <th><i class="fas fa-video"></i> Online</th>
                  <th><i class="fas fa-location-dot"></i> Tatap Muka</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Konseling Individu<br><small>50-60 menit</small></td>
                  <td><strong>Rp375.000</strong></td>
                  <td><strong>Rp475.000</strong></td>
                </tr>
                <tr>
                  <td>Konseling Pasangan<br><small>80-90 menit</small></td>
                  <td><strong>Rp650.000</strong></td>
                  <td><strong>Rp750.000</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-4 reveal reveal-d1">
        <div class="rate-card rate-card-featured">
          <div class="rate-card-heading">
            <div class="rate-tier"><span class="rate-tier-icon"><i class="fas fa-star"></i></span>
              <div>
                <h3>Senior Clinical Psychologist</h3><small>Perawatan lanjutan</small>
              </div>
            </div><span class="rate-badge rate-badge-featured">Perawatan ahli</span>
          </div>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Layanan</th>
                  <th><i class="fas fa-video"></i> Online</th>
                  <th><i class="fas fa-location-dot"></i> Tatap Muka</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Konseling Individu<br><small>50-60 menit</small></td>
                  <td><strong>Rp500.000</strong></td>
                  <td><strong>Rp600.000</strong></td>
                </tr>
                <tr>
                  <td>Konseling Pasangan<br><small>80-90 menit</small></td>
                  <td><strong>Rp850.000</strong></td>
                  <td><strong>Rp950.000</strong></td>
                </tr>
                <tr>
                  <td>Konseling Keluarga<br><small>80-90 menit</small></td>
                  <td><strong>Rp850.000</strong></td>
                  <td><strong>Rp950.000</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-4 rate-assessment-col reveal reveal-d2">
        <div class="rate-card rate-card-assessment">
          <div class="rate-card-heading">
            <div class="rate-tier"><span class="rate-tier-icon"><i class="fas fa-clipboard-check"></i></span>
              <div>
                <h3>Asesmen Psikologis</h3><small>Untuk memahami kebutuhan dan potensi</small>
              </div>
            </div><span class="rate-badge rate-badge-orange">Mulai dari</span>
          </div>
          <div class="assessment-rate"><strong>Rp500.000</strong><span>Pemeriksaan psikologis</span></div>
          <p class="mb-0">Biaya disesuaikan dengan tujuan pemeriksaan, jenis asesmen, dan kebutuhan klien.</p>
        </div>
      </div>
    </div>
    <div class="text-center mt-4"><a href="<?= base_url('/register') ?>" class="btn-hero">Mulai Konsultasi <span
          class="icon-box"><i class="fas fa-arrow-right"></i></span></a></div>
  </div>
</section>

<!-- ======================= HOW IT WORKS ======================= -->
<!-- <section class="steps-band">
  <div class="container">
    <div class="text-center mb-2 reveal">
      <div class="section-eyebrow justify-content-center">Cara Kerja</div>
      <h2 class="section-title">Tiga Langkah Menuju Sesi <em class="accent-italic">Pertama Anda</em></h2>
    </div>
    <div class="steps-track">
      <div class="step-card reveal">
        <div class="step-num">01</div>
        <h5>Daftar &amp; Ceritakan Keluhan</h5>
        <p>Buat akun lalu isi asesmen singkat agar sistem memahami kebutuhan Anda.</p>
      </div>
      <div class="step-card reveal reveal-d1">
        <div class="step-num">02</div>
        <h5>Dapatkan Rekomendasi</h5>
        <p>Sistem pencocokan cerdas kami menyarankan psikolog yang paling sesuai.</p>
      </div>
      <div class="step-card reveal reveal-d2">
        <div class="step-num">03</div>
        <h5>Jadwalkan Sesi</h5>
        <p>Pilih waktu yang tersedia, secara online maupun tatap muka di klinik kami.</p>
      </div>
      <div class="step-card reveal reveal-d3">
        <div class="step-num">04</div>
        <h5>Konsultasi &amp; Pantau Progres</h5>
        <p>Ikuti sesi, simpan catatan pribadi di jurnal, dan pantau perkembangan Anda.</p>
      </div>
    </div>
  </div>
</section> -->





<!-- ======================= SITE REVIEWS (Landing) - Swiper + Review Modal ======================= -->
<section class="site-reviews-band pt-section pb-section">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <div class="section-eyebrow justify-content-center">Ulasan Client</div>
      <h2 class="section-title">Apa Kata Client Tentang Kami</h2>
      <p class="section-copy mx-auto mt-3" style="max-width:680px; color:var(--ink-faint);">Baca pengalaman nyata client
        dan beri tahu kami bagaimana sesi konsultasi membantu Anda berkembang.</p>
    </div>

    <div class="reviews-content">
      <div class="swiper testimonials-swiper">
        <div class="swiper-wrapper">
          <?php if (!empty($siteReviews)): ?>
          <?php foreach ($siteReviews as $sr): ?>
          <div class="swiper-slide">
            <div class="testimonial-card text-center">
              <div class="avatar mb-3 mx-auto">
                <?php if (!empty($sr['avatar'])): ?>
                <img src="<?= e($sr['avatar']) ?>" alt="<?= e($sr['user_name']) ?>" loading="lazy">
                <?php else: ?>
                <?= e(mb_substr($sr['user_name'], 0, 1)) ?>
                <?php endif; ?>
              </div>
              <div class="quote-mark">“</div>
              <p class="quote mb-3"><?= e($sr['comment']) ?></p>
              <div class="rating small mb-2"><?php for ($i=1;$i<=5;$i++): ?><i
                  class="fa<?= $i <= $sr['rating'] ? 's' : 'r' ?> fa-star"></i><?php endfor; ?></div>
              <!-- <strong class="d-block mt-2"><?= e($sr['user_name']) ?></strong> -->
              <?php if (!empty($sr['role'])): ?><div class="testimonial-role text-muted small"><?= e($sr['role']) ?>
              </div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
          <div class="swiper-slide">
            <div class="testimonial-card text-center">Belum ada ulasan.</div>
          </div>
          <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>

      <div class="review-cta text-center mt-4">
        <div class="review-output text-muted small mb-3">Klik tombol di bawah untuk membuka form ulasan. Jika belum
          masuk, Anda akan diarahkan ke halaman login.</div>
        <?php $canReview = \Core\Auth::check() && (str_ends_with(strtolower((string) (\Core\Auth::user()['email'] ?? '')), '@gmail.com') || str_ends_with(strtolower((string) (\Core\Auth::user()['email'] ?? '')), '@googlemail.com')); ?>
        <?php if ($canReview): ?>
        <button class="btn btn-primary btn-lg review-action-btn" data-bs-toggle="modal"
          data-bs-target="#reviewModal">Berikan Ulasan</button>
        <?php else: ?>
        <a href="<?= base_url('/login') ?>" class="btn btn-outline-primary btn-lg review-action-btn">Masuk / Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title">Tinggalkan Ulasan</h5>
          <p class="small text-muted mb-0">Beritahu pengalaman Anda setelah sesi.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body pt-2">
        <form method="POST" action="<?= base_url('/reviews') ?>">
          <?= \Core\Csrf::field() ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Rating</label>
            <select name="rating" class="form-select form-select-lg">
              <option value="5">5 — Sangat Baik</option>
              <option value="4">4 — Baik</option>
              <option value="3">3 — Cukup</option>
              <option value="2">2 — Kurang</option>
              <option value="1">1 — Buruk</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Komentar</label>
            <textarea name="comment" class="form-control form-control-lg" rows="4"
              placeholder="Bagikan pengalaman Anda secara singkat..."></textarea>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Kirim Ulasan</button>
            <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Swiper CDN -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<style>
  .review-highlight {
    display: none;
  }

  .reviews-content {
    max-width: 1160px;
    margin: 0 auto;
  }

  .review-output {
    background: #f8fafc;
    border: 1px solid rgba(15, 118, 110, .16);
    border-radius: 18px;
    padding: 1rem 1.2rem;
    color: #334155;
    margin: 0 auto 1.75rem;
    max-width: 740px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
  }

  .review-cta {
    max-width: 460px;
    margin: 0 auto;
  }

  .review-action-btn {
    min-width: 240px;
  }

  .testimonial-card {
    background: #ffffff;
    border-radius: 28px;
    padding: 2.4rem 2rem;
    box-shadow: 0 20px 46px rgba(15, 23, 42, 0.08);
    width: 416px;
    height: 363px;
    min-height: 363px;
    max-height: 363px;
    transition: transform .25s ease, box-shadow .25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
  }

  .testimonial-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 36px 80px rgba(15, 23, 42, 0.14);
  }

  .quote-mark {
    font-size: 2.4rem;
    color: #10b981;
    opacity: .18;
    margin-bottom: 0.9rem;
  }

  .quote {
    margin: 0 auto;
    color: #0f172a;
    line-height: 1.6;
    font-weight: 500;
    font-size: .95rem;
    max-width: 38ch;
    flex: 1 1 auto;
    overflow: hidden;
  }

  .testimonial-role {
    color: #64748b;
    margin-top: .75rem;
    font-size: .95rem;
  }

  .avatar {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    background: #effaf4;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #0f766e;
    overflow: hidden;
    border: 1px solid rgba(15, 118, 110, 0.14);
    margin: 0 auto 1rem;
  }

  .testimonial-card .rating {
    margin-bottom: 1rem;
  }

  .testimonial-card .rating i {
    color: #f59e0b;
    margin-right: 4px;
  }

  .testimonials-swiper {
    padding-left: 0;
    padding-right: 0;
  }

  .swiper {
    padding-bottom: 1.75rem;
    overflow: hidden;
    position: relative;
  }

  .swiper-wrapper {
    align-items: stretch;
  }

  .testimonials-swiper .swiper-slide {
    box-sizing: border-box;
    display: flex;
    justify-content: center;
    align-items: stretch;
    padding: 0 6px;
    transition: transform .45s cubic-bezier(.2, .8, .2, 1), opacity .45s ease;
    flex: 0 0 416px;
    width: 416px;
    max-width: 416px;
  }

  .testimonials-swiper .swiper-slide {
    opacity: .88;
  }

  .testimonials-swiper .swiper-slide.swiper-slide-active {
    opacity: 1;
    transform: translateY(-4px);
    z-index: 2;
  }

  .swiper-pagination {
    display: flex;
    justify-content: center;
    gap: .5rem;
    margin-top: 1rem;
  }

  .swiper-pagination-bullet {
    width: 11px;
    height: 11px;
    background: rgba(15, 23, 42, .25);
    opacity: 1;
  }

  .swiper-pagination-bullet-active {
    background: var(--teal);
  }

  .review-layout .col-xl-5,
  .review-layout .col-xl-7 {
    display: none;
  }

  .modal-content {
    border-radius: 24px;
  }

  .modal-header {
    padding-bottom: 0;
  }

  .modal-body {
    padding-top: 1rem;
  }

  .form-select-lg,
  .form-control-lg {
    border-radius: 16px;
  }

  @media (max-width: 1199.98px) {
    .testimonials-swiper .swiper-slide {
      flex: 0 0 calc((100% - 20px) / 2);
      width: calc((100% - 20px) / 2);
      max-width: calc((100% - 20px) / 2);
    }
  }

  @media (max-width: 767.98px) {
    .testimonials-swiper .swiper-slide {
      flex: 0 0 100% !important;
      width: 100% !important;
      max-width: 100% !important;
    }

    .testimonial-card {
      width: 100%;
      height: auto;
      min-height: 0;
      max-height: none;
      padding: 1.35rem 1.15rem;
      border-radius: 20px;
    }

    .testimonial-card .quote {
      font-size: .9rem;
      line-height: 1.6;
    }

    .testimonial-card .quote-mark {
      font-size: 1.8rem;
      margin-bottom: .35rem;
    }

    .testimonial-card .avatar {
      width: 52px;
      height: 52px;
      font-size: 1.2rem;
      margin-bottom: .6rem !important;
    }

    .testimonial-card .testimonial-role {
      margin-top: .5rem;
      font-size: .8rem;
    }

    .review-action-btn {
      width: 100%;
    }

    .review-output {
      margin-bottom: 1rem;
    }
  }

  @media (max-width: 991.98px) {
    .review-layout {
      gap: 1.5rem;
    }
  }

  @media (max-width: 767.98px) {
    .review-highlight {
      padding: 1.75rem;
    }

    .review-highlight h3 {
      font-size: 1.6rem;
    }

    .review-score {
      flex-direction: column;
      align-items: flex-start;
      gap: .45rem;
    }

    .review-score strong {
      font-size: 2.4rem;
    }

    .review-count {
      width: 100%;
    }

    .testimonials-swiper .swiper-slide {
      flex: 0 0 100% !important;
      width: 100% !important;
      max-width: 100% !important;
    }

    .testimonial-card {
      min-height: 0;
    }

    .testimonials-swiper {
      padding-left: 0;
      padding-right: 0;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // "Visi & Misi" - tampil/sembunyi (khusus mobile)
    var vm = document.getElementById('visionMission');
    var vmToggle = document.getElementById('visionMissionToggle');
    if (vm && vmToggle) {
      vmToggle.addEventListener('click', function () {
        var open = vm.classList.toggle('is-open');
        vmToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }

    if (typeof Swiper === 'undefined') return;
    new Swiper('.testimonials-swiper', {
      loop: true,
      autoplay: {
        delay: 3200,
        disableOnInteraction: false
      },
      speed: 800,
      spaceBetween: 10,
      grabCursor: true,
      slidesPerView: 3,
      centeredSlides: false,
      pagination: {
        el: '.swiper-pagination',
        clickable: true
      },
      breakpoints: {
        0: {
          slidesPerView: 1,
          spaceBetween: 10
        },
        640: {
          slidesPerView: 1,
          spaceBetween: 10
        },
        768: {
          slidesPerView: 2,
          spaceBetween: 10
        },
        992: {
          slidesPerView: 3,
          spaceBetween: 10
        }
      }
    });

    var providersSwiper = document.querySelector('.providers-swiper');
    if (providersSwiper) {
      new Swiper(providersSwiper, {
        // Mobile: 2 kartu terlihat penuh berdampingan, sisanya bisa digeser.
        slidesPerView: 2,
        spaceBetween: 10,
        grabCursor: true,
        speed: 600,
        watchOverflow: true,
        // Swiper otomatis berhenti sendiri bila jumlah kartu tidak cukup untuk digeser.
        autoplay: {
          delay: 4200,
          disableOnInteraction: false,
          pauseOnMouseEnter: true
        },
        navigation: {
          nextEl: '#providersNext',
          prevEl: '#providersPrev'
        },
        pagination: {
          el: '#providersDots',
          clickable: true
        },
        breakpoints: {
          // 480-767px tetap 2 kartu penuh (tanpa potongan kartu ke-3).
          480: {
            slidesPerView: 2,
            spaceBetween: 12
          },
          640: {
            slidesPerView: 2,
            spaceBetween: 14
          },
          768: {
            slidesPerView: 2.4,
            spaceBetween: 18
          },
          992: {
            slidesPerView: 3.2,
            spaceBetween: 22
          }
        }
      });
    }
    var articlesSwiperEl = document.querySelector('.articles-swiper');
    if (articlesSwiperEl) {
      new Swiper(articlesSwiperEl, {
        // 3 artikel: di HP 2 kartu terlihat penuh lalu di-swipe, di desktop 3 kartu sekaligus.
        slidesPerView: 2,
        spaceBetween: 10,
        slidesPerGroup: 1,
        grabCursor: true,
        speed: 600,
        watchOverflow: true,
        breakpoints: {
          480: {
            slidesPerView: 2,
            spaceBetween: 12
          },
          640: {
            slidesPerView: 2,
            spaceBetween: 14
          },
          768: {
            slidesPerView: 2.4,
            spaceBetween: 18
          },
          1024: {
            slidesPerView: 3,
            spaceBetween: 24
          }
        }
      });
    }
  });
</script>

<?php if (!empty($serviceVideos)): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var track = document.getElementById('svTrack');
    var modalEl = document.getElementById('videoModal');
    var player = document.getElementById('videoModalPlayer');
    var titleEl = document.getElementById('videoModalTitle');

    // Panah geser kiri/kanan
    ['svArrowLeft', 'svArrowRight'].forEach(function (id) {
      var btn = document.getElementById(id);
      if (!btn || !track) return;
      btn.addEventListener('click', function () {
        var card = track.querySelector('.video-card');
        var step = card ? (card.getBoundingClientRect().width + 24) : 320;
        track.scrollBy({
          left: id === 'svArrowLeft' ? -step : step,
          behavior: 'smooth'
        });
      });
    });

    // Buka modal & injeksi pemutar hanya saat diklik (lazy)
    document.querySelectorAll('.video-play-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        player.innerHTML = '';
        titleEl.textContent = btn.getAttribute('aria-label') ? btn.getAttribute('aria-label').replace(
          /^Putar video: /, '') : '';
        var embed = btn.dataset.embed;
        var file = btn.dataset.file;
        if (embed) {
          var iframe = document.createElement('iframe');
          iframe.src = embed;
          iframe.allow =
            'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
          iframe.allowFullscreen = true;
          iframe.title = titleEl.textContent;
          player.appendChild(iframe);
        } else if (file) {
          var video = document.createElement('video');
          video.src = file;
          video.controls = true;
          video.autoplay = true;
          video.playsInline = true;
          video.style.width = '100%';
          video.style.height = '100%';
          player.appendChild(video);
        }
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      });
    });

    // Hentikan pemutaran saat modal ditutup
    modalEl.addEventListener('hidden.bs.modal', function () {
      player.innerHTML = '';
    });
  });
</script>
<?php endif; ?>

<!-- ======================= WHY US / TRUST ======================= -->
<section class="trust-band">
  <div class="container">
    <div class="text-center mb-2 reveal">
      <div class="section-eyebrow justify-content-center">Kenapa NFG</div>
      <h2 class="section-title">Didampingi dengan Empati, <em class="accent-italic">Ditangani Secara Profesional</em>
      </h2>
    </div>
    <div class="trust-grid">
      <div class="trust-card reveal">
        <span class="ti" style="background:var(--teal)"><i class="fas fa-user-shield"></i></span>
        <h6>Psikolog Berlisensi</h6>
        <p>Seluruh psikolog kami memiliki STR/SIPP resmi dan berpengalaman menangani berbagai isu.</p>
      </div>
      <div class="trust-card reveal reveal-d1">
        <span class="ti" style="background:var(--pink)"><i class="fas fa-lock"></i></span>
        <h6>Kerahasiaan Terjaga</h6>
        <p>Data dan cerita Anda disimpan secara aman dan hanya dapat diakses oleh Anda &amp; psikolog yang menangani.
        </p>
      </div>
      <div class="trust-card reveal reveal-d2">
        <span class="ti" style="background:var(--orange)"><i class="fas fa-calendar-check"></i></span>
        <h6>Jadwal Fleksibel</h6>
        <p>Pilih sesi online atau tatap muka, kapan pun jadwal Anda memungkinkan &mdash; termasuk akhir pekan.</p>
      </div>
      <div class="trust-card reveal reveal-d3">
        <span class="ti" style="background:var(--ink)"><i class="fas fa-chart-line"></i></span>
        <h6>Pantauan Berkelanjutan</h6>
        <p>Jurnal harian, pelacakan mood, dan laporan perkembangan membantu Anda melihat progres nyata.</p>
      </div>
    </div>
    <?php if ($overallAvg): ?>
    <div class="trust-score reveal">
      <div class="num"><?= e((string) $overallAvg) ?></div>
      <div>
        <div class="stars"><?php for ($i = 1; $i <= 5; $i++): ?><i
            class="fa<?= $i <= round($overallAvg) ? 's' : 'r' ?> fa-star"></i><?php endfor; ?></div>
        <small><?= (int) $sumCount ?> ulasan dari client yang telah menyelesaikan sesi</small>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ======================= VIDEO LAYANAN ======================= -->
<?php if (!empty($serviceVideos)): ?>
<section class="video-band" id="video-layanan">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-2 gap-3 reveal">
      <div>
        <div class="section-eyebrow">Video Layanan</div>
        <h2 class="section-title mb-0">Kenali Layanan Kami <em class="accent-italic">Lebih Dekat</em></h2>
      </div>
    </div>

    <div class="position-relative">
      <div class="video-track reveal" id="svTrack">
        <?php foreach ($serviceVideos as $i => $v):
          $thumb = \App\Models\ServiceVideo::thumbUrl($v);
          $embed = \App\Models\ServiceVideo::embedUrl($v);
          $file  = \App\Models\ServiceVideo::fileSourceUrl($v);
        ?>
        <article class="video-card reveal <?= ['','reveal-d1','reveal-d2'][$i % 3] ?>">
          <button type="button" class="video-thumb video-play-btn" data-embed="<?= $embed ? e($embed) : '' ?>"
            data-file="<?= ($file && !$embed) ? e($file) : '' ?>" aria-label="Putar video: <?= e($v['title']) ?>">
            <?php if ($thumb): ?>
            <img src="<?= e($thumb) ?>" alt="<?= e($v['title']) ?>" loading="lazy">
            <?php else: ?>
            <span class="video-thumb-fallback"><i class="fas fa-clinic-medical"></i></span>
            <?php endif; ?>
            <span class="video-play">
              <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" style="margin-left:4px">
                <path d="M8 5.5v13l11-6.5z" /></svg>
            </span>
            <?php if (!empty($v['badge_text'])): ?>
            <span class="video-chip"><?= e($v['badge_text']) ?></span>
            <?php endif; ?>
            <span class="video-shade"></span>
          </button>
          <div class="video-body">
            <h5 class="video-title"><?= e($v['title']) ?></h5>
            <?php if (!empty($v['description'])): ?>
            <p class="video-desc"><?= e($v['description']) ?></p>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php if (count($serviceVideos) > 3): ?>
      <button type="button" class="sv-arrow sv-arrow-left" id="svArrowLeft" aria-label="Geser kiri"><i
          class="fas fa-chevron-left"></i></button>
      <button type="button" class="sv-arrow sv-arrow-right" id="svArrowRight" aria-label="Geser kanan"><i
          class="fas fa-chevron-right"></i></button>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-video">
    <div class="modal-content video-modal-content">
      <button type="button" class="btn-close btn-close-white video-modal-close" data-bs-dismiss="modal"
        aria-label="Tutup"></button>
      <div class="ratio ratio-16x9 video-ratio">
        <div id="videoModalPlayer"></div>
      </div>
      <div class="px-4 pt-3 pb-2">
        <h6 class="fw-bold text-white mb-0" id="videoModalTitle"></h6>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
  .video-band {
    padding: var(--section-gap, 72px) 0;
    background:
      radial-gradient(900px 400px at 85% 0%, rgba(0, 199, 156, .08), transparent 60%),
      radial-gradient(700px 380px at 0% 100%, rgba(236, 22, 108, .06), transparent 55%);
  }

  /* Lebar kartu video disamakan dengan kartu Tim Psikolog agar seragam:
     mobile 2 kartu penuh, tablet 2,4 kartu, desktop 3,2 kartu. */
  .video-track {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    padding: 6px 0 18px;
    scrollbar-width: none;
  }

  .video-track::-webkit-scrollbar {
    display: none;
  }

  .video-card {
    flex: 0 0 calc((100% - 10px) / 2);
    width: calc((100% - 10px) / 2);
    scroll-snap-align: start;
    background: #fff;
    border: 1px solid rgba(18, 36, 29, .08);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 26px rgba(18, 36, 29, .10);
    transition: transform .35s ease, box-shadow .35s ease;
  }

  .video-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 44px rgba(18, 36, 29, .14);
  }

  .video-thumb {
    position: relative;
    display: block;
    width: 100%;
    aspect-ratio: 1/1.04;
    padding: 0;
    border: 0;
    background: #12241d;
    cursor: pointer;
    overflow: hidden;
  }

  .video-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .5s ease;
  }

  .video-card:hover .video-thumb img {
    transform: scale(1.06);
  }

  .video-thumb-fallback {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.4rem;
    color: #00c79c;
    background: linear-gradient(135deg, #12241d, #1c3a2f);
  }

  .video-play {
    position: absolute;
    inset: 0;
    margin: auto;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(255, 255, 255, .94);
    color: #00a383;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .28);
    transition: transform .25s ease, background .25s ease;
    z-index: 2;
  }

  .video-card:hover .video-play {
    transform: scale(1.12);
    background: #fff;
  }

  .video-play::after {
    content: '';
    position: absolute;
    inset: -10px;
    border-radius: 50%;
    border: 2px solid rgba(0, 199, 156, .55);
    animation: svPulse 2.2s ease-out infinite;
  }

  @keyframes svPulse {
    0% {
      transform: scale(.75);
      opacity: .9
    }

    70% {
      transform: scale(1.15);
      opacity: 0
    }

    100% {
      opacity: 0
    }
  }

  .video-chip {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    padding: .32rem .7rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    background: rgba(255, 255, 255, .92);
    color: #0e6e59;
    backdrop-filter: blur(4px);
  }

  .video-shade {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 45%, rgba(10, 20, 16, .55));
    opacity: .7;
    transition: opacity .3s;
  }

  .video-card:hover .video-shade {
    opacity: .35;
  }

  .video-body {
    padding: 11px 12px 13px;
  }

  .video-title {
    font-weight: 800;
    font-size: .88rem;
    color: var(--ink);
    margin-bottom: 4px;
    line-height: 1.3;
  }

  .video-desc {
    font-size: .78rem;
    color: #5b6b64;
    margin: 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .sv-arrow {
    position: absolute;
    top: 38%;
    transform: translateY(-50%);
    z-index: 5;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 0;
    background: #fff;
    color: var(--ink);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(18, 36, 29, .18);
    cursor: pointer;
    transition: background .2s, transform .2s;
  }

  .sv-arrow:hover {
    background: var(--mint-tint);
    transform: translateY(-50%) scale(1.06);
  }

  .sv-arrow-left {
    left: -14px;
  }

  .sv-arrow-right {
    right: -14px;
  }

  .modal-video .modal-dialog {
    max-width: min(880px, 92vw);
  }

  .video-modal-content {
    background: #101b17;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
  }

  .video-modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 3;
  }

  .video-ratio iframe,
  .video-ratio video {
    width: 100%;
    height: 100%;
    border: 0;
  }

  @media (max-width:576px) {
    .video-band {
      padding: 40px 0;
    }
  }

  /* --- Video: samakan ukuran kartu dengan Tim Psikolog di tiap ukuran layar --- */
  @media (max-width: 767px) {

    /* Tanpa bleed: strip mulai & berakhir di gutter yang sama dengan kartu psikolog. */
    .video-track {
      gap: 10px;
    }

    .sv-arrow {
      display: none;
    }
  }

  @media (min-width: 480px) {
    .video-track {
      gap: 12px;
    }

    .video-card {
      flex: 0 0 calc((100% - 12px) / 2);
      width: calc((100% - 12px) / 2);
      border-radius: 20px;
      box-shadow: 0 16px 38px rgba(18, 36, 29, .11);
    }

    .video-thumb {
      aspect-ratio: 4/3.1;
    }

    .video-body {
      padding: 15px 17px 17px;
    }

    .video-title {
      font-size: 1.02rem;
      margin-bottom: 5px;
    }

    .video-desc {
      font-size: .84rem;
      -webkit-line-clamp: 3;
    }
  }

  @media (min-width: 640px) {
    .video-track {
      gap: 14px;
    }

    .video-card {
      flex: 0 0 calc((100% - 14px) / 2);
      width: calc((100% - 14px) / 2);
    }
  }

  @media (min-width: 768px) {
    .video-track {
      gap: 18px;
    }

    .video-card {
      flex: 0 0 calc((100% - 25.2px) / 2.4);
      width: calc((100% - 25.2px) / 2.4);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
    }

    .video-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
    }

    .video-thumb {
      aspect-ratio: 4/5;
    }

    .video-play {
      width: 56px;
      height: 56px;
    }

    .video-body {
      padding: 13px 15px 15px;
    }

    .video-title {
      font-size: 1rem;
      margin-bottom: 6px;
    }

    .video-desc {
      font-size: .8rem;
      -webkit-line-clamp: 3;
    }
  }

  @media (min-width: 992px) {
    .video-track {
      gap: 22px;
    }

    .video-card {
      flex: 0 0 calc((100% - 48.4px) / 3.2);
      width: calc((100% - 48.4px) / 3.2);
    }
  }

  @media (max-width: 479px) {
    .video-chip {
      font-size: .7rem;
      padding: 4px 9px;
      top: 9px;
      left: 9px;
    }

    .video-play {
      width: 48px;
      height: 48px;
    }
  }
</style>


<!-- ======================= ARTICLES ======================= -->
<?php if (!empty($articles)): ?>
<section class="articles-band">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-2 gap-3 reveal">
      <div>
        <div class="section-eyebrow">Artikel</div>
        <h2 class="section-title mb-0">Wawasan Kesehatan <em class="accent-italic">Mental</em></h2>
      </div>
      <a href="<?= base_url('/education') ?>" class="btn-ghost-arrow">Lihat semua artikel <i
          class="fas fa-arrow-up-right"></i></a>
    </div>
    <!-- Carousel artikel: 3 artikel. Di HP 2 kartu terlihat + swipe, di desktop 3 kartu sekaligus. -->
    <div class="articles-carousel reveal">
      <div class="swiper articles-swiper" id="articlesSwiper">
        <div class="swiper-wrapper">
          <?php foreach (array_slice($articles, 0, 3) as $i => $a): ?>
          <div class="swiper-slide">
            <a href="<?= base_url('/education/' . $a['slug']) ?>"
              class="article-card <?= ['', 'reveal-d1', 'reveal-d2'][$i % 3] ?>">
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
                  <span class="article-cat-pill"><?= e($a['category_name'] ?? ucfirst($a['type'])) ?></span>
                  <span class="article-arrow-btn"><i class="fas fa-arrow-up-right"></i></span>
                </div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ======================= TEAM ======================= -->
<!-- <?php if (!empty($supportTeam)): ?>
<section class="team-band">
  <div class="container">
    <div class="text-center mb-2 reveal">
      <div class="section-eyebrow justify-content-center">Di Balik Layar</div>
      <h2 class="section-title">Tim yang <em class="accent-italic">Mendukung</em> Perjalanan Anda</h2>
    </div>
    <div class="team-scroll mt-4">
      <?php foreach ($supportTeam as $tm): ?>
        <div class="team-chip reveal">
          <div class="photo">
            <?php if (!empty($tm['photo'])): ?>
              <img src="<?= base_url($tm['photo']) ?>" alt="<?= e($tm['name']) ?>">
            <?php else: ?>
              <span class="initial"><?= e(mb_substr($tm['name'], 0, 1)) ?></span>
            <?php endif; ?>
          </div>
          <h6><?= e($tm['name']) ?></h6>
          <span><?= e($tm['role_title']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?> -->

<!-- ======================= FAQ PREVIEW ======================= -->
<?php if (!empty($faqs)): ?>
<section class="pt-section pb-section">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <div class="section-eyebrow justify-content-center">FAQ</div>
      <h2 class="section-title">Pertanyaan yang Sering <em class="accent-italic">Diajukan</em></h2>
    </div>
    <div class="accordion mx-auto reveal" id="faqPreview" style="max-width:780px">
      <?php foreach (array_slice($faqs, 0, 5) as $i => $f): ?>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse"
            data-bs-target="#faqp<?= $f['id'] ?>"><?= e($f['question']) ?></button>
        </h2>
        <div id="faqp<?= $f['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
          data-bs-parent="#faqPreview">
          <div class="accordion-body small text-muted"><?= e($f['answer']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- <div class="text-center mt-4 reveal">
      <a href="<?= base_url('/faq') ?>" class="btn-ghost-arrow justify-content-center">Lihat semua FAQ <i class="fas fa-arrow-up-right"></i></a>
    </div> -->
  </div>
</section>
<?php endif; ?>

<!-- ======================= FINAL CTA ======================= -->
<section class="pb-section">
  <div class="container">
    <div class="final-cta reveal">
      <div class="blot blot-teal blot-shape" style="width:260px;height:260px;top:-90px;left:-60px"></div>
      <div class="blot blot-pink blot-shape-alt" style="width:200px;height:200px;bottom:-70px;right:-40px"></div>
      <div style="position:relative;z-index:1">
        <div class="section-eyebrow justify-content-center" style="color:var(--teal)">Mulai Hari Ini</div>
        <h2>Siap Memulai Langkah Pertama untuk <em class="accent-italic">Diri Anda?</em></h2>
        <p>Daftar akun gratis, ceritakan yang Anda rasakan, dan biarkan kami mencarikan psikolog yang paling sesuai.</p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
          <a href="<?= base_url('/register') ?>" class="btn-hero justify-content-center">Daftar &amp; Booking <span
              class="icon-box"><i class="fas fa-arrow-right"></i></span></a>
          <a href="<?= base_url('/contact') ?>" class="btn-outline-price justify-content-center"
            style="border-color:rgba(251,248,242,.4);color:var(--paper)">Hubungi Kami</a>
        </div>
      </div>
    </div>
  </div>
</section>