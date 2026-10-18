<?php
$__user = \Core\Auth::user();
$__role = \Core\Auth::roleName();
$__roleLabels = [
    'pasien' => 'Client', 'psikolog' => 'Psikolog',
    'admin' => 'Admin', 'super_admin' => 'Super Admin',
];
$__roleLabel = $__roleLabels[$__role] ?? ucfirst((string) $__role);
$__appUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__appBase = base_path();
$__appPath = $__appBase && str_starts_with($__appUri, $__appBase) ? substr($__appUri, strlen($__appBase)) : $__appUri;
$__appPath = '/' . ltrim($__appPath, '/');
$__familyProfiles = [];
$__activePatientId = null;
if ($__role === 'pasien') {
  \App\Models\FamilyProfileAccess::ensurePrimaryProfile($__user['id']);
  $__familyProfiles = \App\Models\FamilyProfileAccess::forUser($__user['id']);
  $__activePatientId = current_patient_id();
}
?>
<!DOCTYPE html>
<html lang="<?= locale() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' · ' : '' ?><?= e(config_get('app_name', 'NurtureForGrowth')) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500;1,600&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('css/app-shell.css') ?>">
<link rel="icon" href="<?= asset('img/brand/nfg-logo.jpg') ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="app-body">

<div class="app-shell">
  <aside class="app-sidebar" id="appSidebar">
    <div class="app-sidebar-brand">
      <a href="<?= base_url('/dashboard') ?>" class="brand-mark">
        <img class="brand-logo-image" src="<?= asset('img/brand/nfg-logo.jpg') ?>" alt="Nurture for Growth">
      </a>
      <button type="button" class="sidebar-close-btn d-lg-none" id="sidebarClose" aria-label="Tutup menu"><i class="fas fa-xmark"></i></button>
    </div>
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="app-sidebar-foot">
      <a href="<?= base_url('/') ?>"><i class="fas fa-arrow-left"></i> Kembali ke Situs Utama</a>
    </div>
  </aside>

  <div class="app-sidebar-overlay" id="sidebarOverlay"></div>

  <div class="app-main">
    <header class="app-topbar">
      <button type="button" class="sidebar-open-btn d-lg-none" id="sidebarOpen" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
      <div class="app-topbar-title">
        <?= isset($pageTitle) ? e($pageTitle) : 'Dasbor' ?>
      </div>
      <div class="app-topbar-actions">
        <!-- <?php include __DIR__ . '/../partials/header_language_switcher.php'; ?> -->
        <div class="dropdown app-user-menu">
          <button class="app-user-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (!empty($__user['photo'])): ?>
              <img src="<?= base_url($__user['photo']) ?>" alt="<?= e($__user['name'] ?? '') ?>" class="app-user-avatar" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <span class="app-user-avatar app-user-avatar-fallback" style="display:none"><?= e(mb_substr($__user['name'] ?? '?', 0, 1)) ?></span>
            <?php else: ?>
              <span class="app-user-avatar app-user-avatar-fallback"><?= e(mb_substr($__user['name'] ?? '?', 0, 1)) ?></span>
            <?php endif; ?>
            <span class="app-user-meta d-none d-sm-block">
              <strong><?= e($__user['name'] ?? 'Pengguna') ?></strong>
              <small><?= e($__roleLabel) ?></small>
            </span>
            <i class="fas fa-chevron-down app-user-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end app-user-dropdown">
            <?php if ($__role === 'pasien' && count($__familyProfiles) > 1): ?>
              <li><h6 class="dropdown-header">Profil keluarga aktif</h6></li>
              <?php foreach ($__familyProfiles as $__familyProfile): ?>
                <li>
                  <form method="POST" action="<?= base_url('/profile/family/switch') ?>" class="family-switch-form">
                    <?= \Core\Csrf::field() ?>
                    <input type="hidden" name="patient_id" value="<?= (int) $__familyProfile['patient_id'] ?>">
                    <button class="dropdown-item family-switch-item <?= (int) $__activePatientId === (int) $__familyProfile['patient_id'] ? 'active' : '' ?>" type="submit">
                      <i class="fas fa-<?= (int) $__activePatientId === (int) $__familyProfile['patient_id'] ? 'check' : 'user' ?> me-2"></i><?= e($__familyProfile['patient_name']) ?>
                      <small class="d-block ms-4 text-muted"><?= e(ucfirst($__familyProfile['relationship'])) ?></small>
                    </button>
                  </form>
                </li>
              <?php endforeach; ?>
              <li><hr class="dropdown-divider"></li>
            <?php endif; ?>
            <li><a class="dropdown-item" href="<?= base_url('/profile') ?>"><i class="fas fa-user me-2"></i>Profil Saya</a></li>
            <li><a class="dropdown-item" href="<?= base_url('/dashboard') ?>"><i class="fas fa-gauge me-2"></i>Dasbor</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= base_url('/logout') ?>"><i class="fas fa-right-from-bracket me-2"></i>Keluar</a></li>
          </ul>
        </div>
      </div>
    </header>

    <?php if ($flashSuccess = \Core\Session::flash('success')): ?>
      <div class="app-content-pad pt-3 pb-0">
        <div class="alert alert-success d-flex align-items-center gap-2 rounded-4 border-0 shadow-sm mb-0" role="alert">
          <i class="fas fa-circle-check"></i> <?= e($flashSuccess) ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($flashError = \Core\Session::flash('error')): ?>
      <div class="app-content-pad pt-3 pb-0">
        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-4 border-0 shadow-sm mb-0" role="alert">
          <i class="fas fa-triangle-exclamation"></i> <?= e($flashError) ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($waUrl = \Core\Session::flash('wa_prefill')): ?>
      <script>
        Swal.fire({
          title: 'Buka WhatsApp untuk mengirim pemberitahuan ke client?',
          html: 'Klik <strong>Buka WhatsApp</strong> untuk membuka chat yang sudah terisi pesan otomatis.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Buka WhatsApp',
          cancelButtonText: 'Nanti'
        }).then(function (res) {
          if (res.isConfirmed) {
            window.open('<?= e($waUrl) ?>', '_blank');
          }
        });
      </script>
    <?php endif; ?>

    <main class="app-content">
      <div class="app-content-pad">
        <?= $content ?>
      </div>
    </main>
  </div>
