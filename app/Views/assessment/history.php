<h4 class="fw-bold mb-3">Riwayat Assessment</h4>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle" id="historyTable">
      <thead><tr><th>Tanggal</th><th>Assessment</th><th>Skor</th><th>Kategori</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($history as $h): ?>
        <tr>
          <td><?= format_date($h['created_at'], 'd M Y H:i') ?></td>
          <td><?= e($h['assessment_title']) ?></td>
          <td><?= (int) $h['total_score'] ?></td>
          <td><span class="badge <?= $h['is_high_risk'] ? 'badge-risk-high' : 'badge-risk-low' ?>"><?= e($h['category_label'] ?? '-') ?></span></td>
          <td><a href="<?= base_url('/assessment/result/' . $h['id']) ?>" class="btn btn-sm btn-outline-primary">Lihat</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
