<!-- Language Switcher - Example Implementation Guide -->

<?php
/**
 * HOW TO USE TRANSLATIONS IN YOUR VIEWS
 * 
 * Example file showing various ways to implement multilingual support
 */

// Get current locale
$currentLang = locale();

// Check if string needs translation
$canTranslate = function_exists('trans');
?>

<div class="container py-5">
    <h1><?= __('app.welcome') ?></h1>

    <!-- Example 1: Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-5">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/') ?>">
                <?= __('app.title') ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/dashboard') ?>">
                            <?= __('nav.dashboard') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/consultation') ?>">
                            <?= __('nav.consultation') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/profile') ?>">
                            <?= __('nav.profile') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Example 2: Form -->
    <form method="POST" class="card p-4 mb-5">
        <h2><?= __('consultation.new') ?></h2>
        
        <div class="mb-3">
            <label class="form-label"><?= __('patient.name') ?></label>
            <input type="text" class="form-control" name="patient_name" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><?= __('consultation.duration') ?></label>
            <select class="form-select" name="duration">
                <option value="30">30 mins</option>
                <option value="60">60 mins</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><?= __('consultation.reason') ?></label>
            <textarea class="form-control" name="reason" rows="4"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <?= __('btn.submit') ?>
        </button>
        <a href="<?= base_url('/consultation') ?>" class="btn btn-secondary">
            <?= __('btn.cancel') ?>
        </a>
    </form>

    <!-- Example 3: Data Table -->
    <div class="card mb-5">
        <div class="card-header">
            <h3><?= __('consultation.list') ?></h3>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('label.date') ?></th>
                        <th><?= __('patient.name') ?></th>
                        <th><?= __('consultation.status') ?></th>
                        <th><?= __('label.action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>27-07-2026</td>
                        <td>Nur Hidayat</td>
                        <td>
                            <span class="badge bg-success">
                                <?= __('consultation.approved') ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info">
                                <?= __('btn.edit') ?>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Example 4: Alerts/Messages -->
    <div class="alert alert-info" role="alert">
        <strong><?= __('msg.info') ?></strong>
        <?= __('msg.loading') ?>
    </div>

    <!-- Example 5: Current Language Info -->
    <div class="card mt-5">
        <div class="card-body">
            <p><?= __('setting.language') ?>: <strong><?= ucfirst($currentLang) ?></strong></p>
            <p><?= __('app.tagline') ?></p>
        </div>
    </div>
</div>

<style>
    .navbar-brand {
        font-weight: bold;
        color: #0e7c86 !important;
    }
</style>
