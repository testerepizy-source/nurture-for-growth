<?php
$__uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__base = base_path();
$__path = $__base && str_starts_with($__uri, $__base) ? substr($__uri, strlen($__base)) : $__uri;
$__path = '/' . ltrim($__path, '/');
function __nav_active($path, $target) {
    if ($target === '/') return $path === '/' ? 'active' : '';
    return str_starts_with($path, $target) ? 'active' : '';
}
$__authed = \Core\Auth::check();
?>
<!DOCTYPE html>
<html lang="<?= locale() ?>">

<head>

  <!-- Google Tag Manager -->
  <script>
    (function (w, d, s, l, i) {
      w[l] = w[l] || [];
      w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
      });
      var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
      j.async = true;
      j.src =
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
      f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-N8FNTTF6');
  </script>
  <!-- End Google Tag Manager -->

  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16474362107"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'AW-16474362107');
    gtag('config', 'G-CC4WQ8TM9X');
    gtag('config', 'G-VGT4BTLLFW');
  </script>

  <!-- Event snippet for Kontak conversion page -->
  <script>
    gtag('event', 'conversion', {
      'send_to': 'AW-16474362107/wF81CNHxhLgZEPuhy689'
    });
  </script>




  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' · ' : '' ?><?= e(config_get('app_name', 'NurtureForGrowth')) ?>
  </title>
  <meta name="description"
    content="<?= isset($pageDescription) ? e($pageDescription) : 'Layanan konseling, asesmen, dan terapi psikologis bersama psikolog berlisensi. Booking sesi online maupun tatap muka dengan mudah.' ?>">
  <meta name="robots" content="<?= isset($pageRobots) ? e($pageRobots) : 'index, follow' ?>">
  <link rel="canonical" href="<?= e(canonical_url()) ?>">
  <meta property="og:title"
    content="<?= isset($pageTitle) ? e($pageTitle . ' · ' . config_get('app_name', 'NurtureForGrowth')) : e(config_get('app_name', 'NurtureForGrowth')) ?>">
  <meta property="og:description"
    content="<?= isset($pageDescription) ? e($pageDescription) : 'Layanan konseling, asesmen, dan terapi psikologis bersama psikolog berlisensi. Booking sesi online maupun tatap muka dengan mudah.' ?>">
  <meta property="og:type" content="<?= isset($pageType) ? e($pageType) : 'website' ?>">
  <meta property="og:url" content="<?= e(canonical_url()) ?>">
  <meta property="og:image" content="<?= e($pageImage ?? absolute_asset('img/brand/nfg-logo.jpg')) ?>">
  <meta property="og:site_name" content="<?= e(config_get('app_name', 'NurtureForGrowth')) ?>">
  <meta property="og:locale" content="<?= e(locale() === 'id' ? 'id_ID' : 'en_US') ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title"
    content="<?= isset($pageTitle) ? e($pageTitle . ' · ' . config_get('app_name', 'NurtureForGrowth')) : e(config_get('app_name', 'NurtureForGrowth')) ?>">
  <meta name="twitter:description"
    content="<?= isset($pageDescription) ? e($pageDescription) : 'Layanan konseling, asesmen, dan terapi psikologis bersama psikolog berlisensi. Booking sesi online maupun tatap muka dengan mudah.' ?>">
  <meta name="twitter:image" content="<?= e($pageImage ?? absolute_asset('img/brand/nfg-logo.jpg')) ?>">
  <?php
$defaultDescription = 'Layanan konseling, asesmen, dan terapi psikologis bersama psikolog berlisensi. Booking sesi online maupun tatap muka dengan mudah.';
$defaultImage = absolute_asset('img/brand/nfg-logo.jpg');
$schemaType = isset($pageType) ? ($pageType === 'article' ? 'BlogPosting' : ($pageType === 'profile' ? 'Person' : 'WebPage')) : 'WebPage';
$schemaObject = [
    '@type' => $schemaType,
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => canonical_url()],
    'headline' => $pageTitle ?? config_get('app_name', 'NurtureForGrowth'),
    'description' => $pageDescription ?? $defaultDescription,
    'image' => $pageImage ?? $defaultImage,
    'url' => canonical_url(),
];
if ($schemaType === 'BlogPosting' && isset($article)) {
    $schemaObject['datePublished'] = date('c', strtotime($article['created_at'] ?? date('Y-m-d')));
    $schemaObject['dateModified'] = date('c', strtotime($article['updated_at'] ?? $article['created_at'] ?? date('Y-m-d')));
    $schemaObject['author'] = ['@type' => 'Organization', 'name' => config_get('app_name', 'NurtureForGrowth')];
    $schemaObject['publisher'] = [
        '@type' => 'Organization',
        'name' => config_get('app_name', 'NurtureForGrowth'),
        'logo' => ['@type' => 'ImageObject', 'url' => $defaultImage],
    ];
}
if ($schemaType === 'Person' && isset($provider)) {
    $schemaObject['name'] = $provider['name'];
    $schemaObject['description'] = $pageDescription ?? $defaultDescription;
    $schemaObject['image'] = $pageImage ?? $defaultImage;
}
$schemaData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            'url' => config_get('app_url', 'http://localhost'),
            'name' => config_get('app_name', 'NurtureForGrowth'),
            'description' => $pageDescription ?? $defaultDescription,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => absolute_url('education?q={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ],
        $schemaObject,
    ],
];
?>
  <script type="application/ld+json">
    < ? = json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ? >
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500;1,600&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preload" href="<?= asset('css/style.css') ?>" as="style">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <link rel="icon" href="<?= asset('img/brand/nfg-logo.jpg') ?>">
</head>

