<style>
  .career-hero{ padding: 72px 0 40px; }
  .career-hero h1{ font-family: var(--font-display); font-weight:800; font-size: clamp(2rem, 4vw, 3.1rem); line-height:1.12; color:var(--ink); margin:18px 0 14px; }
  .career-hero h1 em{ font-style:italic; font-family: var(--font-accent); font-weight:500; color:var(--pink); }
  .career-lede{ max-width:560px; font-size:1.05rem; line-height:1.7; }
  .career-stat{ display:inline-flex; align-items:center; gap:10px; background:#fff; border-radius:999px; padding:10px 20px; box-shadow:var(--shadow-sm); font-weight:600; color:var(--ink); }
  .career-stat i{ color:var(--teal-ink); background:var(--mint-tint); border-radius:50%; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; }

  .job-filter{ display:flex; flex-wrap:wrap; gap:10px; margin:28px 0 8px; }
  .job-filter a{ text-decoration:none; font-size:.88rem; font-weight:600; color:var(--ink-soft); background:#fff; border:1.5px solid rgba(18,36,29,.08); border-radius:999px; padding:9px 18px; transition:all .18s ease; }
  .job-filter a:hover{ border-color:var(--teal); color:var(--teal-ink); transform:translateY(-1px); }
  .job-filter a.active{ background:var(--ink); border-color:var(--ink); color:var(--paper); }
  .job-filter a span{ opacity:.65; font-weight:500; margin-left:4px; }

  .job-card{ position:relative; display:flex; flex-direction:column; height:100%; background:var(--paper-raised); border-radius:var(--radius-md); box-shadow:var(--shadow-sm); transition:transform .2s ease, box-shadow .2s ease; overflow:hidden; text-decoration:none; }
  .job-card:hover{ transform:translateY(-4px); box-shadow:var(--shadow-md); }
  .job-card-top{ display:flex; align-items:center; gap:14px; }
  .job-icon{ width:52px; height:52px; flex:0 0 52px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; color:var(--teal-ink); background:var(--mint-tint); }
  .job-card h6.job-title{ font-family:var(--font-display); font-weight:800; color:var(--ink); font-size:1.02rem; margin:0 44px 0 0; line-height:1.35; }
  .job-dept{ font-size:.78rem; color:var(--ink-faint); font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
  .job-arrow{ position:absolute; top:18px; right:18px; width:32px; height:32px; border-radius:50%; background:var(--mint-tint); color:var(--teal-ink); display:flex; align-items:center; justify-content:center; font-size:.75rem; transition:all .2s ease; }
  .job-card:hover .job-arrow{ background:var(--teal); color:#fff; transform:rotate(45deg); }
  .job-desc{ font-size:.88rem; line-height:1.55; margin-top:12px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.7em; }
  .job-chips{ display:flex; flex-wrap:wrap; gap:6px; margin-top:14px; }
  .job-chip{ font-size:.72rem; font-weight:600; color:var(--ink-soft); background:rgba(18,36,29,.05); border-radius:999px; padding:5px 11px; display:inline-flex; align-items:center; gap:5px; }
  .job-chip.salary{ background:var(--mint-tint); color:var(--teal-ink); }
  .job-card-foot{ display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:auto; padding-top:14px; border-top:1px dashed rgba(18,36,29,.1); }
  .job-deadline{ font-size:.75rem; font-weight:600; }
  .job-deadline.ok{ color:var(--teal-ink); }
  .job-deadline.soon{ color:var(--orange); }
  .job-open-pill{ font-size:.72rem; font-weight:700; color:#0a7d33; background:#e5f6ec; border-radius:999px; padding:5px 12px; white-space:nowrap; }

  .empty-jobs{ text-align:center; padding:64px 20px; background:#fff; border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); }
  .empty-jobs .icon-wrap{ width:84px; height:84px; margin:0 auto 20px; border-radius:50%; background:var(--mint-tint); color:var(--teal-ink); display:flex; align-items:center; justify-content:center; font-size:2rem; }

  .career-cta{ background:linear-gradient(120deg, #063f35, #00594a); color:var(--paper); border-radius:var(--radius-lg); padding:48px; position:relative; overflow:hidden; }
  .career-cta::after{ content:''; position:absolute; width:260px; height:260px; border-radius:50%; background:rgba(255,255,255,.06); top:-90px; right:-70px; }
  .career-cta h4{ font-family:var(--font-display); font-weight:800; color:#fff; }
</style>

<section class="career-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="section-eyebrow">Karier di NFG</div>
        <h1>Tumbuh Bersama Kami, <em>Temukan Peranmu</em></h1>
        <p class="career-lede">Kami mencari orang-orang yang peduli pada kesehatan mental &mdash; psikolog berbakat maupun
          talenta pendukung yang ingin ikut mendampingi lebih banyak orang untuk tumbuh.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <span class="career-stat"><i class="fas fa-briefcase"></i> <?= (int) $totalOpen ?> lowongan terbuka</span>
      </div>
    </div>

    <div class="job-filter">
      <a href="<?= base_url('/karier') ?>" class="<?= $activeType === '' ? 'active' : '' ?>">Semua <span><?= array_sum($counts) ?></span></a>
      <?php foreach (\App\Models\JobVacancy::EMPLOYMENT_TYPES as $key => $label): if (empty($counts[$key])) continue; ?>
        <a href="<?= base_url('/karier?type=' . $key) ?>" class="<?= $activeType === $key ? 'active' : '' ?>"><?= e($label) ?> <span><?= (int) $counts[$key] ?></span></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="container pb-5">
  <?php if (empty($vacancies)): ?>
    <div class="empty-jobs">
      <div class="icon-wrap"><i class="fas fa-magnifying-glass"></i></div>
      <h5 class="fw-bold" style="font-family:var(--font-display)">Belum ada lowongan<?= $activeType ? ' pada kategori ini' : '' ?></h5>
      <p class="text-muted mb-4">Coba periksa kategori lain atau tinggalkan kontak kami &mdash; kami akan mengabari saat ada posisi baru.</p>
      <a href="<?= base_url('/contact') ?>" class="btn btn-hero">Hubungi Kami <span class="icon-box"><i class="fas fa-arrow-right"></i></span></a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($vacancies as $v):
        $daysLeft = null;
        if (!empty($v['deadline_date'])) {
            $daysLeft = (int) floor((strtotime($v['deadline_date']) - strtotime(date('Y-m-d'))) / 86400);
        }
        $salary = \App\Models\JobVacancy::salaryLabel($v);
      ?>
      <div class="col-md-6 col-lg-4">
        <a href="<?= base_url('/karier/' . e($v['slug'])) ?>" class="job-card p-4">
          <div class="job-card-top">
            <div class="job-icon"><i class="<?= $typeIcons[$v['employment_type']] ?? 'fa-solid fa-briefcase' ?>"></i></div>
            <div>
              <h6 class="job-title"><?= e($v['title']) ?></h6>
              <div class="job-dept"><?= e($v['department'] ?: 'NurtureForGrowth') ?></div>
            </div>
          </div>
          <span class="job-arrow"><i class="fas fa-arrow-up-right"></i></span>
          <p class="job-desc text-muted"><?= e($v['short_description'] ?: mb_substr(strip_tags((string) $v['description']), 0, 140)) ?></p>
          <div class="job-chips">
            <span class="job-chip"><i class="fas fa-location-dot"></i> <?= e($v['location'] ?: 'Jakarta') ?></span>
            <span class="job-chip"><i class="fas fa-laptop-house"></i> <?= e(\App\Models\JobVacancy::WORK_MODES[$v['work_mode']]) ?></span>
            <?php if ($salary): ?><span class="job-chip salary"><i class="fas fa-wallet"></i> <?= e($salary) ?>/bulan</span><?php endif; ?>
          </div>
          <div class="job-card-foot">
            <span class="job-open-pill"><i class="fas fa-circle-check me-1"></i>Diterima terus-menerus</span>
            <?php if ($daysLeft !== null && $daysLeft <= 14): ?>
              <span class="job-deadline <?= $daysLeft <= 5 ? 'soon' : 'ok' ?>">
                <i class="far fa-clock me-1"></i><?= $daysLeft <= 0 ? 'Terakhir hari ini!' : 'Tutup dalam ' . $daysLeft . ' hari' ?>
              </span>
            <?php endif; ?>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="career-cta mt-5">
    <div class="row align-items-center g-3">
      <div class="col-md-8">
        <h4>Tidak menemukan posisi yang cocok?</h4>
        <p class="mb-0" style="opacity:.85">Kirimkan CV dan ceritakan minatmu &mdash; kami selalu senang mengenal calon rekan baru.</p>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="<?= base_url('/join-us') ?>" class="btn btn-hero">Gabung Jadi Mitra <span class="icon-box"><i class="fas fa-arrow-right"></i></span></a>
      </div>
    </div>
  </div>
</section>
