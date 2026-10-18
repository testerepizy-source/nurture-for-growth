<?php
$currentLocale = locale();
$supportedLocales = \Core\Localization::getSupportedLocales();
?>
<div class="language-switcher-header">
  <?php foreach ($supportedLocales as $locale):
    $isActive = $currentLocale === $locale;
    $flag = $locale === 'id' ? '🇮🇩' : '🇬🇧';
    $label = $locale === 'id' ? 'ID' : 'EN';
  ?>
    <a class="language-button <?= $isActive ? 'active' : '' ?>" href="<?= base_url('/language/' . $locale) ?>" title="<?= $label ?>">
      <span><?= $flag ?></span>
    </a>
  <?php endforeach; ?>
</div>