<body class="public-body">

  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N8FNTTF6" height="0" width="0"
      style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <header class="site-header">
    <div class="container nav-inner">
      <a href="<?= base_url('/') ?>" class="brand-mark">
        <img class="brand-logo-image" src="<?= asset('img/brand/nfg-logo.jpg') ?>" alt="Nurture for Growth">
      </a>

      <nav class="main-nav">
        <a href="<?= base_url('/') ?>" class="<?= __nav_active($__path, '/') ?>">Beranda</a>
        <a href="<?= base_url('/#tentang') ?>">Tentang NFG</a>
        <a href="<?= base_url('/#tarif') ?>">Tarif</a>
        <a href="<?= base_url('/education') ?>" class="<?= __nav_active($__path, '/education') ?>">Artikel</a>
        <!-- <a href="<?= base_url('/karier') ?>" class="<?= __nav_active($__path, '/karier') ?>">Karier</a> -->
        <a href="<?= base_url('/contact') ?>" class="<?= __nav_active($__path, '/contact') ?>">Kontak</a>
      </nav>

      <div class="nav-cta">
        <!-- <?php include __DIR__ . '/../partials/header_language_switcher.php'; ?> -->
        <?php if ($__authed): ?>
        <a href="<?= base_url('/dashboard') ?>" class="btn-login" aria-label="Dashboard"><i class="fas fa-gauge"></i>
          <span>Dashboard</span></a>
        <?php else: ?>
        <a href="<?= base_url('/login') ?>" class="btn-login" aria-label="Masuk ke akun"><i
            class="fas fa-arrow-right-to-bracket"></i> <span>Masuk</span></a>
        <?php endif; ?>
        <a href="<?= base_url($__authed ? '/dashboard' : '/register') ?>" class="btn-hero"
          aria-label="<?= $__authed ? 'Buka dashboard dan booking sesi' : 'Daftar dan booking sesi sekarang' ?>">
          <span><?= $__authed ? 'Booking' : 'Booking Sekarang' ?></span> <span class="icon-box"><i
              class="fas fa-arrow-right"></i></span>
        </a>
        <button type="button" class="navbar-toggler-custom" aria-label="Buka menu" aria-controls="mobileNavPanel"
          aria-expanded="false"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>

  <div class="mobile-nav-panel" id="mobileNavPanel" aria-label="Menu navigasi mobile">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span class="brand-mark"><img class="brand-logo-image" src="<?= asset('img/brand/nfg-logo.jpg') ?>"
          alt="Nurture for Growth"></span>
      <button type="button" class="navbar-toggler-custom"
        onclick="document.querySelector('.mobile-nav-panel').classList.remove('is-open');document.body.style.overflow=''"
        aria-label="Tutup menu"><span style="transform:rotate(45deg) translate(2px,2px)"></span><span
          style="opacity:0"></span><span style="transform:rotate(-45deg) translate(2px,-2px)"></span></button>
    </div>
    <a href="<?= base_url('/') ?>">Beranda</a>
    <a href="<?= base_url('/#tentang') ?>">Tentang NFG</a>
    <a href="<?= base_url('/#tarif') ?>">Tarif</a>
    <a href="<?= base_url('/education') ?>">Artikel</a>
    <!-- <a href="<?= base_url('/karier') ?>">Karier</a> -->
    <!-- <a href="<?= base_url('/faq') ?>">FAQ</a> -->
    <a href="<?= base_url('/contact') ?>">Kontak</a>
    <?php if ($__authed): ?>
    <a href="<?= base_url('/dashboard') ?>">Dashboard</a>
    <?php else: ?>
    <a href="<?= base_url('/login') ?>">Masuk</a>
    <a href="<?= base_url('/register') ?>">Daftar</a>
    <?php endif; ?>
  </div>

  <?php if ($flashSuccess = \Core\Session::flash('success')): ?>
  <div class="container mt-3">
    <div class="alert alert-success d-flex align-items-center gap-2 rounded-4 border-0 shadow-sm" role="alert">
      <i class="fas fa-circle-check"></i> <?= e($flashSuccess) ?>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($flashError = \Core\Session::flash('error')): ?>
  <div class="container mt-3">
    <div class="alert alert-danger d-flex align-items-center gap-2 rounded-4 border-0 shadow-sm" role="alert">
      <i class="fas fa-triangle-exclamation"></i> <?= e($flashError) ?>
    </div>
  </div>
  <?php endif; ?>

  <main>
    <?= $content ?>
  </main>

  <nav class="mobile-bottom-nav" aria-label="Navigasi cepat">
    <a href="<?= base_url('/') ?>" class="<?= __nav_active($__path, '/') ?>" aria-label="Beranda">
      <i class="fas fa-house"></i><span>Beranda</span>
    </a>
    <a href="<?= base_url('/#tarif') ?>" aria-label="Price">
      <i class="fas fa-tags"></i><span>Harga</span>
    </a>
    <a href="<?= base_url($__authed ? '/dashboard' : '/register') ?>" class="mobile-bottom-nav-primary"
      aria-label="Booking sekarang">
      <i class="fas fa-calendar-plus"></i><span>Booking</span>
    </a>
    <a href="<?= base_url('/#tim-klinisi') ?>" aria-label="Tim Psikolog">
      <i class="fas fa-user-doctor"></i><span>Tim Psikolog</span>
    </a>
    <a href="<?= base_url($__authed ? '/profile' : '/login') ?>"
      class="<?= $__authed ? __nav_active($__path, '/profile') : __nav_active($__path, '/login') ?>"
      aria-label="Profil">
      <i class="fas fa-user"></i><span>Profil</span>
    </a>
  </nav>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-crisis">
        <i class="fas fa-heart-pulse"></i>
        <div><strong>Dalam keadaan darurat?</strong> Hubungi <strong>119</strong> (layanan gawat darurat nasional) atau
          kunjungi UGD terdekat sekarang juga.</div>
      </div>

      <div class="row g-4 pb-5">
        <div class="col-lg-4">
          <span class="brand-mark"><img class="brand-logo-image" src="<?= asset('img/brand/nfg-logo.jpg') ?>"
              alt="Nurture for Growth"></span>
          <p class="footer-lede">Nurture For Growth mendampingi anak, remaja, dewasa, pasangan, keluarga, sekolah, dan
            komunitas lewat konseling, asesmen, dan terapi psikologis yang berempati &amp; berbasis bukti.</p>
          <div class="footer-social">
            <a href="#" class="social-circle" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-circle" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            <a href="#" class="social-circle" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="social-circle" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-circle" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-6">
          <h6>Jelajahi</h6>
          <a href="<?= base_url('/') ?>">Beranda</a>
          <a href="<?= base_url('/education') ?>">Artikel</a>
          <!-- <a href="<?= base_url('/karier') ?>">Karier</a> -->
          <a href="<?= base_url('/faq') ?>">FAQ</a>
          <a href="<?= base_url('/contact') ?>">Kontak</a>
        </div>
        <div class="col-lg-2 col-6">
          <h6>Akun</h6>
          <a href="<?= base_url('/login') ?>">Masuk</a>
          <a href="<?= base_url('/register') ?>">Daftar Akun</a>
          <!-- <a href="<?= base_url('/provider-application') ?>">Gabung Jadi Mitra</a> -->
        </div>
        <div class="col-lg-4">
          <h6>Hubungi Kami</h6>
          <a href="#" style="cursor:default">Jl. Tomang Utara No. 17, Jakarta Barat 11440</a>
          <a href="mailto:admin@nurtureforgrowth.com">admin@nurtureforgrowth.com</a>
          <a href="tel:+6285716030407">0857 1603 0407</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> NurtureForGrowth (NFG). Seluruh hak cipta dilindungi.
    </div>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/js/bootstrap.bundle.min.js" defer>
  </script>
  <script src="<?= asset('js/main.js') ?>" defer></script>

  <a href="https://wa.me/6285716030407?text=Halo%20NurtureForGrowth" class="whatsapp-fab" target="_blank" rel="noopener"
    aria-label="Chat via WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span>Chat via WhatsApp</span>
  </a>