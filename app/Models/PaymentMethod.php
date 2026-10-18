<?php
namespace App\Models;
use Core\Model;

class PaymentMethod extends Model
{
    protected static string $table = 'payment_methods';

    public static function allActive(): array
    {
        try {
            return self::raw("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY name");
        } catch (\PDOException $e) {
            return self::defaultMethods();
        }
    }

    public static function getByCode(string $code): ?array
    {
        try {
            return self::rawOne("SELECT * FROM payment_methods WHERE code = ? AND is_active = 1", [$code]);
        } catch (\PDOException $e) {
            $methods = self::defaultMethods();
            foreach ($methods as $method) {
                if ($method['code'] === $code) {
                    return $method;
                }
            }
            return null;
        }
    }

    private static function defaultMethods(): array
    {
        return [
            ['code' => 'transfer', 'name' => 'Transfer Bank', 'description' => 'Bayar melalui transfer bank.', 'is_active' => 1, 'requires_verification' => 1],
            ['code' => 'card', 'name' => 'Kartu Kredit/Debit', 'description' => 'Bayar menggunakan kartu kredit atau debit.', 'is_active' => 1, 'requires_verification' => 0],
            ['code' => 'e_wallet', 'name' => 'E-Wallet', 'description' => 'Bayar menggunakan aplikasi e-wallet populer.', 'is_active' => 1, 'requires_verification' => 0],
            ['code' => 'midtrans', 'name' => 'Midtrans', 'description' => 'Bayar aman melalui bank, kartu, e-wallet, QRIS, dan metode lainnya.', 'is_active' => 1, 'requires_verification' => 0],
            ['code' => 'cash', 'name' => 'Tunai', 'description' => 'Bayar tunai di loket klinik.', 'is_active' => 1, 'requires_verification' => 0],
        ];
    }
}
