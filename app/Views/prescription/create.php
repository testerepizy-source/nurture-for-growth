<h4 class="fw-bold mb-1">Buat Resep Baru</h4>
<p class="text-muted small mb-4">Client: <strong><?= e($patient['name']) ?></strong> &middot; No. RM: <?= e($patient['medical_record_number']) ?></p>

<form method="POST" action="<?= base_url('/patients/' . $patient['id'] . '/prescription') ?>" class="card p-3">
  <?= \Core\Csrf::field() ?>
  <div class="mb-3">
    <label class="form-label small">Catatan Indikasi Umum <span class="text-muted">(opsional, bukan diagnosis final)</span></label>
    <input type="text" name="diagnosis_note" class="form-control">
  </div>

  <h6 class="fw-bold mb-2">Daftar Obat</h6>
  <div id="rxItems">
    <div class="row g-2 mb-2 rx-item">
      <div class="col-md-3"><input type="text" name="medication_name[]" class="form-control form-control-sm" placeholder="Nama Obat" required></div>
      <div class="col-md-2"><input type="text" name="dosage[]" class="form-control form-control-sm" placeholder="Dosis (mg)"></div>
      <div class="col-md-2"><input type="text" name="frequency[]" class="form-control form-control-sm" placeholder="Frekuensi"></div>
      <div class="col-md-2"><input type="text" name="duration[]" class="form-control form-control-sm" placeholder="Durasi"></div>
      <div class="col-md-3"><input type="text" name="instructions[]" class="form-control form-control-sm" placeholder="Instruksi"></div>
    </div>
  </div>
  <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="addRxItem()">+ Tambah Obat</button>
  <br>
  <button class="btn btn-primary" type="submit">Simpan Resep</button>
  <a href="<?= base_url('/patients/' . $patient['id']) ?>" class="btn btn-outline-secondary">Batal</a>
</form>

<script>
function addRxItem() {
  const wrap = document.getElementById('rxItems');
  const row = wrap.querySelector('.rx-item').cloneNode(true);
  row.querySelectorAll('input').forEach(i => i.value = '');
  wrap.appendChild(row);
}
</script>
