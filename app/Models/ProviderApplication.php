<?php
namespace App\Models;
use Core\Model;

class ProviderApplication extends Model
{
    protected static string $table = 'provider_applications';

    public static function pendingList(): array
    {
        return self::raw("SELECT * FROM provider_applications WHERE status = 'pending' ORDER BY created_at ASC");
    }

    public static function allList(): array
    {
        return self::raw("SELECT pa.*, u.name AS reviewer_name FROM provider_applications pa
                           LEFT JOIN users u ON u.id = pa.reviewed_by ORDER BY pa.created_at DESC");
    }
}
