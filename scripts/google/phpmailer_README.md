# PHPMailer SMTP (Gmail App Password)

This provides a simple PHP script to send emails via Gmail SMTP using an App Password.

When to use:
- You prefer SMTP (simpler setup) instead of OAuth2.
- Your Google Workspace account allows App Passwords (2-Step Verification must be enabled).

Steps
1. Enable 2-Step Verification for `admin@nurtureforgrowth.com` and create an App Password (Mail > Other). Copy the 16-character App Password.
2. In project folder run:

```bash
cd c:/xampp/htdocs/nfg/scripts/google
composer require phpmailer/phpmailer
```

3. Edit `send_via_smtp.php` and set the `SMTP_USERNAME` and `SMTP_APP_PASSWORD` environment variables or hardcode (not recommended).

4. Run the script from CLI or browser:

```bash
php send_via_smtp.php
```

Security note
- Do NOT commit App Passwords or real credentials to version control.
- Prefer storing credentials in environment variables or a secure store.

If you want, I can also add a helper to read creds from a `.env` file (and a `.gitignore` entry).