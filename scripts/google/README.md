# Google API Integration (Gmail + Calendar)

This folder contains helper scripts to:
- run OAuth2 authorization (one-time) to obtain `token.json`
- create a Google Calendar event with a Google Meet link
- send an email via Gmail API containing the Meet link

Important:
- Do NOT store or share account passwords here.
- Place your OAuth client credentials JSON (from Google Cloud) as `credentials.json` in this folder.
- Ensure the OAuth redirect URI includes: `http://localhost/nfg/scripts/google/oauth2callback.php` (or adjust below).

Setup
1. In Google Cloud Console enable **Gmail API** and **Google Calendar API**.
2. Create an OAuth 2.0 Client ID (Web application). Add redirect URI: `http://localhost/nfg/scripts/google/oauth2callback.php`.
3. Download the credentials JSON and save as `c:/xampp/htdocs/nfg/scripts/google/credentials.json`.
4. From project root run:

```bash
cd c:/xampp/htdocs/nfg/scripts/google
composer install
```

5. Start your local server (XAMPP already serves `http://localhost/nfg`) and open the auth URL:

- Visit: `http://localhost/nfg/scripts/google/auth.php`
- Sign in and grant scopes. The callback will save `token.json`.

Usage
- After `token.json` exists, edit `create_event_and_send_email.php` to set event date/time and recipient, then run it from browser or CLI:

```
php create_event_and_send_email.php
```

Files
- `auth.php` — starts OAuth flow
- `oauth2callback.php` — handles the OAuth callback and saves token
- `create_event_and_send_email.php` — creates a Calendar event + Meet link and sends email with the link
- `credentials.json` — (you must provide)
- `token.json` — created after first auth

Security
- Keep `credentials.json` and `token.json` out of version control.
- For production, store tokens in a secure store (env var, secrets manager).

If you prefer SMTP/App Password instead, I can provide a PHPMailer script instead.