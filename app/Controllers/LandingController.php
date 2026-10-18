<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Article;
use App\Models\Faq;
use App\Models\Psychologist;
use App\Models\ConcernTag;
use App\Models\User;
use App\Models\Patient;
use App\Models\ProviderReview;

class LandingController extends Controller
{
    public function index(): void
    {
        $heroSlides = \App\Models\HeroSlide::active();
        $serviceVideos = \App\Models\ServiceVideo::active();
        $articles = array_slice(Article::published(), 0, 6);
        $faqs = Faq::published();
        $psychologists = array_map(fn($p) => $p + ['role_label' => 'Psikolog'], Psychologist::allWithUser());
        $providers = array_slice($psychologists, 0, 8);
        $tags = array_slice(ConcernTag::active(), 0, 6);

        $stats = [
            'providers' => count($psychologists),
            'patients'  => Patient::count(),
            'sessions'  => \App\Models\Consultation::count("status = 'completed'"),
            'max_experience' => (int) max(array_merge(
                array_column($psychologists, 'years_experience'),
                [0]
            )),
        ];
        $trustedAvatars = array_slice($providers, 0, 3);
        $reviewSummaries = ProviderReview::summaryForMany(array_column($providers, 'user_id'));
        $providerTags = [];
        foreach ($providers as $p) {
            $providerTags[(int) $p['user_id']] = array_slice(
                array_map(fn($t) => $t['name'], ConcernTag::tagsForProvider((int) $p['user_id'])),
                0,
                3
            );
        }
        $supportTeam = \App\Models\TeamMember::active();
        $siteReviews = \App\Models\SiteReview::published();
        $promos = \App\Models\ServicePromo::runningNow();

        $pageDescription = 'NurtureForGrowth mendampingi proses kesehatan mental Anda dengan konseling, asesmen, dan terapi psikologis berlisensi — online atau tatap muka.';
        $pageType = 'website';
        $pageImage = absolute_asset('img/brand/nfg-logo.jpg');

        $this->view('landing.index', compact('articles', 'faqs', 'providers', 'providerTags', 'tags', 'stats', 'trustedAvatars', 'reviewSummaries', 'supportTeam', 'heroSlides', 'serviceVideos', 'pageDescription', 'pageType', 'pageImage', 'siteReviews', 'promos'), 'public');
    }
}
