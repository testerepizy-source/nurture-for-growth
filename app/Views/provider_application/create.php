<div class="page-breadcrumb">
  <div class="container">
    <span class="crumb">
      <span class="home-icon"><i class="fas fa-house"></i></span>
      <a href="<?= base_url('/') ?>" class="text-white">BERANDA</a>
      <span class="sep">/</span>
      <span class="current">DAFTAR SEBAGAI PSIKOLOG</span>
    </span>
  </div>
</div>

<section class="container py-5 my-3" style="max-width:720px">
  <span class="badge mb-3" style="background:#eef4ef;color:#00c79c;">Bergabung dengan Tim NFG</span>
  <h2 class="section-title mb-3">Daftar sebagai <em class="accent-italic">Psikolog</em></h2>
  <p class="section-sub mb-4">Isi formulir di bawah untuk mengajukan diri bergabung sebagai tenaga profesional NFG. Tim kami akan meninjau kualifikasi &amp; dokumen Anda, dan menghubungi melalui email yang terdaftar.</p>

  <form method="POST" action="<?= base_url('/join-us') ?>" enctype="multipart/form-data" class="card p-4">
    <?= \Core\Csrf::field() ?>
    <div class="row g-3 mb-3">
      <div class="col-md-6"><label class="form-label small">Nama Lengkap</label><input type="text" name="name" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label small">Email</label><input type="email" name="email" class="form-control" required></div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6"><label class="form-label small">Nomor Telepon</label><input type="text" name="phone" class="form-control"></div>
      <div class="col-md-6">
        <label class="form-label small">Mendaftar sebagai</label>
        <select name="role_type" class="form-select" required>
          <option value="psikolog">Psikolog</option>
        </select>
      </div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6"><label class="form-label small">No. STR</label><input type="text" name="str_number" class="form-control"></div>
      <div class="col-md-6"><label class="form-label small">No. SIPP/SIP</label><input type="text" name="license_number" class="form-control"></div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-8"><label class="form-label small">Spesialisasi</label><input type="text" name="specialization" class="form-control" placeholder="Misal: Psikologi Klinis Anak dan Keluarga"></div>
      <div class="col-md-4"><label class="form-label small">Tahun Pengalaman</label><input type="number" name="years_experience" class="form-control" min="0"></div>
    </div>
    <div class="mb-3">
      <label class="form-label small">Ringkasan Profesional / Bio</label>
      <textarea name="bio" class="form-control" rows="3"></textarea>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label small">Unggah CV <span class="text-muted">(PDF/JPG/PNG, maks 5MB)</span></label>
        <input type="file" name="cv_file" class="form-control" accept=".pdf,image/jpeg,image/png">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Unggah Sertifikat/STR <span class="text-muted">(PDF/JPG/PNG, maks 5MB)</span></label>
        <input type="file" name="certificate_file" class="form-control" accept=".pdf,image/jpeg,image/png">
      </div>
    </div>
    <button type="submit" class="btn-hero" style="background:#00c79c;color:#004d3d;">Kirim Aplikasi <span class="icon-box" style="background:#fff;"><i class="fas fa-paper-plane"></i></span></button>
  </form>
</section>
