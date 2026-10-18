<style>
  .job-hero{ padding: 48px 0 36px; }
  .job-back{ text-decoration:none; font-size:.88rem; font-weight:600; color:var(--ink-soft); display:inline-flex; align-items:center; gap:8px; margin-bottom:22px; transition:color .15s ease; }
  .job-back:hover{ color:var(--teal-ink); }
  .job-hero-icon{ width:72px; height:72px; border-radius:22px; background:var(--mint-tint); color:var(--teal-ink); font-size:1.8rem; display:flex; align-items:center; justify-content:center; box-shadow:var(--shadow-sm); }
  .job-hero h1{ font-family:var(--font-display); font-weight:800; color:var(--ink); font-size:clamp(1.7rem,3vw,2.5rem); line-height:1.2; margin:18px 0 10px; }
  .job-meta-chips{ display:flex; flex-wrap:wrap; gap:8px; }
  .job-meta-chip{ font-size:.8rem; font-weight:600; padding:7px 14px; border-radius:999px; background:#fff; color:var(--ink-soft); box-shadow:var(--shadow-sm); display:inline-flex; align-items:center; gap:6px; }
  .job-meta-chip i{ color:var(--teal-ink); }

  .job-body-card{ background:#fff; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); padding:32px; height:100%; }
  .job-body-card h5{ font-family:var(--font-display); font-weight:800; color:var(--ink); display:flex; align-items:center; gap:10px; }
  .job-body-card h5 .mini-icon{ width:34px; height:34px; flex:0 0 34px; border-radius:10px; background:var(--mint-tint); color:var(--teal-ink); font-size:.85rem; display:inline-flex; align-items:center; justify-content:center; }
  .job-section-text{ white-space:pre-line; line-height:1.75; }

  .check-list{ list-style:none; margin:0; padding:0; }
  .check-list li{ position:relative; padding:0 0 12px 30px; line-height:1.6; }
  .check-list li::before{ content:'\f00c'; font-family:'Font Awesome 6 Free'; font-weight:900; position:absolute; left:0; top:2px; width:20px; height:20px; border-radius:50%; background:var(--mint-tint); color:var(--teal-ink); font-size:.62rem; display:flex; align-items:center; justify-content:center; }

  .gift-list{ list-style:none; margin:0; padding:0; }
  .gift-list li{ position:relative; padding:0 0 12px 30px; line-height:1.6; }
  .gift-list li::before{ content:'\e4dc'; font-family:'Font Awesome 6 Free'; font-weight:900; position:absolute; left:0; top:2px; width:20px; height:20px; border-radius:50%; background:#fdeef4; color:var(--pink); font-size:.6rem; display:flex; align-items:center; justify-content:center; }

  .job-side-card{ background:#fff; border-radius:var(--radius-md); box-shadow:var(--shadow-md); overflow:hidden; position:sticky; top:90px; }
  .job-side-head{ background:linear-gradient(120deg,#063f35,#00594a); color:var(--paper); padding:24px 26px; }
  .job-side-head .label{ font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; opacity:.75; font-weight:700; }
  .job-side-head .salary-big{ font-family:var(--font-display); font-weight:800; font-size:1.45rem; }
  .job-summary-row{ display:flex; justify-content:space-between; gap:12px; padding:13px 26px; border-bottom:1px dashed rgba(18,36,29,.08); font-size:.88rem; }
  .job-summary-row:last-child{ border-bottom:0; }
  .job-summary-row .k{ color:var(--ink-faint); font-weight:500; }
  .job-summary-row .v{ color:var(--ink); font-weight:700; text-align:right; }
  .apply-btn{ display:flex; align-items:center; justify-content:center; gap:10px; background:var(--teal); color:#fff !important; font-weight:800; border-radius:999px; padding:14px; text-decoration:none; transition:all .18s ease; }
  .apply-btn:hover{ background:#00b58e; transform:translateY(-2px); box-shadow:var(--shadow-md); }
  .deadline-pill{ display:inline-flex; align-items:center; gap:8px; background:#fff4e3; color:#a05c00; font-weight:700; font-size:.82rem; border-radius:999px; padding:8px 16px; }
  .deadline-pill.expired{ background:#fdecec; color:#a03030; }

  .related-job{ display:block; background:#fff; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); padding:18px 20px; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; height:100%; }
  .related-job:hover{ transform:translateY(-3px); box-shadow:var(--shadow-md); }
</style>

<?php
$typeLabels = \App\Models\JobVacancy::EMPLOYMENT_TYPES;
$modeLabels = \App\Models\JobVacancy::WORK_MODES;
$salary = \App\Models\JobVacancy::salaryLabel($job);
$daysLeft = null;
if (!empty($job['deadline_date'])) {
    $daysLeft = (int) floor((strtotime($job['deadline_date']) - strtotime(date('Y-m-d'))) / 86400);
}
$applyEmail = $job['contact_email'] ?: 'admin@nurtureforgrowth.com';
$applySubject = rawurlencode('Lamaran Posisi ' . $job['title'] . ' - NurtureForGrowth');
?>

<section class="job-hero">
  <div class="container">
    <a href="<?= base_url('/karier') ?>" class="job-back"><i class="fas fa-arrow-left"></i> Kembali ke semua lowongan</a>
    <div class="d-flex gap-3 align-items-start mb-3">
      <div class="job-hero-icon"><i class="fa-solid fa-briefcase"></i></div>
      <div>
        <h1><?= e($job['title']) ?></h1>
        <div class="job-dept fw-semibold" style="font-size:.8rem;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em;">
          <?= e($job['department'] ?: 'NurtureForGrowth') ?>
        </div>
      </div>
    </div>
    <div class="job-meta-chips">
      <span class="job-meta-chip"><i class="fas fa-clock"></i> <?= e($typeLabels[$job['employment_type']]) ?></span>
      <span class="job-meta-chip"><i class="fas fa-laptop-house"></i> <?= e($modeLabels[$job['work_mode']]) ?></span>
      <span class="job-meta-chip"><i class="fas fa-location-dot"></i> <?= e($job['location'] ?: 'Jakarta') ?></span>
      <?php if ($daysLeft !== null): ?>
        <?php if ($daysLeft < 0): ?>
          <span class="deadline-pill expired"><i class="fas fa-circle-xmark"></i> Lowongan telah ditutup</span>
        <?php else: ?>
          <span class="deadline-pill"><i class="far fa-calendar"></i> Tutup <?= format_date($job['deadline_date'], 'd M Y') ?></span>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="container pb-5">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="job-body-card">
        <?php if ($salary): ?>
          <div class="mb-4 p-3 rounded-4" style="background:var(--mint-tint)">
            <span class="fw-bold" style="color:var(--teal-ink)"><i class="fas fa-wallet me-2"></i>Rentang Gaji: <?= e($salary) ?>/bulan</span>
          </div>
        <?php endif; ?>

        <div class="mb-4">
          <h5><span class="mini-icon"><i class="fas fa-circle-info"></i></span> Tentang Posisi Ini</h5>
          <p class="job-section-text mt-3"><?= e($job['description'] ?: $job['short_description'] ?: '-') ?></p>
        </div>

        <?php if (!empty($job['requirements'])): ?>
        <div class="mb-4">
          <h5><span class="mini-icon"><i class="fas fa-list-check"></i></span> Kualifikasi</h5>
          <ul class="check-list mt-3">
            <?php foreach (explode("\n", $job['requirements']) as $req): ?>
              <li><?= e($req) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($job['benefits'])): ?>
        <div class="mb-2">
          <h5><span class="mini-icon" style="background:#fdeef4;color:var(--pink)"><i class="fas fa-gift"></i></span> Benefit</h5>
          <ul class="gift-list mt-3">
            <?php foreach (explode("\n", $job['benefits']) as $benefit): ?>
              <li><?= e($benefit) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="job-side-card">
        <div class="job-side-head">
          <div class="label">Ringkasan Posisi</div>
          <div class="salary-big mt-1"><?= $salary ? e($salary) : 'Menarik & Kompetitif' ?></div>
          <?php if ($salary): ?><div class="small" style="opacity:.8">per bulan</div><?php endif; ?>
        </div>
        <div class="py-2">
          <div class="job-summary-row"><span class="k">Tipe Kerja</span><span class="v"><?= e($typeLabels[$job['employment_type']]) ?></span></div>
          <div class="job-summary-row"><span class="k">Sistem Kerja</span><span class="v"><?= e($modeLabels[$job['work_mode']]) ?></span></div>
          <div class="job-summary-row"><span class="k">Lokasi</span><span class="v"><?= e($job['location'] ?: 'Jakarta') ?></span></div>
          <div class="job-summary-row"><span class="k">Departemen</span><span class="v"><?= e($job['department'] ?: '-') ?></span></div>
          <div class="job-summary-row"><span class="k">Batas Lamaran</span><span class="v"><?= $job['deadline_date'] ? format_date($job['deadline_date'], 'd M Y') : 'Diterima terus' ?></span></div>
        </div>
        <div class="p-4 pt-2">
          <?php if ($daysLeft === null || $daysLeft >= 0): ?>
            <a href="mailto:<?= e($applyEmail) ?>?subject=<?= $applySubject ?>" class="apply-btn w-100">
              <i class="fas fa-paper-plane"></i> Kirim Lamaran
            </a>
            <p class="small text-muted text-center mt-3 mb-0"><?= e($job['apply_note'] ?: 'Kirim CV beserta portofolio singkat melalui email.') ?></p>
          <?php else: ?>
            <div class="alert alert-secondary small text-center mb-0">Lowongan ini sudah ditutup. Lihat lowongan lain di bawah.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($related)): ?>
  <div class="mt-5 pt-2">
    <h5 class="fw-bold" style="font-family:var(--font-display);color:var(--ink)">Lowongan Lainnya</h5>
    <div class="row g-3 mt-1">
      <?php foreach ($related as $r): ?>
        <div class="col-md-4">
          <a href="<?= base_url('/karier/' . e($r['slug'])) ?>" class="related-job">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-bold" style="color:var(--ink);font-family:var(--font-display)"><?= e($r['title']) ?></div>
                <div class="small text-muted"><?= e($typeLabels[$r['employment_type']]) ?> &middot; <?= e($modeLabels[$r['work_mode']]) ?></div>
              </div>
              <span style="color:var(--teal)"><i class="fas fa-arrow-up-right"></i></span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>

<script type="application/ld+json">
<?php
$schemaJob = [
    '@context' => 'https://schema.org',
    '@type' => 'JobPosting',
    'title' => $job['title'],
    'description' => strip_tags((string) $job['description']),
    'datePosted' => date('c', strtotime($job['created_at'])),
    'employmentType' => strtoupper($job['employment_type']),
    'hiringOrganization' => [
        '@type' => 'Organization',
        'name' => config_get('app_name'),
        'sameAs' => absolute_url(),
    ],
    'jobLocation' => [
        '@type' => 'Place',
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job['location'] ?: 'Jakarta', 'addressCountry' => 'ID'],
    ],
];
if (!empty($job['deadline_date'])) {
    $schemaJob['validThrough'] = date('Y-m-d', strtotime($job['deadline_date'])) . 'T23:59:59+07:00';
}
?>
<?= json_encode($schemaJob, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
