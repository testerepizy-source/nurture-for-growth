<!-- <div class="page-breadcrumb">
  <div class="container">
    <span class="crumb">
      <span class="home-icon"><i class="fas fa-house"></i></span>
      <a href="<?= base_url('/') ?>" class="text-white">BERANDA</a>
      <span class="sep">/</span>
      <span class="current">PROFIL <?= mb_strtoupper(e($roleLabel)) ?></span>
    </span>
  </div>
</div> -->

<section class="container py-5">
  <div class="row g-4">
    <!-- ============ SIDEBAR ============ -->
    <div class="col-lg-4">
      <div class="profile-sidebar">
        <div class="profile-sidebar-photo">
          <?php if (!empty($provider['photo'])): ?>
            <img src="<?= base_url($provider['photo']) ?>" alt="<?= e($provider['name']) ?>">
          <?php else: ?>
            <span class="avatar-circle"><?= e(mb_substr($provider['name'], 0, 1)) ?></span>
          <?php endif; ?>
        </div>
        <div class="profile-sidebar-body">
          <span class="small fw-bold" style="color:#00c79c;text-transform:uppercase;letter-spacing:.03em;"><?= e($roleLabel) ?></span>
          <h3 class="mb-2" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;"><?= e($provider['name']) ?></h3>
          <?php if ($reviewSummary['total'] > 0): ?>
            <div class="provider-rating mb-3">
              <span class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="fa<?= $i <= round($reviewSummary['average']) ? 's' : 'r' ?> fa-star"></i>
                <?php endfor; ?>
              </span>
              <span class="fw-bold small"><?= $reviewSummary['average'] ?></span>
              <span class="count">(<?= $reviewSummary['total'] ?> ulasan)</span>
            </div>
          <?php else: ?>
            <p class="small text-muted mb-3">Belum ada ulasan</p>
          <?php endif; ?>

          <div class="profile-info-row">
            <div class="icon"><i class="fas fa-phone"></i></div>
            <div><span class="label">Nomor Telepon</span><span class="value"><?= e($provider['phone'] ?: '-') ?></span></div>
          </div>
          <div class="profile-info-row">
            <div class="icon"><i class="fas fa-envelope"></i></div>
            <div><span class="label">Email</span><span class="value"><?= e($provider['email']) ?></span></div>
          </div>
          <div class="profile-info-row">
            <div class="icon"><i class="fas fa-briefcase-medical"></i></div>
            <div><span class="label">Pengalaman</span><span class="value"><?= (int) $provider['years_experience'] ?>+ Tahun</span></div>
          </div>

          <h6 class="fw-bold mt-4 mb-2 sidebar-block-title">Jadwal Praktik</h6>
          <?php
            $days = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
            $schModes = [
              'online'  => ['label' => 'Online', 'icon' => 'fa-video', 'class' => 'hours-mode-online'],
              'offline' => ['label' => 'Tatap Muka', 'icon' => 'fa-location-dot', 'class' => 'hours-mode-offline'],
              'both'    => ['label' => 'Online & Tatap Muka', 'icon' => 'fa-globe', 'class' => 'hours-mode-both'],
            ];
            $scheduleByDay = [];
            foreach ((array) $schedules as $sch) {
              if (empty($sch['is_active'])) continue;
              $scheduleByDay[(int) $sch['day_of_week']][] = $sch;
            }
            ksort($scheduleByDay);
            $slotMinutes = 0;
            foreach ($scheduleByDay as $dayRows) {
              foreach ($dayRows as $row) {
                  $slotMinutes = max($slotMinutes, (int) ($row['slot_duration_minutes'] ?? 0));
              }
            }
          ?>
          <?php if (empty($scheduleByDay)): ?>
            <div class="hours-card mb-3">
              <p class="hours-empty"><i class="fas fa-calendar-xmark"></i> Jadwal belum tersedia, silakan hubungi klinik.</p>
            </div>
          <?php else: ?>
            <div class="hours-card mb-3">
              <div class="hours-card-head">
                <span class="hours-legend">
                  <span class="hours-mode hours-mode-online"><i class="fas fa-video"></i> Online</span>
                  <span class="hours-mode hours-mode-offline"><i class="fas fa-location-dot"></i> Tatap Muka</span>
                </span>
                <span class="hours-card-count"><?= count($scheduleByDay) ?> hari aktif</span>
              </div>
              <ul class="hours-list">
                <?php foreach ($scheduleByDay as $dayIndex => $dayRows): ?>
                <li class="hours-row">
                  <span class="hours-day"><?= $days[$dayIndex] ?? 'Hari ' . $dayIndex ?></span>
                  <span class="hours-slots">
                    <?php foreach ($dayRows as $row):
                      $modeKey = $row['mode'] ?? 'both';
                      $mode = $schModes[$modeKey] ?? ['label' => ucfirst((string) $modeKey), 'icon' => 'fa-clock', 'class' => 'hours-mode-both'];
                    ?>
                    <span class="hours-slot">
                      <span class="hours-time"><?= substr((string) $row['start_time'], 0, 5) ?><span class="hours-dash">&ndash;</span><?= substr((string) $row['end_time'], 0, 5) ?></span>
                      <span class="hours-mode <?= $mode['class'] ?>"><i class="fas <?= $mode['icon'] ?>"></i> <?= $mode['label'] ?></span>
                    </span>
                    <?php endforeach; ?>
                  </span>
                </li>
                <?php endforeach; ?>
              </ul>
              <p class="hours-note">
                <i class="fas fa-circle-info"></i>
                Jadwal dapat berubah sewaktu-waktu<?= $slotMinutes ? ' &middot; durasi slot &plusmn;' . $slotMinutes . ' menit' : '' ?>. Konfirmasi waktu pasti dikirim setelah booking disetujui.
              </p>
            </div>
          <?php endif; ?>

          <div class="d-flex justify-content-center gap-3 mt-3 profile-social-row">
            <?php if (!empty($provider['facebook_url'])): ?><a href="<?= e($provider['facebook_url']) ?>" target="_blank" rel="noopener" class="social-circle"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
            <?php if (!empty($provider['twitter_url'])): ?><a href="<?= e($provider['twitter_url']) ?>" target="_blank" rel="noopener" class="social-circle"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
            <?php if (!empty($provider['linkedin_url'])): ?><a href="<?= e($provider['linkedin_url']) ?>" target="_blank" rel="noopener" class="social-circle"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
            <?php if (!empty($provider['instagram_url'])): ?><a href="<?= e($provider['instagram_url']) ?>" target="_blank" rel="noopener" class="social-circle"><i class="fab fa-instagram"></i></a><?php endif; ?>
            <?php if (!empty($provider['whatsapp_number'])): ?><a href="https://wa.me/<?= e(preg_replace('/\D/', '', $provider['whatsapp_number'])) ?>" target="_blank" rel="noopener" class="social-circle"><i class="fab fa-whatsapp"></i></a><?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ============ KONTEN UTAMA ============ -->
    <div class="col-lg-8">
      <h2 class="section-title mb-3">Ringkasan Profesional</h2>
      <p class="section-sub mb-4" style="font-size:1.02rem;line-height:1.7">
        <?= e($provider['bio'] ?: 'Belum ada ringkasan profesional yang ditambahkan.') ?>
      </p>

      <div class="row g-3 mb-5 stat-row">
        <div class="col-6 col-sm-4">
          <div class="stat-card text-center">
            <div class="stat-value"><?= (int) $provider['years_experience'] ?>+</div>
            <div class="stat-label">Tahun Pengalaman</div>
          </div>
        </div>
        <div class="col-6 col-sm-4">
          <div class="stat-card text-center">
            <div class="stat-value"><?= (int) $stats['sessions_completed'] ?></div>
            <div class="stat-label">Sesi Selesai</div>
          </div>
        </div>
        <div class="col-6 col-sm-4">
          <div class="stat-card text-center">
            <div class="stat-value"><?= (int) $stats['patients_handled'] ?></div>
            <div class="stat-label">Client Ditangani</div>
          </div>
        </div>
      </div>

      <h2 class="section-title mb-3">Bidang Spesialisasi</h2>
      <p class="section-sub mb-3">Area keluhan yang menjadi fokus penanganan <?= e($provider['name']) ?>:</p>
      <?php if (empty($tags)): ?>
        <p class="text-muted small">Belum ada tag spesialisasi yang ditentukan.</p>
      <?php else: ?>
        <div class="row mb-5">
          <div class="col-12">
            <ul class="specialization-list specialization-flow">
              <?php foreach ($tags as $t): ?>
                <li><i class="fas fa-circle-check"></i> <?= e($t['name']) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($reviews)): ?>
      <?php $reviewSlides = array_slice($reviews, 0, 12); ?>
      <h2 class="section-title mb-3">Ulasan Client</h2>
      <div class="position-relative mb-5">
        <div class="swiper provider-review-swiper">
          <div class="swiper-wrapper">
            <?php foreach ($reviewSlides as $r): ?>
              <div class="swiper-slide h-auto">
                <div class="card p-3 h-100 d-flex flex-column">
                  <div class="mb-1" style="color:#f68c12">
                    <?php for ($i = 1; $i <= 5; $i++): ?><i class="fa<?= $i <= $r['rating'] ? 's' : 'r' ?> fa-star" style="font-size:.8rem"></i><?php endfor; ?>
                  </div>
                  <?php if ($r['comment']): ?><p class="small mb-2 flex-grow-1">&ldquo;<?= e($r['comment']) ?>&rdquo;</p><?php endif; ?>
                  <p class="small text-muted mb-0 mt-auto">&mdash; <?= format_date($r['created_at']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="provider-review-pagination text-center mt-3"></div>
        </div>
        <!-- <button type="button" class="provider-review-nav prev" aria-label="Ulasan sebelumnya"><i class="fas fa-chevron-left"></i></button>
        <button type="button" class="provider-review-nav next" aria-label="Ulasan berikutnya"><i class="fas fa-chevron-right"></i></button> -->
      </div>

      <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
      <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
      <style>
        .provider-review-swiper { padding: 4px 6px 10px; overflow: visible; }
        .provider-review-swiper .swiper-slide { height: auto; display: flex; }
        .provider-review-swiper .card { width: 100%; border: 1px solid #e8ede9; box-shadow: 0 6px 18px rgba(15,32,48,.06); border-radius: 14px; }
        .provider-review-nav {
          position: absolute; top: 50%; transform: translateY(-50%); z-index: 5;
          width: 38px; height: 38px; border-radius: 50%; border: 0;
          background: #fff; color: #0f2030; box-shadow: 0 4px 14px rgba(15,32,48,.16);
          display: grid; place-items: center; cursor: pointer; transition: background .2s, color .2s;
        }
        .provider-review-nav:hover { background: #00c79c; color: #fff; }
        .provider-review-nav.prev { left: -12px; }
        .provider-review-nav.next { right: -12px; }
        .provider-review-pagination { position: static !important; }
        .provider-review-pagination .swiper-pagination-bullet { background: #9db3a6; opacity: 1; }
        .provider-review-pagination .swiper-pagination-bullet-active { background: #00c79c; width: 20px; border-radius: 999px; }
        @media (max-width: 767px) { .provider-review-nav { display: none; } }
      </style>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          if (typeof Swiper === 'undefined') return;
          new Swiper('.provider-review-swiper', {
            loop: <?= count($reviewSlides) >= 4 ? 'true' : 'false' ?>,
            autoplay: { delay: 3800, disableOnInteraction: false, pauseOnMouseEnter: true },
            speed: 700,
            spaceBetween: 16,
            slidesPerView: 1,
            grabCursor: true,
            pagination: { el: '.provider-review-pagination', clickable: true },
            navigation: { nextEl: '.provider-review-nav.next', prevEl: '.provider-review-nav.prev' },
            breakpoints: {
              768: { slidesPerView: 2, spaceBetween: 16 },
              992: { slidesPerView: 3, spaceBetween: 16 }
            }
          });
        });
      </script>
      <?php endif; ?>

      <!-- ============ CTA BOOKING ============ -->
      <div class="matching-cta-card">
        <span class="badge mb-3" style="background:#eef4ef;color:#00c79c;">Ajukan Sesi Konsultasi</span>
        <h2 class="section-title mb-3">Jadwalkan Konsultasi dengan <em class="accent-italic"><?= e($provider['name']) ?></em></h2>
        <p class="section-sub mb-4">Untuk booking sesi, silakan daftar atau masuk ke akun Anda terlebih dahulu. Setelah masuk, Anda bisa langsung memilih <?= e($provider['name']) ?> atau menggunakan sistem pencocokan cerdas kami untuk dicarikan waktu &amp; layanan yang paling sesuai.</p>
        <div class="d-flex flex-column flex-sm-row gap-3">
          <a href="<?= base_url('/register') ?>" class="btn-hero justify-content-center flex-fill">
            Daftar &amp; Booking <span class="icon-box"><i class="fas fa-arrow-right"></i></span>
          </a>
          <a href="<?= base_url('/login') ?>" class="btn btn-outline-price flex-fill d-flex align-items-center justify-content-center" style="border-color:#00c79c;color:#00c79c;">Sudah Punya Akun? Masuk</a>
        </div>
      </div>
    </div>
  </div>
</section>
