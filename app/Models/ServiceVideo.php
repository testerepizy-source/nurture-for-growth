<?php
namespace App\Models;
use Core\Model;

class ServiceVideo extends Model
{
    protected static string $table = 'service_videos';

    public static function active(): array
    {
        return self::raw("SELECT * FROM service_videos WHERE is_active = 1 ORDER BY order_no ASC, id ASC");
    }

    public static function allOrdered(): array
    {
        return self::raw("SELECT * FROM service_videos ORDER BY order_no ASC, id ASC");
    }

    /** Ambil ID YouTube dari berbagai format URL (watch, youtu.be, shorts, embed). */
    public static function youtubeId(string $url): ?string
    {
        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,20})#i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function vimeoId(string $url): ?string
    {
        if (preg_match('#vimeo\.com/(?:video/)?(\d{6,12})#i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * URL embed untuk pemutar modal. Null jika video adalah file lokal (MP4)
     * yang sebaiknya diputar lewat tag <video>.
     */
    public static function embedUrl(array $video): ?string
    {
        $url = trim((string) ($video['video_url'] ?? ''));
        if ($url === '') return null;

        if ($yt = self::youtubeId($url)) {
            return 'https://www.youtube.com/embed/' . $yt . '?autoplay=1&rel=0';
        }
        if ($vimeo = self::vimeoId($url)) {
            return 'https://player.vimeo.com/video/' . $vimeo . '?autoplay=1';
        }
        // URL langsung ke file video (mp4/webm)
        if (preg_match('#^https?://.+\.(mp4|webm|ogg)(\?.*)?$#i', $url)) {
            return null;
        }
        return null;
    }

    /** True jika sumber video adalah file yang bisa diputar dengan <video>. */
    public static function isFileSource(array $video): bool
    {
        if (!empty($video['video_path'])) return true;
        $url = trim((string) ($video['video_url'] ?? ''));
        return (bool) preg_match('#^https?://.+\.(mp4|webm|ogg)(\?.*)?$#i', $url);
    }

    /** URL sumber untuk <video> (file unggahan atau URL langsung). */
    public static function fileSourceUrl(array $video): ?string
    {
        if (!empty($video['video_path'])) return base_url($video['video_path']);
        $url = trim((string) ($video['video_url'] ?? ''));
        return preg_match('#^https?://.+\.(mp4|webm|ogg)(\?.*)?$#i', $url) ? $url : null;
    }

    /** Thumbnail: unggahan > thumbnail otomatis YouTube > null. */
    public static function thumbUrl(array $video): ?string
    {
        if (!empty($video['thumbnail_path'])) return base_url($video['thumbnail_path']);
        $yt = self::youtubeId(trim((string) ($video['video_url'] ?? '')));
        if ($yt) return 'https://i.ytimg.com/vi/' . $yt . '/hqdefault.jpg';
        return null;
    }
}
