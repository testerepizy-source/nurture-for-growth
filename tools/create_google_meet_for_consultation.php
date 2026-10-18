<?php
// usage: php tools/create_google_meet_for_consultation.php 43
chdir(__DIR__ . '/../');
require_once __DIR__ . '/../core/autoload.php';

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use App\Services\GoogleMeetService;

if ($argc < 2) {
    echo "Usage: php tools/create_google_meet_for_consultation.php <consultation_id>\n";
    exit(1);
}

$id = (int) $argv[1];
$consultation = Consultation::find($id);
if (!$consultation) {
    echo "Consultation with id $id not found.\n";
    exit(1);
}

$patient = Patient::detailWithUser((int) $consultation['patient_id']);
$provider = User::find((int) $consultation['provider_id']);

if (!$patient || !$provider) {
    echo "Missing patient or provider data.\n";
    exit(1);
}

echo "Attempting to create Google Meet for consultation $id...\n";
$link = GoogleMeetService::createMeeting($consultation, $patient, $provider);

if (!$link) {
    echo "Google Meet could not be created. Check integration_configs google credentials and server cURL availability.\n";
    exit(2);
}

// Save meeting_link
$update = ['meeting_link' => $link];
$update = \App\Models\Consultation::filterColumns($update);
Consultation::update($id, $update);

echo "Success. Meeting link saved: $link\n";
exit(0);
