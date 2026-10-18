<?php
$total = count($providers);
?>

<section class="directory-section">
  <div class="container">

    <nav class="directory-breadcrumb" aria-label="breadcrumb">
      <a href="<?= base_url('/') ?>">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <span>Tim Psikolog</span>
    </nav>

    <div class="directory-header reveal">
      <div class="section-eyebrow">Tim Psikolog NurtureForGrowth</div>
      <h1 class="section-title mb-2">Semua <em class="accent-italic">Psikolog</em></h1>
      <p class="directory-lead">Psikolog berlisensi yang siap mendampingi Anda — pilih berdasarkan keahlian dan jadwal yang
        paling sesuai.</p>
    </div>

    <div class="directory-controls reveal">
      <span class="directory-result-count" id="directory-result-count">Menampilkan <?= $total ?> psikolog</span>
      <div class="directory-search">
        <i class="fas fa-search"></i>
        <input type="search" id="directory-search" placeholder="Cari nama atau keahlian…" autocomplete="off">
      </div>
    </div>

    <div class="row g-4 mt-1" id="directory-grid">
      <?php foreach ($providers as $p):
        $rs = $reviewSummaries[$p['user_id']] ?? null;
        $ptags = $tags[(int) $p['user_id']] ?? [];
        $searchText = mb_strtolower($p['name'] . ' ' . $p['role_label'] . ' ' . implode(' ', $ptags));
      ?>
      <div class="col-6 col-md-4 col-xl-3" data-directory-card data-search="<?= e($searchText) ?>">
        <a href="<?= base_url('/providers/' . $p['user_id']) ?>" class="provider-card provider-card-hover">
          <div class="provider-card-photo">
            <?php if (!empty($p['photo'])): ?>
            <img src="<?= base_url($p['photo']) ?>" alt="<?= e($p['name']) ?>">
            <?php else: ?>
            <span class="avatar-circle"><?= e(mb_substr($p['name'], 0, 1)) ?></span>
            <?php endif; ?>
            <span class="provider-role-pill"><?= e($p['role_label']) ?></span>
          </div>
          <div class="provider-card-body">
            <h6><?= e($p['name']) ?></h6>
            <div class="provider-card-meta">
              <span><?= (int) ($p['years_experience'] ?? 0) ?>+ tahun pengalaman</span>
              <?php if ($rs && $rs['total'] > 0): ?>
              <span class="rating"><i class="fas fa-star"></i> <?= e((string) $rs['average']) ?></span>
              <?php endif; ?>
            </div>
            <?php if (!empty($ptags)): ?>
            <div class="directory-tags">
              <?php foreach (array_slice($ptags, 0, 3) as $t): ?>
              <span><?= e($t) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="directory-empty" id="directory-empty" hidden>
      <i class="fas fa-user-slash"></i>
      <h3>Tidak ditemukan</h3>
      <p>Belum ada psikolog yang cocok dengan kata kunci atau filter tersebut.</p>
    </div>

  </div>
</section>

<style>
  .directory-section {
    padding-block: calc(var(--section-space) + 14px);
    background: linear-gradient(180deg, #fff 0%, var(--sand-panel) 100%);
  }

  .directory-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--muted, #6b7d72);
    margin-bottom: 18px;
  }

  .directory-breadcrumb a {
    color: var(--teal-ink, #12341f);
    text-decoration: none;
  }

  .directory-breadcrumb a:hover {
    color: var(--brand, #00c79c);
  }

  .directory-breadcrumb i {
    font-size: 9px;
    color: #a9b8ae;
  }

  .directory-breadcrumb span {
    color: var(--brand, #00c79c);
    font-weight: 600;
  }

  .directory-header {
    max-width: 620px;
    margin-bottom: 28px;
  }

  .directory-lead {
    color: var(--muted, #5b6e63);
    font-size: 15px;
    line-height: 1.7;
    margin: 10px 0 0;
  }

  .directory-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 26px;
  }

  .directory-result-count {
    font-size: 13px;
    font-weight: 600;
    color: var(--muted, #5b6e63);
  }

  .directory-search {
    position: relative;
    flex: 1 1 240px;
    max-width: 320px;
  }

  .directory-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9db3a6;
    font-size: 14px;
  }

  .directory-search input {
    width: 100%;
    border: 1px solid #dbe5dd;
    border-radius: 999px;
    padding: 9px 16px 9px 38px;
    font-size: 14px;
    background: #fff;
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease;
  }

  .directory-search input:focus {
    border-color: var(--brand, #00c79c);
    box-shadow: 0 0 0 3px rgba(0, 199, 156, .14);
  }

  .provider-card .directory-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 10px;
  }

  .provider-card .directory-tags span {
    background: var(--sand-panel, #f4f4ee);
    border: 1px solid #e7ebd6;
    color: var(--teal-ink, #12341f);
    font-size: 11px;
    font-weight: 600;
    border-radius: 6px;
    padding: 3px 8px;
  }

  [data-directory-card][hidden] {
    display: none !important;
  }

  .directory-empty {
    text-align: center;
    padding: 56px 20px;
  }

  .directory-empty i {
    font-size: 34px;
    color: #c4d0c7;
    margin-bottom: 14px;
  }

  .directory-empty h3 {
    font-size: 18px;
    margin-bottom: 4px;
  }

  .directory-empty p {
    color: var(--muted, #5b6e63);
    font-size: 14px;
  }

  @media (max-width: 575px) {
    #directory-grid {
      --bs-gutter-x: 8px;
      --bs-gutter-y: 8px;
      margin-left: -8px;
      margin-right: -8px;
      padding-left: 8px;
      padding-right: 8px;
    }

    #directory-grid .provider-card-photo {
      aspect-ratio: 4/3;
    }

    #directory-grid .provider-card-body {
      padding: 12px 14px 16px;
    }

    #directory-grid .provider-card-body h6 {
      font-size: .92rem;
    }

    #directory-grid .provider-card-meta {
      flex-direction: row;
      flex-wrap: wrap;
      gap: 6px;
      font-size: .76rem;
    }

    #directory-grid .provider-card .directory-tags {
      gap: 4px;
      margin-top: 8px;
    }

    #directory-grid .provider-card .directory-tags span {
      font-size: .7rem;
      padding: 3px 7px;
    }
  }
</style>

<script>
  (function () {
    var cards = document.querySelectorAll('[data-directory-card]');
    var search = document.getElementById('directory-search');
    var empty = document.getElementById('directory-empty');
    var grid = document.getElementById('directory-grid');
    var count = document.getElementById('directory-result-count');
    var total = cards.length;

    function applyFilter() {
      var q = search ? search.value.trim().toLowerCase() : '';
      var shown = 0;
      cards.forEach(function (card) {
        var visible = !q || card.getAttribute('data-search').indexOf(q) !== -1;
        card.hidden = !visible;
        if (visible) shown++;
      });
      if (empty) empty.hidden = shown > 0;
      if (grid) grid.hidden = shown === 0;
      if (count) {
        count.textContent = shown === total
          ? 'Menampilkan ' + total + ' psikolog'
          : 'Menampilkan ' + shown + ' dari ' + total + ' psikolog';
      }
    }

    if (search) {
      search.addEventListener('input', applyFilter);
      search.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') e.preventDefault();
      });
    }
  })();
</script>