<?php
require __DIR__ . '/vendor/autoload.php';

$credentialsPath = __DIR__ . '/credentials.json';
$tokenPath = __DIR__ . '/token.json';

if (!file_exists($credentialsPath) || !file_exists($tokenPath)) {
    echo "Missing credentials.json or token.json. Run auth.php first."; exit;
}

$client = new Google_Client();
$client->setAuthConfig($credentialsPath);
$client->setAccessType('offline');
$client->setPrompt('consent');
$client->setScopes([Google_Service_Gmail::GMAIL_SEND, Google_Service_Calendar::CALENDAR_EVENTS]);

$token = json_decode(file_get_contents($tokenPath), true);
$client->setAccessToken($token);

if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    } else {
        echo "Token expired and no refresh token available. Re-run auth flow."; exit;
    }
}

$calendarService = new Google_Service_Calendar($client);
$gmailService = new Google_Service_Gmail($client);

// ===== CONFIGURE EVENT + ATTENDEE =====
$summary = 'Konsultasi NFG';
$description = 'Sesi konsultasi via Google Meet';
$startDateTime = '2026-08-10T10:00:00+07:00';
$endDateTime = '2026-08-10T10:30:00+07:00';
$attendeeEmail = 'client@example.com'; // <-- change to recipient

$event = new Google_Service_Calendar_Event([
    'summary' => $summary,
    'description' => $description,
    'start' => ['dateTime' => $startDateTime],
    'end' => ['dateTime' => $endDateTime],
    'attendees' => [['email' => $attendeeEmail]],
    'conferenceData' => [
        'createRequest' => [
            'requestId' => 'nfg-' . time(),
            'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
        ]
    ]
]);

$created = $calendarService->events->insert('primary', $event, ['conferenceDataVersion' => 1]);
$conference = $created->getConferenceData();
$meetLink = null;
if ($conference && $conference->getEntryPoints()) {
    foreach ($conference->getEntryPoints() as $ep) {
        if ($ep->getEntryPointType() === 'video') {
            $meetLink = $ep->getUri();
            break;
        }
    }
}

if (!$meetLink) {
    echo "Failed to create Meet link."; exit;
}

// ===== CREATE EMAIL MESSAGE =====
$from = 'admin@nurtureforgrowth.com';
$to = $attendeeEmail;
$subject = 'Undangan Konsultasi — NFG';
$bodyHtml = "<p>Halo,</p><p>Silakan bergabung pada sesi konsultasi:</p><p><a href=\"$meetLink\">Join Google Meet</a></p>";

$rawMessageString = "From: NFG <$from>\r\n";
$rawMessageString .= "To: $to\r\n";
$rawMessageString .= "Subject: $subject\r\n";
$rawMessageString .= "MIME-Version: 1.0\r\n";
$rawMessageString .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$rawMessageString .= $bodyHtml;

$raw = rtrim(strtr(base64_encode($rawMessageString), '+/', '-_'), '=');

$msg = new Google_Service_Gmail_Message();
$msg->setRaw($raw);

$sent = $gmailService->users_messages->send('me', $msg);

echo "Event created: " . htmlspecialchars($created->getHtmlLink()) . "<br>";
echo "Meet link: " . htmlspecialchars($meetLink) . "<br>";
echo "Email sent: ID " . htmlspecialchars($sent->getId());
