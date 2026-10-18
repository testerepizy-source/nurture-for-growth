<?php
namespace App\Models;

use App\Models\Psychiatrist;
use App\Models\Psychologist;
use Core\Model;

class Patient extends Model
{
    protected static string $table = 'patients';

    public static function byUserId(int $userId): ?array
    {
        return self::findBy('user_id', $userId);
    }

    /** Returns patient row joined with user info, for provider-facing lists. */
    public static function allWithUser(): array
    {
        return self::raw("SELECT p.*, u.name, u.email, u.phone, u.status
                           FROM patients p JOIN users u ON u.id = p.user_id
                           ORDER BY u.name ASC");
    }

    public static function assignedTo(int $providerId, string $role): array
    {
        $col = $role === 'psikiater' ? 'assigned_psychiatrist_id' : 'assigned_psychologist_id';
        return self::raw("SELECT DISTINCT p.*, u.name, u.email, u.phone
                           FROM patients p
                           JOIN users u ON u.id = p.user_id
                           LEFT JOIN consultations c ON c.patient_id = p.id
                             AND c.provider_id = ?
                             AND c.status IN ('pending', 'approved', 'completed', 'no_show')
                                                     WHERE p.$col = ? OR c.id IS NOT NULL
                           ORDER BY u.name ASC", [$providerId, $providerId]);
    }

    public static function assignToProvider(int $patientId, int $providerId): void
    {
        if (!$providerId || !$patientId) {
            return;
        }

        if (Psychologist::byUserId($providerId)) {
            self::update($patientId, ['assigned_psychologist_id' => $providerId]);
            return;
        }

        if (Psychiatrist::byUserId($providerId)) {
            self::update($patientId, ['assigned_psychiatrist_id' => $providerId]);
            return;
        }
    }

    public static function detailWithUser(int $patientId): ?array
    {
        $select = [
            'p.*',
            'u.name',
            'u.email',
            'u.phone',
        ];

        if (self::columnExists('whatsapp_number')) {
            $select[] = 'p.whatsapp_number';
        } elseif (User::columnExists('whatsapp_number')) {
            $select[] = 'u.whatsapp_number AS whatsapp_number';
        } else {
            $select[] = 'NULL AS whatsapp_number';
        }

        $select[] = 'u.gender';
        $select[] = 'u.birth_date';
        $select[] = 'u.address';
        $select[] = 'u.photo';

        return self::rawOne("SELECT " . implode(', ', $select) . "
                              FROM patients p JOIN users u ON u.id = p.user_id WHERE p.id = ?", [$patientId]);
    }
}
