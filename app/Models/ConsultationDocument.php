<?php
namespace App\Models;
use Core\Model;

class ConsultationDocument extends Model
{
    protected static string $table = 'consultation_documents';

    /**
     * Dapatkan semua dokumen untuk konsultasi tertentu
     */
    public static function forConsultation(int $consultationId): array
    {
        return self::raw(
            "SELECT cd.*, u.name as uploader_name 
             FROM consultation_documents cd
             JOIN users u ON u.id = cd.uploaded_by 
             WHERE cd.consultation_id = ? 
             ORDER BY cd.created_at DESC",
            [$consultationId]
        );
    }

    /**
     * Dapatkan dokumen berdasarkan tipe
     */
    public static function byType(int $consultationId, string $type): ?array
    {
        return self::rawOne(
            "SELECT cd.*, u.name as uploader_name 
             FROM consultation_documents cd
             JOIN users u ON u.id = cd.uploaded_by 
             WHERE cd.consultation_id = ? AND cd.document_type = ?",
            [$consultationId, $type]
        );
    }

    /**
     * Dapatkan dokumen untuk ditampilkan pasien
     */
    public static function visibleToPatient(int $consultationId): array
    {
        return self::raw(
            "SELECT cd.*, u.name as uploader_name 
             FROM consultation_documents cd
             JOIN users u ON u.id = cd.uploaded_by 
             WHERE cd.consultation_id = ? AND cd.is_visible_to_patient = 1
             ORDER BY cd.created_at DESC",
            [$consultationId]
        );
    }

    /**
     * Hitung jumlah dokumen untuk konsultasi
     */
    public static function countForConsultation(int $consultationId): int
    {
        $result = self::rawOne(
            "SELECT COUNT(*) as total FROM consultation_documents WHERE consultation_id = ?",
            [$consultationId]
        );
        return $result['total'] ?? 0;
    }

    /**
     * Dapatkan dokumen berdasarkan ID
     */
    public static function findWithUploader(int $id): ?array
    {
        return self::rawOne(
            "SELECT cd.*, u.name as uploader_name 
             FROM consultation_documents cd
             JOIN users u ON u.id = cd.uploaded_by 
             WHERE cd.id = ?",
            [$id]
        );
    }
}
