<?php
require __DIR__ . '/vendor/autoload.php';
session_start();

$credentialsPath = __DIR__ . '/credentials.json';
$tokenPath = __DIR__ . '/token.json';

if (!file_exists($credentialsPath)) {
    echo "Missing credentials.json"; exit;
}

$client = new Google_Client();
$client->setAuthConfig($credentialsPath);
$client->setRedirectUri('http://localhost/nfg/scripts/google/oauth2callback.php');
$client->setAccessType('offline');
$client->setPrompt('consent');
$scopes = [Google_Service_Gmail::GMAIL_SEND, Google_Service_Calendar::CALENDAR_EVENTS];
$client->setScopes($scopes);

if (!isset($_GET['code'])) {
    echo "Authorization code not found."; exit;
}

$code = $_GET['code'];
$token = $client->fetchAccessTokenWithAuthCode($code);

if (array_key_exists('error', $token)) {
    echo 'Error fetching token: ' . htmlspecialchars(json_encode($token));
    exit;
}

file_put_contents($tokenPath, json_encode($token));

echo "<h3>Success</h3><p>Saved token to token.json. You may close this tab.</p>";
