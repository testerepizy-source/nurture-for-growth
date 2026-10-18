<?php
// Wellness: Journal & Mood Tracker
$js = array_slice(array_reverse($history ?? []), 0, 60); // urut tanggal naik
$todayMood = null;
foreach (($history ?? []) as $h) { if ($h['mood_date'] === date('Y-m-d')) { $todayMood = $h; break; } }

// Hitung streak hari berurutan
$dates = array_map(fn($h) => $h['mood_date'], $history ?? []);
$dates = array_unique($dates); sort($dates);
$streak = 0;
$cursor = strtotime(date('Y-m-d'));
if ($dates && end($dates) !== date('Y-m-d')) $cursor = strtotime('-1 day');
while (in_array(date('Y-m-d', $cursor), $dates)) { $streak++; $cursor = strtotime('-1 day', $cursor); }

$wkVals = $weekly['values'] ?? [];
$moVals = $monthly['values'] ?? [];
$avgWk = $wkVals ? round(array_sum($wkVals) / count($wkVals), 1) : null;
$avgMo = $moVals ? round(array_sum($moVals) / count($moVals), 1) : null;

$moodMeta = [
    1 => ['😭', 'Sangat Berat'], 2 => ['😢', 'Berat'], 3 => ['😔', 'Sedih'], 4 => ['😕', 'Kurang Baik'],
    5 => ['😐', 'Netral'], 6 => ['🙂', 'Cukup Baik'], 7 => ['😊', 'Baik'], 8 => ['😄', 'Cerah'],
    9 => ['😁', 'Sangat Cerah'], 10 => ['🤩', 'Luar Biasa'],
];
$hour = (int) date('G');
$sapa = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
$bulanId = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tglId = date('j') . ' ' . $bulanId[(int) date('n')] . ' ' . date('Y');
?>

