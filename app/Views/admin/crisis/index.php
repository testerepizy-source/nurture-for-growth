<h4 class="fw-bold mb-3">Crisis Events</h4>
<p class="text-muted small">Daftar kejadian yang ditandai sistem berdasarkan kata kunci/skor risiko tinggi. Gunakan data ini untuk tindak lanjut oleh tenaga profesional terkait sesuai kebijakan dan persetujuan yang berlaku.</p>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Waktu</th><th>Client</th><th>Sumber</th><th>Konteks</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($events as $e): ?>
        <tr>
          <td class="small"><?= format_date($e['created_at'], 'd M Y H:i') ?></td>
          <td><?= e($e['patient_name']) ?></td>
          <td><span class="badge bg-primary-soft text-primary text-uppercase"><?= e($e['source']) ?></span></td>
          <td class="small text-muted"><?= e($e['context_snippet']) ?></td>
          <td><span class="badge <?= $e['provider_notified'] ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $e['provider_notified'] ? 'Ditindaklanjuti' : 'Baru' ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($events)): ?><tr><td colspan="5" class="text-center text-muted py-3">Tidak ada crisis event.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
