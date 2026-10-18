<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Article;
use App\Models\EmergencyContact;

class ArticleController extends Controller
{
    public function index(): void
    {
        $keyword = trim($this->input('q', ''));
        $articles = $keyword !== '' ? Article::search($keyword) : Article::published();
        $categories = Article::categoriesWithCount();
        $tags = Article::popularTags();
        $recent = Article::recent(3);

        $pageTitle = 'Artikel Psikologi';
        $pageDescription = 'Pelajari kesehatan mental, self-care, dan terapi psikologis lewat artikel edukasi terpercaya dari NurtureForGrowth.';
        $pageType = 'website';
        $pageImage = absolute_asset('img/brand/nfg-logo.jpg');

        $this->view('article.index', compact('articles', 'categories', 'tags', 'recent', 'keyword', 'pageTitle', 'pageDescription', 'pageType', 'pageImage'), 'public');
    }

    public function show(array $params): void
    {
        $article = Article::bySlug($params['slug']);
        if (!$article) { http_response_code(404); require __DIR__.'/../Views/errors/404.php'; return; }
        Article::update($article['id'], ['views' => $article['views'] + 1]);
        $contacts = EmergencyContact::active();
        $categories = Article::categoriesWithCount();
        $tags = Article::popularTags();
        $recent = Article::recent(3, $article['id']);

        $pageTitle = $article['title'];
        $pageDescription = trim($article['excerpt'] ?? '') !== ''
            ? $article['excerpt']
            : substr(trim(strip_tags($article['content'] ?? '')), 0, 155);
        if (strlen($pageDescription) > 155) {
            $pageDescription = substr($pageDescription, 0, 152) . '...';
        }
        $pageType = 'article';
        $pageImage = !empty($article['thumbnail']) ? absolute_url($article['thumbnail']) : absolute_asset('img/brand/nfg-logo.jpg');

        $this->view('article.show', compact('article', 'contacts', 'categories', 'tags', 'recent', 'pageTitle', 'pageDescription', 'pageType', 'pageImage'), 'public');
    }
}
