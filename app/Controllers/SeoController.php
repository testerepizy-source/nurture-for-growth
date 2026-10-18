<?php

namespace App\Controllers;

use App\Models\Article;
use App\Models\Psychiatrist;
use App\Models\Psychologist;

class SeoController
{
    public function sitemap()
    {
        header('Content-Type: application/xml; charset=utf-8');
        echo $this->buildSitemap();
    }

    public function robots()
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo $this->buildRobots();
    }

    private function buildSitemap(): string
    {
        $baseUrl = rtrim(config_get('app_url', 'http://localhost'), '/');
        $pages = [
            '/',
            '/faq',
            '/contact',
            '/education',
            '/login',
            '/register',
            '/forgot-password',
            '/join-us',
        ];

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $xml .= $this->buildStaticUrls($baseUrl, $pages);
        $xml .= $this->buildArticleUrls($baseUrl);
        $xml .= $this->buildProviderUrls($baseUrl);

        return $xml . '</urlset>';
    }

    private function buildStaticUrls(string $baseUrl, array $pages): string
    {
        $xml = '';
        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . $page, ENT_QUOTES, 'UTF-8') . "</loc>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }
        return $xml;
    }

    private function buildArticleUrls(string $baseUrl): string
    {
        $xml = '';
        foreach (Article::published() as $article) {
            $slug = $article['slug'] ?? null;
            if (!$slug) {
                continue;
            }

            $updatedAt = $article['updated_at'] ?? $article['created_at'] ?? null;
            $lastmod = $updatedAt ? date('Y-m-d', strtotime($updatedAt)) : null;

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/education/' . $slug, ENT_QUOTES, 'UTF-8') . "</loc>\n";
            if ($lastmod) {
                $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            }
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.7</priority>\n";
            $xml .= "  </url>\n";
        }
        return $xml;
    }

    private function buildProviderUrls(string $baseUrl): string
    {
        $xml = '';
        $providers = array_merge(Psychologist::allWithUser(), Psychiatrist::allWithUser());

        foreach ($providers as $provider) {
            if (isset($provider['status']) && $provider['status'] !== 'active') {
                continue;
            }

            $id = $provider['user_id'] ?? $provider['id'] ?? null;
            if (!$id) {
                continue;
            }

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/providers/' . $id, ENT_QUOTES, 'UTF-8') . "</loc>\n";
            $xml .= "    <changefreq>monthly</changefreq>\n";
            $xml .= "    <priority>0.6</priority>\n";
            $xml .= "  </url>\n";
        }
        return $xml;
    }

    private function buildRobots(): string
    {
        $baseUrl = rtrim(config_get('app_url', 'http://localhost'), '/');
        $appEnv = strtolower(config_get('app_env', 'local'));

        if (in_array($appEnv, ['local', 'staging', 'testing'], true)) {
            return "User-agent: *\nDisallow: /\n";
        }

        return "User-agent: *\nDisallow: /admin\nDisallow: /super\nDisallow: /login\nDisallow: /register\nDisallow: /forgot-password\nDisallow: /dashboard\nDisallow: /profile\nDisallow: /journal\nDisallow: /assessment\nDisallow: /consultation\nDisallow: /patients\nDisallow: /payment\nSitemap: {$baseUrl}/sitemap.xml\n";
    }
}
