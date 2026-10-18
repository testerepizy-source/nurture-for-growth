<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\User;
use App\Models\Psychologist;
use App\Models\Psychiatrist;
use App\Models\ConcernTag;
use App\Models\Schedule;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\ProviderReview;

class ProviderProfileController extends Controller
{
    public function show(array $params): void
    {
        $userId = (int) $params['id'];
        $user = User::find($userId);
        if (!$user) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }

        $psychologist = Psychologist::byUserId($userId);
        $psychiatrist = Psychiatrist::byUserId($userId);
        $provider = $psychologist ?: $psychiatrist;
        if (!$provider) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }

        $roleLabel = $psychologist ? 'Psikolog' : 'Psikiater';
        $provider = array_merge($provider, ['name' => $user['name'], 'email' => $user['email'], 'phone' => $user['phone'], 'photo' => $user['photo']]);

        $tags = ConcernTag::tagsForProvider($userId);
        $schedules = Schedule::forProvider($userId);

        $stats = [
            'patients_handled'   => $psychologist
                ? Patient::count('assigned_psychologist_id = ?', [$userId])
                : Patient::count('assigned_psychiatrist_id = ?', [$userId]),
            'sessions_completed' => Consultation::count("provider_id = ? AND status = 'completed'", [$userId]),
        ];

        $allTags = ConcernTag::active();
        $providers = array_merge(Psychologist::allWithUser(), Psychiatrist::allWithUser());

        $reviewSummary = ProviderReview::summaryFor($userId);
        $reviews = ProviderReview::forProvider($userId);

        $pageTitle = $provider['name'] . ' · ' . $roleLabel;
        $pageDescription = trim($provider['bio'] ?? '') !== ''
            ? $provider['bio']
            : 'Temui ' . $provider['name'] . ', ' . $roleLabel . ' berpengalaman untuk layanan konseling psikologis yang empatik.';
        $pageType = 'profile';
        $pageImage = !empty($provider['photo']) ? absolute_url($provider['photo']) : absolute_asset('img/brand/nfg-logo.jpg');

        $this->view('provider.profile', compact('provider', 'roleLabel', 'tags', 'schedules', 'stats', 'allTags', 'providers', 'reviewSummary', 'reviews', 'pageTitle', 'pageDescription', 'pageType', 'pageImage'), 'public');
    }
}
