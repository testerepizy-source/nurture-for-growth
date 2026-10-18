<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Psychologist;
use App\Models\ConcernTag;
use App\Models\ProviderReview;

class ProviderDirectoryController extends Controller
{
    public function index(): void
    {
        $providers = array_map(fn($p) => $p + ['role_label' => 'Psikolog'], Psychologist::allWithUser());

        $reviewSummaries = ProviderReview::summaryForMany(array_column($providers, 'user_id'));

        $tags = array_fill_keys(array_column($providers, 'user_id'), []);
        foreach ($tags as $uid => $unused) {
            $tags[(int) $uid] = array_map(fn($t) => $t['name'], ConcernTag::tagsForProvider((int) $uid));
        }

        $pageTitle = 'Semua Psikolog';
        $pageDescription = 'Temui seluruh psikolog berlisensi NurtureForGrowth yang siap mendampingi proses kesehatan mental Anda.';
        $pageType = 'website';
        $pageImage = absolute_asset('img/brand/nfg-logo.jpg');

        $this->view('klinisi.index', compact('providers', 'reviewSummaries', 'tags', 'pageTitle', 'pageDescription', 'pageType', 'pageImage'), 'public');
    }
}