<style>
.wl-hero{ background:linear-gradient(120deg,var(--teal-ink,#00443A),var(--teal-deep,#00697D)); border-radius:20px; color:#fff; padding:clamp(1.25rem,3vw,2rem); position:relative; overflow:hidden; }
.wl-hero::after{ content:''; position:absolute; right:-70px; top:-90px; width:260px; height:260px; border-radius:50%; background:rgba(255,255,255,.08); }
.wl-hero::before{ content:''; position:absolute; right:40px; bottom:-110px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,.06); }
.wl-chip{ background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.25); border-radius:999px; padding:.3rem .8rem; font-size:.78rem; font-weight:600; display:inline-flex; align-items:center; gap:.45rem; backdrop-filter:blur(4px); }
.wl-btn{ border-radius:12px; font-weight:700; }
@media (max-width:575.98px){ .wl-btn{ width:100%; } .wl-hero-actions{ width:100%; } }

.stat-card{ background:var(--paper-raised,#fff); border:1px solid var(--sand-line,#EAE2D0); border-radius:16px; padding:1rem 1.15rem; display:flex; align-items:center; gap:.9rem; transition:transform .18s ease, box-shadow .18s ease; height:100%; }
.stat-card:hover{ transform:translateY(-3px); box-shadow:0 12px 28px rgba(18,36,29,.09); }
.stat-ico{ width:46px; min-width:46px; height:46px; border-radius:13px; display:grid; place-items:center; font-size:1.05rem; }
.stat-val{ font-size:1.35rem; font-weight:800; line-height:1.1; color:var(--ink,#12241D); }
.stat-lbl{ font-size:.76rem; color:var(--ink-faint,#8A9791); font-weight:600; }

.panel{ background:var(--paper-raised,#fff); border:1px solid var(--sand-line,#EAE2D0); border-radius:18px; padding:clamp(1rem,2.2vw,1.4rem); height:100%; }
.panel-title{ font-weight:800; font-size:1rem; color:var(--ink,#12241D); margin-bottom:1rem; display:flex; align-items:center; gap:.55rem; }
.panel-title i{ color:var(--teal-ink,#00443A); }

.mood-grid{ display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.5rem; }
.mood-opt{ appearance:none; cursor:pointer; }
.mood-opt + label{ border:2px solid var(--sand-line,#EAE2D0); border-radius:13px; padding:.55rem .2rem .35rem; text-align:center; font-size:clamp(1.25rem,3.2vw,1.5rem); transition:all .15s ease; user-select:none; margin:0; }
.mood-opt + label small{ display:block; font-size:.58rem; font-weight:700; color:var(--ink-faint,#8A9791); margin-top:.15rem; }
.mood-opt:hover + label{ border-color:var(--teal,#00C79C); transform:translateY(-2px); }
.mood-opt:checked + label{ border-color:var(--teal,#00C79C); background:var(--mint-tint,#EAF6F1); box-shadow:0 6px 16px rgba(0,199,156,.22); }
.mood-opt:focus-visible + label{ outline:3px solid rgba(0,199,156,.45); outline-offset:2px; }

.mood-feedback{ font-weight:800; font-size:1rem; transition:color .2s ease; }
.chart-box{ position:relative; height:250px; }
@media (max-width:991.98px){ .chart-box{ height:220px; } }

.today-ring{ width:84px; min-width:84px; height:84px; border-radius:50%; display:grid; place-items:center; font-size:2.4rem; background:var(--mint-tint,#EAF6F1); border:3px solid var(--teal,#00C79C); }
.chip-soft{ background:var(--mint-tint,#EAF6F1); color:var(--teal-ink,#00443A); border-radius:999px; padding:.28rem .7rem; font-size:.74rem; font-weight:700; display:inline-flex; gap:.4rem; align-items:center; }

.entry-row{ display:flex; gap:.85rem; align-items:flex-start; padding:.85rem .35rem; border-bottom:1px solid var(--sand-line,#EAE2D0); }
.entry-row:last-child{ border-bottom:0; }
.entry-ico{ width:42px; min-width:42px; height:42px; border-radius:12px; display:grid; place-items:center; font-size:1rem; background:rgba(0,199,156,.1); color:var(--teal-ink,#00443A); }
.entry-row h6{ font-weight:700; font-size:.92rem; margin-bottom:.15rem; color:var(--ink,#12241D); overflow-wrap:anywhere; }
.entry-meta{ font-size:.76rem; color:var(--ink-faint,#8A9791); }
.badge-soft{ font-size:.66rem; font-weight:700; padding:.32em .65em; border-radius:999px; }

.nav-pills .nav-link{ border-radius:12px; font-weight:700; font-size:.86rem; color:var(--ink-soft,#4E5D56); padding:.45rem 1rem; }
.nav-pills .nav-link.active{ background:var(--teal-ink,#00443A); color:#fff; }
</style>

<div class="container-fluid py-4">

  <!-- HERO -->
  <div class="wl-hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative" style="z-index:1">
      <div>
        <span class="wl-chip mb-2"><i class="fas fa-calendar-day"></i><?= $tglId ?></span>
        <h2 class="fw-bold mb-1" style="color:#fff"><?= $sapa ?>! 👋</h2>
        <p class="mb-0" style="color:rgba(255,255,255,.82)">Bagaimana perasaanmu hari ini? Catat mood dan ceritakan harimu di jurnal.</p>
      </div>
      <div class="d-flex wl-hero-actions gap-2">
        <?php if (!$todayMood): ?>
          <a href="#moodForm" class="btn btn-light wl-btn text-nowrap"><i class="fas fa-heart me-1" style="color:var(--teal-ink)"></i>Catat Mood</a>
        <?php endif; ?>
        <a href="<?= base_url('/journal/create') ?>" class="btn wl-btn text-nowrap" style="background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);color:#fff"><i class="fas fa-pen-fancy me-1"></i>Tulis Jurnal</a>
      </div>
    </div>
  </div>

  <!-- STATS -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-ico" style="background:rgba(0,199,156,.12);color:var(--teal-ink)"><i class="fas fa-book"></i></div>
        <div><div class="stat-val"><?= count($journals ?? []) ?></div><div class="stat-lbl">Total Jurnal</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-ico" style="background:rgba(224,122,91,.14);color:#a34e2c"><i class="fas fa-heart"></i></div>
        <div><div class="stat-val"><?= count($history ?? []) ?></div><div class="stat-lbl">Catatan Mood</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-ico" style="background:rgba(255,193,7,.16);color:#9a6b00"><i class="fas fa-chart-line"></i></div>
        <div><div class="stat-val"><?= $avgWk !== null ? $avgWk . '/10' : '-' ?></div><div class="stat-lbl">Rata-rata 7 Hari</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-ico" style="background:rgba(13,110,253,.1);color:#0b5ed7"><i class="fas fa-fire"></i></div>
        <div><div class="stat-val"><?= $streak ?> hari</div><div class="stat-lbl">Streak Beruntun</div></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <!-- FORM MOOD -->
    <div class="col-xl-5" id="moodForm">
      <div class="panel">
        <div class="panel-title"><i class="fas fa-face-smile"></i>Bagaimana perasaan Anda hari ini?</div>
        <form method="POST" action="<?= base_url('/mood') ?>">
          <?= \Core\Csrf::field() ?>
          <input type="hidden" name="return" value="wellness">
          <input type="hidden" name="emoji" id="emoji_input" value="<?= $moodMeta[5][0] ?>">

          <div class="mood-grid mb-2" role="radiogroup" aria-label="Skala mood 1 sampai 10">
            <?php foreach ($moodMeta as $val => [$emo, $lbl]): ?>
              <input type="radio" class="mood-opt" name="mood_scale" value="<?= $val ?>" id="mood<?= $val ?>" autocomplete="off" <?= $val === 5 ? 'checked' : '' ?>>
              <label for="mood<?= $val ?>"><?= $emo ?><small><?= $val ?></small></label>
            <?php endforeach; ?>
          </div>
          <p class="text-center mb-3"><span class="mood-feedback" id="moodFeedback">Netral</span></p>

          <div class="row g-2 mb-2">
            <div class="col-7 col-sm-8">
              <label class="form-label small fw-bold">Tanggal</label>
              <input type="date" name="mood_date" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-5 col-sm-4">
              <label class="form-label small fw-bold">Penyebab</label>
              <input type="text" name="cause" class="form-control" placeholder="mis. kerja" maxlength="100">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Aktivitas</label>
              <input type="text" name="activity" class="form-control" placeholder="mis. olahraga, membaca..." maxlength="100">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Catatan</label>
              <textarea name="note" class="form-control" rows="2" maxlength="500" placeholder="Ceritakan singkat perasaanmu..."></textarea>
            </div>
          </div>
          <button class="btn w-100 wl-btn text-white" style="background:var(--teal-ink,#00443A)" type="submit">
            <i class="fas fa-check me-1"></i>Simpan Mood Hari Ini
          </button>
        </form>
      </div>
    </div>

    <!-- CHARTS -->
    <div class="col-xl-7">
      <div class="row g-3">
        <div class="col-lg-6">
          <div class="panel">
            <div class="panel-title justify-content-between"><span><i class="fas fa-wave-square"></i>Mingguan</span><span class="entry-meta">7 hari terakhir</span></div>
            <?php if (!empty($weekly['labels'])): ?>
              <div class="chart-box"><canvas id="weeklyChart"></canvas></div>
            <?php else: ?>
              <div class="text-center text-muted py-5"><i class="fas fa-chart-line fa-2x mb-2 d-block opacity-50"></i><small>Belum ada data minggu ini.<br>Isi mood di samping untuk mulai.</small></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="panel">
            <div class="panel-title justify-content-between"><span><i class="fas fa-chart-column"></i>Bulanan</span><span class="entry-meta">30 hari terakhir</span></div>
            <?php if (!empty($monthly['labels'])): ?>
              <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
            <?php else: ?>
              <div class="text-center text-muted py-5"><i class="fas fa-chart-column fa-2x mb-2 d-block opacity-50"></i><small>Belum ada data bulan ini.</small></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- RINGKASAN HARI INI -->
    <div class="col-xl-4">
      <div class="panel">
        <div class="panel-title"><i class="fas fa-sun"></i>Hari Ini</div>
        <?php if ($todayMood): ?>
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="today-ring"><?= e($todayMood['emoji'] ?? '🙂') ?></div>
            <div>
              <div class="fw-bold fs-4"><?= (int) $todayMood['mood_scale'] ?>/10</div>
              <div class="chip-soft mt-1"><i class="fas fa-bolt"></i><?= e($todayMood['cause'] ?: '-') ?></div>
            </div>
          </div>
          <div class="progress mb-3" style="height:8px;border-radius:999px">
            <div class="progress-bar" style="width:<?= ((int) $todayMood['mood_scale'] * 10) ?>%;background:var(--teal,#00C79C);border-radius:999px"></div>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <span class="chip-soft"><i class="fas fa-compass"></i><?= e($todayMood['activity'] ?: '-') ?></span>
            <span class="chip-soft" style="background:rgba(224,122,91,.14);color:#a34e2c"><i class="fas fa-fire"></i>Streak <?= $streak ?> hari</span>
          </div>
          <?php if (!empty($todayMood['note'])): ?>
            <hr class="my-3"><p class="small text-muted mb-0 fst-italic">&ldquo;<?= e($todayMood['note']) ?>&rdquo;</p>
          <?php endif; ?>
        <?php else: ?>
          <div class="text-center py-4">
            <div style="font-size:2.6rem">🌤️</div>
            <p class="text-muted small mt-2 mb-3">Belum ada catatan mood hari ini.<br>Yuk mulai harimu dengan mencatat perasaan!</p>
            <a href="#moodForm" class="btn btn-sm wl-btn text-white px-3" style="background:var(--teal-ink,#00443A)"><i class="fas fa-plus me-1"></i>Catat Sekarang</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIWAYAT -->
    <div class="col-xl-8">
      <div class="panel">
        <ul class="nav nav-pills gap-2 mb-3" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pane-journals" type="button" role="tab"><i class="fas fa-book me-1"></i>Jurnal Terbaru</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-moods" type="button" role="tab"><i class="fas fa-heart me-1"></i>Riwayat Mood</button></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="pane-journals" role="tabpanel">
            <?php if (empty($journals)): ?>
              <div class="text-center py-4">
                <div style="font-size:2.2rem">📖</div>
                <p class="text-muted small mt-2 mb-3">Belum ada jurnal. Tuangkan pikiranmu dalam tulisan pertama.</p>
                <a href="<?= base_url('/journal/create') ?>" class="btn btn-sm wl-btn text-white px-3" style="background:var(--teal-ink,#00443A)"><i class="fas fa-pen-fancy me-1"></i>Tulis Jurnal</a>
              </div>
            <?php else: ?>
              <?php foreach (array_slice($journals, 0, 6) as $j): ?>
                <div class="entry-row">
                  <div class="entry-ico"><i class="fas fa-feather-pointed"></i></div>
                  <div class="flex-grow-1 min-w-0">
                    <h6><?= e($j['title']) ?></h6>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <span class="badge-soft" style="background:rgba(13,110,253,.09);color:#0b5ed7"><?= e($j['category'] ?? 'Umum') ?></span>
                      <?php if ($j['is_private']): ?>
                        <span class="badge-soft" style="background:rgba(220,53,69,.1);color:#dc3545"><i class="fas fa-lock"></i>Privat</span>
                      <?php else: ?>
                        <span class="badge-soft" style="background:rgba(25,135,84,.12);color:#198754"><i class="fas fa-lock-open"></i>Dibagikan</span>
                      <?php endif; ?>
                      <span class="entry-meta"><i class="far fa-calendar me-1"></i><?= format_date($j['journal_date']) ?></span>
                    </div>
                  </div>
                  <a href="<?= base_url('/journal/' . $j['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary rounded-3 align-self-center" title="Edit"><i class="fas fa-edit"></i></a>
                </div>
              <?php endforeach; ?>
              <?php if (count($journals) > 6): ?>
                <div class="text-center pt-3"><a href="<?= base_url('/journal') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Lihat Semua Jurnal (<?= count($journals) ?>)</a></div>
              <?php endif; ?>
            <?php endif; ?>
          </div>

          <div class="tab-pane fade" id="pane-moods" role="tabpanel">
            <?php if (empty($history)): ?>
              <div class="text-center py-4">
                <div style="font-size:2.2rem">💛</div>
                <p class="text-muted small mt-2 mb-0">Belum ada riwayat mood.</p>
              </div>
            <?php else: ?>
              <?php foreach (array_slice($history, 0, 8) as $h): ?>
                <div class="entry-row">
                  <div class="entry-ico" style="font-size:1.35rem;background:var(--mint-tint)"><?= e($h['emoji'] ?? '🙂') ?></div>
                  <div class="flex-grow-1 min-w-0">
                    <h6><strong><?= (int) $h['mood_scale'] ?>/10</strong> · <?= e($h['cause'] ?: 'Tanpa penyebab') ?></h6>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <span class="entry-meta"><i class="far fa-calendar me-1"></i><?= format_date($h['mood_date']) ?></span>
                      <?php if (!empty($h['activity'])): ?><span class="entry-meta"><i class="fas fa-compass me-1"></i><?= e($h['activity']) ?></span><?php endif; ?>
                    </div>
                    <?php if (!empty($h['note'])): ?><div class="entry-meta fst-italic mt-1">&ldquo;<?= e(mb_substr($h['note'], 0, 90)) ?><?= mb_strlen($h['note']) > 90 ? '…' : '' ?>&rdquo;</div><?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if (count($history) > 8): ?>
                <div class="text-center pt-3"><a href="<?= base_url('/mood') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Lihat Semua Mood (<?= count($history) ?>)</a></div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  /* ---------- Pemilih mood ---------- */
  const fb = document.getElementById('moodFeedback');
  const fbColor = { bad:'#dc3545', mid:'#9a6b00', good:'#198754' };
  function fbStyle(v){
    if (v <= 3) return [fbColor.bad, 'Sangat Berat'];
    if (v <= 5) return [fbColor.mid, v === 5 ? 'Netral' : 'Kurang Baik'];
    if (v <= 8) return [fbColor.good, v >= 8 ? 'Cerah' : 'Baik'];
    return [fbColor.good, 'Luar Biasa'];
  }
  document.querySelectorAll('.mood-opt').forEach(function (opt) {
    opt.addEventListener('change', function () {
      const emo = this.nextElementSibling.childNodes[0].textContent.trim();
      document.getElementById('emoji_input').value = emo;
      const [color, label] = fbStyle(parseInt(this.value, 10));
      fb.textContent = label; fb.style.color = color;
    });
  });

  /* ---------- Grafik ---------- */
  if (typeof Chart === 'undefined') return;
  const css = getComputedStyle(document.documentElement);
  const txt = css.getPropertyValue('--nfg-text').trim() || '#4E5D56';
  const grid = css.getPropertyValue('--nfg-border').trim() || 'rgba(0,0,0,.08)';
  const TEAL = '#00C79C', TEAL_INK = '#00443A', ORANGE = '#E08A5B';

  function grad(ctx, area, rgb) {
    const g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
    g.addColorStop(0, 'rgba(' + rgb + ',.32)');
    g.addColorStop(1, 'rgba(' + rgb + ',.02)');
    return g;
  }
  const tooltipCfg = {
    callbacks: { label: (c) => ' Mood: ' + c.parsed.y + '/10' },
    backgroundColor: '#12241D', padding: 10, cornerRadius: 10,
    titleFont: { weight: 'bold' }, displayColors: false
  };

  const wc = document.getElementById('weeklyChart');
  if (wc) new Chart(wc, {
    type: 'line',
    data: { labels: <?= json_encode($weekly['labels'] ?? []) ?>,
      datasets: [{ data: <?= json_encode($weekly['values'] ?? []) ?>, borderColor: TEAL_INK, borderWidth: 2.5,
        pointBackgroundColor: TEAL, pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4.5,
        pointHoverRadius: 6.5, tension: .38, fill: true }] },
    options: { responsive: true, maintainAspectRatio: false,
      scales: { x: { ticks: { color: txt, font: { size: 10 } }, grid: { display: false }, border: { color: grid } },
        y: { min: 0, max: 10, ticks: { stepSize: 2, color: txt, font: { size: 10 } }, grid: { color: grid } } },
      plugins: { legend: { display: false }, tooltip: tooltipCfg } }
  });

  const mc = document.getElementById('monthlyChart');
  if (mc) new Chart(mc, {
    type: 'bar',
    data: { labels: <?= json_encode($monthly['labels'] ?? []) ?>,
      datasets: [{ data: <?= json_encode($monthly['values'] ?? []) ?>, backgroundColor: ORANGE,
        hoverBackgroundColor: '#c96f3f', borderRadius: 6, borderSkipped: false, maxBarThickness: 26 }] },
    options: { responsive: true, maintainAspectRatio: false,
      scales: { x: { ticks: { color: txt, font: { size: 9 }, maxRotation: 0, autoSkip: true }, grid: { display: false }, border: { color: grid } },
        y: { min: 0, max: 10, ticks: { stepSize: 2, color: txt, font: { size: 10 } }, grid: { color: grid } } },
      plugins: { legend: { display: false }, tooltip: tooltipCfg } }
  });
});
</script>
