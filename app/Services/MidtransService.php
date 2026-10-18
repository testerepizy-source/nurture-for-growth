<?php
namespace App\Services;

use RuntimeException;

class MidtransService
{
    public static function hostedRedirectUrl(string $token): string
    {
        $host = env('MIDTRANS_IS_PRODUCTION', '0') === '1' ? 'app.midtrans.com' : 'app.sandbox.midtrans.com';
        return 'https://' . $host . '/snap/v4/redirection/' . rawurlencode($token);
    }

    public static function isConfigured(): bool
    {
        return trim((string) env('MIDTRANS_SERVER_KEY', '')) !== ''
            && trim((string) env('MIDTRANS_CLIENT_KEY', '')) !== '';
    }

    public static function createSnapToken(array $transaction): array
    {
        $serverKey = trim((string) env('MIDTRANS_SERVER_KEY', ''));
        if ($serverKey === '' || trim((string) env('MIDTRANS_CLIENT_KEY', '')) === '') {
            throw new RuntimeException('Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di file .env.');
        }

        $orderId = (string)($transaction['order_id'] ?? '');
        $amount = (int)($transaction['gross_amount'] ?? 0);
        if ($orderId === '' || $amount < 1) {
            throw new RuntimeException('Data transaksi Midtrans tidak valid.');
        }

        $response = self::request('POST', '/snap/v1/transactions', [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => array_filter([
                'first_name' => $transaction['customer_name'] ?? null,
                'email' => $transaction['customer_email'] ?? null,
                'phone' => $transaction['customer_phone'] ?? null,
            ]),
            'item_details' => [[
                'id' => substr((string)($transaction['reference_type'] ?? 'payment'), 0, 50),
                'price' => $amount,
                'quantity' => 1,
                'name' => substr((string)($transaction['description'] ?? 'Pembayaran NFG'), 0, 50),
            ]],
            'callbacks' => [
                'finish' => (string)($transaction['finish_url'] ?? ''),
            ],
        ]);

        if (empty($response['token'])) {
            throw new RuntimeException('Midtrans tidak mengembalikan token pembayaran.');
        }

        return ['order_id' => $orderId, 'token' => (string)$response['token'], 'redirect_url' => (string)($response['redirect_url'] ?? '')];
    }

    public static function getStatus(string $orderId): array
    {
        return self::request('GET', '/v2/' . rawurlencode($orderId) . '/status');
    }

    public static function isValidNotification(array $notification): bool
    {
        $serverKey = trim((string) env('MIDTRANS_SERVER_KEY', ''));
        $expected = hash('sha512', ($notification['order_id'] ?? '') . ($notification['status_code'] ?? '') . ($notification['gross_amount'] ?? '') . $serverKey);
        return $serverKey !== '' && hash_equals($expected, (string)($notification['signature_key'] ?? ''));
    }

    private static function request(string $method, string $path, ?array $payload = null): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Ekstensi PHP cURL wajib diaktifkan untuk Midtrans.');
        }

        $baseUrl = env('MIDTRANS_IS_PRODUCTION', '0') === '1'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
        $curl = curl_init($baseUrl . $path);
        $headers = [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(trim((string) env('MIDTRANS_SERVER_KEY', '')) . ':'),
        ];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ];
        if ($payload !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_THROW_ON_ERROR);
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($body === false || $error !== '') {
            throw new RuntimeException('Tidak dapat terhubung ke Midtrans: ' . $error);
        }
        $response = json_decode($body, true);
        if ($status < 200 || $status >= 300 || !is_array($response)) {
            throw new RuntimeException('Midtrans menolak transaksi. Silakan coba lagi.');
        }
        return $response;
    }
}
