<?php
require __DIR__ . '/vendor/autoload.php';
session_start();

$credentialsPath = __DIR__ . '/credentials.json';
if (!file_exists($credentialsPath)) {
    echo "Missing credentials.json. Place your OAuth client JSON at: $credentialsPath";
    exit;
}

$client = new Google_Client();
$client->setAuthConfig($credentialsPath);
$client->setRedirectUri('http://localhost/nfg/scripts/google/oauth2callback.php');
$client->setAccessType('offline');
$client->setPrompt('consent');
$scopes = [
    Google_Service_Gmail::GMAIL_SEND,
    Google_Service_Calendar::CALENDAR_EVENTS
];
$client->setScopes($scopes);

$authUrl = $client->createAuthUrl();

echo "<h2>Google OAuth2 Authorization</h2>";
echo "<p>Click the link to authorize this application to access Gmail (send) and Calendar (create events).</p>";
echo "<a href=\"$authUrl\">Authorize with Google</a>";
