<?php
namespace App\Models;
use Core\Model;

class JournalAttachment extends Model
{
    protected static string $table = 'journal_attachments';

    public static function forJournal(int $journalId): array
    {
        return self::raw('SELECT * FROM journal_attachments WHERE journal_id = ?', [$journalId]);
    }
}
