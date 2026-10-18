<?php
$__title = $pageTitle ?? 'Dokumen';
?>
<!DOCTYPE html>
<html lang="<?= locale() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($__title) ?> · <?= e(config_get('app_name', 'NurtureForGrowth')) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" href="<?= asset('img/brand/nfg-logo.jpg') ?>">
<style>
  :root{ --teal:#00C79C; --pink:#EC166C; --orange:#F68C12; --ink:#12241D; --ink-soft:#4E5D56; --sand-line:#EAE2D0; }
  body{ font-family:'Inter',sans-serif; background:#EDE7D8; color:var(--ink-soft); padding:40px 16px; }
  h1,h2,h3,h4,h5,h6{ font-family:'Plus Jakarta Sans',sans-serif; color:var(--ink); }
  .print-toolbar{ max-width:800px; margin:0 auto 16px; display:flex; justify-content:space-between; align-items:center; gap:12px; }
  .print-toolbar a{ text-decoration:none; color:var(--ink-soft); font-weight:600; font-size:.88rem; }
  .print-toolbar a:hover{ color:var(--ink); }
  .print-sheet{ max-width:800px; margin:0 auto; background:#fff; border-radius:14px; box-shadow:0 20px 50px -20px rgba(18,36,29,.25); padding:44px 48px; }
  .print-sheet table th{ font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#8A9791; border-bottom-width:1px; }
  .print-sheet hr{ border-color:var(--sand-line); }
  .btn-print{ background:var(--ink); color:#fff; border:none; border-radius:999px; padding:9px 20px; font-weight:700; font-size:.86rem; }
  .btn-print:hover{ background:#00443A; color:#fff; }
  @media print{
    body{ background:#fff; padding:0; }
    .print-toolbar{ display:none; }
    .print-sheet{ box-shadow:none; border-radius:0; padding:0; max-width:none; }
  }
</style>
</head>
<body>

<div class="print-toolbar">
  <a href="<?= base_url('/dashboard') ?>"><i class="fas fa-arrow-left me-1"></i> Kembali ke Dasbor</a>
  <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print me-2"></i>Print / Simpan sebagai PDF</button>
</div>

<div class="print-sheet">
<?= $content ?>
</div>

</body>
</html>
