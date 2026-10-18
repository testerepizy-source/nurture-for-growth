<?php
use Core\Auth;
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function navActive(string $needle, string $path): string {
    return str_contains($path, $needle) ? 'active' : '';
}
$role = Auth::roleName();
?>
<div class="sidebar-inner">
  <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link <?= navActive('/dashboard', $path) ?>" href="<?= base_url('/dashboard') ?>"><i class="fas fa-chart-pie"></i> Dasbor</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/calendar', $path) ?>" href="<?= base_url('/calendar') ?>"><i class="fas fa-calendar-alt"></i> Kalender</a></li>

    <?php if ($role === 'pasien'): ?>
    <li class="nav-heading">Layanan Saya</li>
    <li class="nav-item"><a class="nav-link <?= navActive('/assessment', $path) ?>" href="<?= base_url('/assessment') ?>"><i class="fas fa-clipboard-list"></i> Asesmen</a></li>
    <?php $wellnessActive = str_contains($path, '/wellness') || str_contains($path, '/mood') || str_contains($path, '/journal'); ?>
    <li class="nav-item"><a class="nav-link <?= $wellnessActive ? 'active' : '' ?>" href="<?= base_url('/wellness') ?>"><i class="fas fa-heart"></i> Mood &amp; Journal</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/matching', $path) ?>" href="<?= base_url('/matching') ?>"><i class="fas fa-wand-magic-sparkles"></i> Booking Konseling</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/consultation', $path) ?>" href="<?= base_url('/consultation') ?>"><i class="fas fa-calendar-check"></i> Riwayat Konseling Saya</a></li>
    <!-- <li class="nav-item"><a class="nav-link <?= navActive('/education', $path) ?>" href="<?= base_url('/education') ?>"><i class="fas fa-graduation-cap"></i> Artikel</a></li> -->
    <!-- <li class="nav-item"><a class="nav-link <?= navActive('/faq', $path) ?>" href="<?= base_url('/faq') ?>"><i class="fas fa-circle-question"></i> FAQ</a></li> -->
    <?php endif; ?>

    <?php if (in_array($role, ['psikolog', 'psikiater'], true)): ?>
    <li class="nav-heading">Layanan Klinis</li>
    <li class="nav-item"><a class="nav-link <?= navActive('/schedule', $path) ?>" href="<?= base_url('/schedule') ?>"><i class="fas fa-calendar-days"></i> Jadwal Praktik</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/consultation', $path) ?>" href="<?= base_url('/consultation') ?>"><i class="fas fa-calendar-check"></i> Booking &amp; Approval</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/patients', $path) ?>" href="<?= base_url('/patients') ?>"><i class="fas fa-users"></i> Daftar Client</a></li>
    <!-- <li class="nav-item"><a class="nav-link <?= navActive('/promo', $path) ?>" href="<?= base_url('/promo') ?>"><i class="fas fa-tag"></i> Promo Layanan</a></li> -->
    <!-- <li class="nav-item"><a class="nav-link <?= navActive('/report', $path) ?>" href="<?= base_url('/report') ?>"><i class="fas fa-file-export"></i> Laporan</a></li> -->
    <?php endif; ?>

    <li class="nav-item"><a class="nav-link <?= navActive('/profile', $path) ?>" href="<?= base_url('/profile') ?>"><i class="fas fa-user"></i> Profil Saya</a></li>

    <?php if (Auth::can('user.manage') || Auth::can('assessment.manage') || Auth::can('article.manage') || Auth::can('audit.view') || Auth::can('provider.manage') || Auth::can('faq.manage')): ?>
    <li class="nav-heading">Administrasi Klinik</li>
    <?php endif; ?>

    <?php if (Auth::can('user.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/users', $path) ?>" href="<?= base_url('/admin/users') ?>"><i class="fas fa-users-gear"></i> Data Pengguna</a></li>
    <?php endif; ?>
    <?php if (Auth::can('patient.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/patients', $path) ?>" href="<?= base_url('/admin/patients') ?>"><i class="fas fa-hospital-user"></i> Data Client</a></li>
    <?php endif; ?>
    <?php if (Auth::can('provider.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/psychologists', $path) ?>" href="<?= base_url('/admin/psychologists') ?>"><i class="fas fa-user-doctor"></i> Data Psikolog</a></li>
    
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/concern-tags', $path) ?>" href="<?= base_url('/admin/concern-tags') ?>"><i class="fas fa-tags"></i> Data Tag Keluhan (Sistem Pencocokan Cerdas)</a></li>
    <?php endif; ?>
    <?php if (Auth::can('user.manage')): ?>
    <?php endif; ?>
    <?php if (Auth::can('job.manage')): ?>
    <!-- <li class="nav-item"><a class="nav-link <?= navActive('/admin/jobs', $path) ?>" href="<?= base_url('/admin/jobs') ?>"><i class="fas fa-briefcase"></i> Lowongan Pekerjaan</a></li> -->
    <?php endif; ?>
    <?php if (Auth::can('promo.manage') && !in_array($role, ['psikolog','psikiater'], true)): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/promos', $path) ?>" href="<?= base_url('/admin/promos') ?>"><i class="fas fa-tags"></i> Data Promo Konseling</a></li>
    <?php endif; ?>
    <?php if (Auth::can('consultation.approve')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/cancellations', $path) ?>" href="<?= base_url('/admin/cancellations') ?>"><i class="fas fa-rotate-left"></i> Pembatalan &amp; Refund</a></li>
    <?php endif; ?>
    <?php if (Auth::can('payment.channel.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/payment-channels', $path) ?>" href="<?= base_url('/admin/payment-channels') ?>"><i class="fas fa-money-bill-transfer"></i> Kanal Pembayaran</a></li>
    <?php endif; ?>
    <?php if (Auth::can('assessment.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/assessments', $path) ?>" href="<?= base_url('/admin/assessments') ?>"><i class="fas fa-list-check"></i> Data Asesmen</a></li>
    <?php endif; ?>
    <?php if (Auth::can('article.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/articles', $path) ?>" href="<?= base_url('/admin/articles') ?>"><i class="fas fa-newspaper"></i> Data Artikel</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/hero-slides', $path) ?>" href="<?= base_url('/admin/hero-slides') ?>"><i class="fas fa-images"></i> Slide Utama</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/service-videos', $path) ?>" href="<?= base_url('/admin/service-videos') ?>"><i class="fas fa-circle-play"></i> Video Layanan</a></li>
    <?php endif; ?>
    <?php if (Auth::can('faq.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/faq', $path) ?>" href="<?= base_url('/admin/faq') ?>"><i class="fas fa-circle-question"></i> Data FAQ</a></li>
    <!-- <li class="nav-item"><a class="nav-link <?= navActive('/admin/templates', $path) ?>" href="<?= base_url('/admin/templates') ?>"><i class="fas fa-envelope-open-text"></i> Template Notifikasi/Email</a></li> -->
    <?php endif; ?>
    <?php if (Auth::can('report.view') && !in_array($role, ['psikolog','psikiater'], true)): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/report', $path) ?>" href="<?= base_url('/report') ?>"><i class="fas fa-file-export"></i> Laporan</a></li>
    <?php endif; ?>
    <?php if (Auth::can('audit.view')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/crisis', $path) ?>" href="<?= base_url('/admin/crisis') ?>"><i class="fas fa-triangle-exclamation"></i> Insiden Krisis</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/contact-messages', $path) ?>" href="<?= base_url('/admin/contact-messages') ?>"><i class="fas fa-inbox"></i> Pesan Kontak</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/audit', $path) ?>" href="<?= base_url('/admin/audit') ?>"><i class="fas fa-shield-halved"></i> Log Audit</a></li>
    <?php endif; ?>
    <?php if (Auth::can('backup.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= navActive('/super/backup', $path) ?>" href="<?= base_url('/super/backup') ?>"><i class="fas fa-database"></i> Cadangan &amp; Pulihkan</a></li>
    <?php endif; ?>

    <?php if ($role === 'super_admin'): ?>
    <li class="nav-heading">Super Admin</li>
    <li class="nav-item"><a class="nav-link <?= navActive('/admin/roles', $path) ?>" href="<?= base_url('/admin/roles') ?>"><i class="fas fa-user-shield"></i> Peran &amp; Izin</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/super/settings', $path) ?>" href="<?= base_url('/super/settings') ?>"><i class="fas fa-gears"></i> Pengaturan &amp; Integrasi API</a></li>
    <li class="nav-item"><a class="nav-link <?= navActive('/super/monitoring', $path) ?>" href="<?= base_url('/super/monitoring') ?>"><i class="fas fa-server"></i> Pemantauan</a></li>
    <?php endif; ?>
  </ul>

  <hr class="my-3">
   
</div>
