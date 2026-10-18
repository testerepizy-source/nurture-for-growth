<?php
$isProduction = env('MIDTRANS_IS_PRODUCTION', '0') === '1';
$snapJs = $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Aman</title>
</head>
<body style="font-family:system-ui,sans-serif;min-height:100vh;margin:0;background:#f5f7fa;color:#14213d">
    <main style="width:min(100%,720px);margin:0 auto;padding:2rem 1rem">
        <h1 style="margin-top:0;text-align:center">Pembayaran aman</h1>
        <p style="text-align:center">Total pembayaran: <strong>Rp <?= number_format((int)$amount, 0, ',', '.') ?></strong></p>
        <p id="paymentStatus" role="status" style="text-align:center;color:#667085">Menghubungkan ke Midtrans...</p>
        <a id="paymentLink" href="<?= e($redirectUrl) ?>" style="display:block;text-align:center;color:#147d64;font-weight:700">Buka pembayaran Midtrans</a>
    </main>
    <script>
        const paymentLink = document.getElementById('paymentLink');
        const paymentStatus = document.getElementById('paymentStatus');
        const redirectUrl = <?= json_encode($redirectUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        if (redirectUrl) {
            paymentLink.style.display = 'none';
            window.location.replace(redirectUrl);
        } else {
            paymentStatus.textContent = 'Tautan pembayaran tidak tersedia. Muat ulang halaman atau hubungi admin.';
            paymentStatus.style.color = '#b42318';
        }
    </script>
</body>
</html>
