<?php
namespace App\Models;
use Core\Model;

class JournalAccessGrant extends Model
{
    protected static string $table = 'journal_access_grants';

    public static function hasAccess(int $journalId, int $userId): bool
    {
        $row = self::rawOne('SELECT id FROM journal_access_grants WHERE journal_id = ? AND granted_to_user_id = ?', [$journalId, $userId]);
        return (bool) $row;
    }
}
