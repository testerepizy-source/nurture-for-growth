<div class="py-4"></div>
<script>
Swal.fire({
  title: <?= $hasConsent ? "'Informed Consent sudah tersimpan'" : "'Sebelum melanjutkan konseling'" ?>,
  text: <?= $hasConsent ? "'Data kami menunjukkan bahwa kamu sudah mengisi Informed Consent Layanan Psikologi. Form ini cukup diisi satu kali.'" : "'Apakah kamu sudah pernah melakukan konseling atau mengisi form TTD (Informed Consent)? Form ini wajib untuk konseling pertama.'" ?>,
  icon: <?= $hasConsent ? "'info'" : "'question'" ?>,
  showCancelButton: true,
  confirmButtonText: 'Lanjut ke Booking',
  cancelButtonText: <?= $hasConsent ? "'Lihat Status Consent'" : "'Belum pernah'" ?>,
  allowOutsideClick: false,
  reverseButtons: true
}).then(function (result) {
  const query = new URLSearchParams();
  <?php if ($providerId): ?>query.set('provider_id', '<?= (int) $providerId ?>');<?php endif; ?>
  <?php if ($isMatched): ?>query.set('matched', '1');<?php endif; ?>
  if (result.isConfirmed) {
    query.set('consent_checked', '1');
    window.location.href = '<?= rtrim(base_url(), '/') ?>/consultation/create?' + query.toString();
    return;
  }
  window.location.href = '<?= rtrim(base_url(), '/') ?>/consultation/consent?' + query.toString();
});
</script>
