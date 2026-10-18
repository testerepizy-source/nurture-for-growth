<!-- <div class="page-breadcrumb">
  <div class="container">
    <span class="crumb">
      <span class="home-icon"><i class="fas fa-house"></i></span>
      <a href="<?= base_url('/') ?>" class="text-white">BERANDA</a>
      <span class="sep">/</span>
      <span class="current">KONTAK</span>
    </span>
  </div>
</div> -->

<section class="container py-5 my-3">
  <div class="row g-5">
    <!-- ============ KOLOM KIRI: INFO DARURAT & KONTAK ============ -->
    <div class="col-lg-4">
      <h3 class="section-title mb-3">Jika Dalam Keadaan Darurat</h3>
      <p class="section-sub mb-4">Jika Anda mengalami krisis kesehatan mental yang mengancam jiwa, segera hubungi <strong>119</strong> (layanan gawat darurat nasional) atau kunjungi UGD terdekat.</p>

      <div class="contact-info-item">
        <div class="contact-info-icon"><i class="fas fa-house-medical"></i></div>
        <div>
          <h6>Kunjungi Klinik Kami</h6>
          <p>Jl. Tomang Utara No. 17, Jakarta Barat 11440</p>
        </div>
      </div>
      <div class="contact-info-item">
        <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
        <div>
          <h6>Pertanyaan Umum</h6>
          <p>admin@nurtureforgrowth.com</p>
        </div>
      </div>
      <div class="contact-info-item">
        <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
        <div>
          <h6>Telepon</h6>
          <p>0857 1603 0407</p>
        </div>
      </div>

      <h5 class="fw-bold mt-4 mb-3" style="font-family:'Plus Jakarta Sans',sans-serif;">Jam Operasional</h5>
      <div class="hours-list mb-4">
        <div class="row-line"><span>Senin - Jumat</span><span>08.00 - 18.00</span></div>
        <div class="row-line"><span>Sabtu - Minggu</span><span>08.00 - 14.00</span></div>
        <div class="row-line"><span>Darurat</span><span>24/7</span></div>
      </div>

      <!-- <?php if (!empty($emergencyContacts)): ?>
      <div class="mb-4">
        <?php foreach ($emergencyContacts as $ec): ?>
          <p class="small text-muted mb-1"><strong><?= e($ec['name']) ?></strong>: <?= e($ec['phone']) ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?> -->

      <!-- <div class="d-flex gap-2">
        <a href="#" class="social-circle"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-circle"><i class="fab fa-x-twitter"></i></a>
        <a href="#" class="social-circle"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-circle"><i class="fab fa-whatsapp"></i></a>
      </div> -->
    </div>

    <!-- ============ KOLOM KANAN: FORM PERTANYAAN UMUM ============ -->
    <div class="col-lg-8">
      <span class="badge mb-3" style="background:#eef4ef;color:#00c79c;">Hubungi Kami</span>
      <h2 class="section-title mb-3">Ada Pertanyaan? <em class="accent-italic">Kami Siap Membantu</em></h2>
      <p class="section-sub mb-4">Untuk booking konsultasi langsung dengan sistem pencocokan cerdas kami, silakan <a href="<?= base_url('/register') ?>" style="color:#00c79c;font-weight:600">daftar akun</a> terlebih dahulu. Form di bawah ini untuk pertanyaan umum.</p>

      <form method="POST" action="<?= base_url('/contact') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="row g-3 mb-3">
          <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Nama Anda" required></div>
          <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Alamat Email" required></div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6"><input type="text" name="phone" class="form-control" placeholder="Nomor Telepon"></div>
          <div class="col-md-6">
            <select name="service_interest" class="form-select">
              <option value="">Layanan yang Diminati (opsional)</option>
              <?php foreach ($tags as $t): ?><option value="<?= e($t['name']) ?>"><?= e($t['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mb-4">
          <textarea name="message" rows="5" class="form-control" placeholder="Tulis pertanyaan atau pesan Anda" required></textarea>
        </div>
        <button type="submit" class="btn-hero" style="background:#00c79c;color:#004d3d;">
          Kirim Pesan <span class="icon-box" style="background:#fff;"><i class="fas fa-paper-plane"></i></span>
        </button>
      </form>
    </div>
  </div>
</section>

<!-- ============ GOOGLE MAPS ============ -->
<section class="container pb-5">
  <div class="contact-map-shell">
    <div class="contact-map-toolbar">
      <div>
        <span class="section-eyebrow mb-1">Lokasi Klinik</span>
        <strong>Jl. Tomang Utara No. 17, Jakarta Barat 11440</strong>
      </div>
      <!-- <a class="btn btn-sm btn-outline-primary" href="https://www.google.com/maps/search/?api=1&query=Jl.+Tomang+Utara+No.+17,+Jakarta+Barat+11440" target="_blank" rel="noopener"><i class="fas fa-diamond-turn-right me-1"></i>Buka di Google Maps</a> -->
    </div>
    <?php $googleMapsKey = (string) config_get('google_maps_api_key', ''); ?>
    <?php if ($googleMapsKey !== ''): ?>
      <div id="nfgGoogleMap" class="contact-map" data-lat="-6.1817" data-lng="106.7957" data-title="Nurture For Growth (NFG)"></div>
      <script>
        function initNfgMap() {
          const mapElement = document.getElementById('nfgGoogleMap');
          if (!mapElement || !window.google?.maps) return;
          const location = { lat: Number(mapElement.dataset.lat), lng: Number(mapElement.dataset.lng) };
          const map = new google.maps.Map(mapElement, {
            center: location,
            zoom: 16,
            mapTypeControl: true,
            streetViewControl: true,
            fullscreenControl: true,
            zoomControl: true,
            mapTypeId: 'roadmap',
          });
          const marker = new google.maps.Marker({ position: location, map, title: mapElement.dataset.title });
          const infoWindow = new google.maps.InfoWindow({ content: '<strong>Nurture For Growth (NFG)</strong><br>Jl. Tomang Utara No. 17, Jakarta Barat 11440' });
          marker.addListener('click', () => infoWindow.open({ anchor: marker, map }));
          infoWindow.open({ anchor: marker, map });
        }
      </script>
      <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= e($googleMapsKey) ?>&callback=initNfgMap"></script>
    <?php else: ?>
      <iframe class="contact-map" src="https://www.google.com/maps?q=Jl.+Tomang+Utara+No.+17,+Jakarta+Barat+11440&output=embed" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi Nurture For Growth (NFG)"></iframe>
      <!-- <div class="contact-map-fallback"><i class="fas fa-circle-info me-1"></i>Tambahkan <code>GOOGLE_MAPS_API_KEY</code> di file <code>.env</code> untuk mengaktifkan kontrol peta interaktif.</div> -->
    <?php endif; ?>
  </div>
</section>
