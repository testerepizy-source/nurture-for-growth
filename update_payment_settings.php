<?php
require __DIR__ . '/core/autoload.php';
require __DIR__ . '/config/config.php';

use App\Models\Setting;

echo "Current Payment Settings:\n";
echo str_repeat("=", 50) . "\n";

$paymentKeys = [
    'payment_bank_name',
    'payment_account_holder',
    'payment_account_number',
    'payment_instructions'
];

foreach ($paymentKeys as $key) {
    $value = Setting::get($key);
    echo "{$key}: {$value}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Updating to new values...\n";

Setting::set('payment_bank_name', 'BCA');
Setting::set('payment_account_holder', 'PT Tumbuh Anak Bangsa');
Setting::set('payment_account_number', '3730062752');
Setting::set('payment_instructions', 'Transfer sesuai nominal tagihan. Bukti transfer wajib diunggah untuk verifikasi.');

echo "Updated Payment Settings:\n";
echo str_repeat("=", 50) . "\n";

foreach ($paymentKeys as $key) {
    $value = Setting::get($key);
    echo "{$key}: {$value}\n";
}

echo "\nDone!\n";
?>
