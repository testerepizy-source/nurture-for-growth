<?php
$currentLocale = locale();
$supportedLocales = \Core\Localization::getSupportedLocales();
?>

<div class="language-switcher dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <?= svg_icon('globe',16) ?>
        <span class="language-label">
            <?php if ($currentLocale === 'id'): ?>
                <span class="flag-icon">🇮🇩</span> Bahasa Indonesia
            <?php else: ?>
                <span class="flag-icon">🇬🇧</span> English
            <?php endif; ?>
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
        <?php foreach ($supportedLocales as $locale): ?>
            <li>
                <a class="dropdown-item <?= $currentLocale === $locale ? 'active' : '' ?>" href="<?= base_url('/language/' . $locale) ?>">
                    <?php if ($locale === 'id'): ?>
                        <span class="flag-icon me-2">🇮🇩</span> Bahasa Indonesia
                    <?php else: ?>
                        <span class="flag-icon me-2">🇬🇧</span> English
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<style>
.language-switcher {
    display: inline-block;
}

.language-switcher .flag-icon {
    font-size: 1.2em;
    margin-right: 0.25rem;
}

.language-switcher .dropdown-item {
    padding: 0.5rem 1rem;
}

.language-switcher .dropdown-item.active {
    background-color: #0e7c86;
    color: white;
}

.language-switcher .dropdown-item:hover {
    background-color: #f0f0f0;
}

.language-switcher .dropdown-item.active:hover {
    background-color: #0d6b75;
}
</style>