</div>

<?php
$__mobileQuick = match ($__role) {
  'pasien' => ['href' => '/wellness', 'icon' => 'heart', 'label' => 'Wellness', 'active' => str_starts_with($__appPath, '/wellness') || str_starts_with($__appPath, '/mood') || str_starts_with($__appPath, '/journal')],
  'psikolog', 'psikiater' => ['href' => '/schedule', 'icon' => 'calendar-days', 'label' => 'Jadwal', 'active' => str_starts_with($__appPath, '/schedule')],
  default => ['href' => '/admin/users', 'icon' => 'users', 'label' => 'Pengguna', 'active' => str_starts_with($__appPath, '/admin/users')],
};
$__mobileSessionLabel = $__role === 'pasien' ? 'Sesi Saya' : 'Sesi';
?>
<nav class="app-mobile-bottom-nav" aria-label="Navigasi cepat dashboard">
  <a href="<?= base_url('/dashboard') ?>" class="<?= str_starts_with($__appPath, '/dashboard') ? 'active' : '' ?>">
    <i class="fas fa-chart-pie"></i><span>Dasbor</span>
  </a>
  <a href="<?= base_url('/calendar') ?>" class="<?= str_starts_with($__appPath, '/calendar') ? 'active' : '' ?>">
    <i class="fas fa-calendar-alt"></i><span>Kalender</span>
  </a>
  <a href="<?= base_url($__mobileQuick['href']) ?>" class="<?= $__mobileQuick['active'] ? 'active' : '' ?>">
    <i class="fas fa-<?= $__mobileQuick['icon'] ?>"></i><span><?= e($__mobileQuick['label']) ?></span>
  </a>
  <a href="<?= base_url('/consultation') ?>" class="<?= str_starts_with($__appPath, '/consultation') ? 'active' : '' ?>">
    <i class="fas fa-calendar-check"></i><span><?= e($__mobileSessionLabel) ?></span>
  </a>
  <button type="button" class="app-mobile-menu-trigger" data-mobile-sidebar-toggle aria-controls="appSidebar" aria-label="Buka semua menu">
    <i class="fas fa-bars"></i><span>Menu</span>
  </button>
</nav>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>window.NFG_BASE_URL = "<?= rtrim(base_url(), '/') ?>";</script>
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/app-shell.js') ?>"></script>
