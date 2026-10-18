<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h3 class="fw-bold mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;">Nurture For Growth</h3>
    <p class="text-muted small mb-0">Jl. Tomang Utara No. 17, Jakarta Barat 11440</p>
  </div>
  <div class="text-end">
    <h5 class="fw-bold mb-0">RESEP</h5>
    <p class="text-muted small mb-0"><?= e($prescription['prescription_number']) ?></p>
    <p class="text-muted small mb-0"><?= format_date($prescription['created_at']) ?></p>
  </div>
</div>
<hr>
<div class="row mb-4">
  <div class="col-6">
    <p class="small text-muted mb-1">PASIEN</p>
    <p class="fw-bold mb-0"><?= e($prescription['patient_name']) ?></p>
    <p class="small text-muted mb-0">No. RM: <?= e($prescription['medical_record_number']) ?></p>
  </div>
  <div class="col-6 text-end">
    <p class="small text-muted mb-1">PSIKOLOG</p>
    <p class="fw-bold mb-0"><?= e($prescription['psychiatrist_name']) ?></p>
    <p class="small text-muted mb-0">STR: <?= e($prescription['str_number'] ?: '-') ?> &middot; SIP: <?= e($prescription['sip_number'] ?: '-') ?></p>
  </div>
</div>
<?php if ($prescription['diagnosis_note']): ?>
  <p class="small mb-3"><strong>Catatan:</strong> <?= e($prescription['diagnosis_note']) ?></p>
<?php endif; ?>
<table class="table">
  <thead><tr><th>Obat</th><th>Dosis</th><th>Frekuensi</th><th>Durasi</th><th>Instruksi</th></tr></thead>
  <tbody>
    <?php foreach ($items as $it): ?>
    <tr>
      <td><?= e($it['medication_name']) ?></td>
      <td><?= e($it['dosage'] ?: '-') ?></td>
      <td><?= e($it['frequency'] ?: '-') ?></td>
      <td><?= e($it['duration'] ?: '-') ?></td>
      <td><?= e($it['instructions'] ?: '-') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p class="small text-muted mt-4">Resep ini bersifat catatan medis internal klinik. Untuk pengambilan obat di apotek/farmasi, ikuti prosedur resep resmi yang berlaku di apotek terkait.</p>
