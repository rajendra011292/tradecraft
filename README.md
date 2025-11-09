# Tradecraft — PHP 8.3 Auth + Plan (MVC)

Dev-ready skeleton with Composer autoload, Tailwind UI, CSRF, session/IP throttle, Argon2id, email verification & reset (dev mail logs), flash messages, middleware guards, .env config, and a robust Plan module (create, list, view, adjust, execute, cancel) with event logs and reason tracking — integrated into a secure welcome/dashboard.

## Stack
- PHP 8.3 (Laragon 2025 recommended)
- MySQL (Laragon default)
- Composer (autoload only — **no external packages required**)
- Tailwind via CDN (dev-friendly)

## Quick Start (Laragon + VS Code)

1. **Unzip** into `C:\laragon\www\Tradecraft` (folder name `Tradecraft`).
2. Open in **VS Code**.
3. Run in terminal (VS Code or Laragon terminal):

```bash
cd C:\laragon\www\Tradecraft
copy .env.example .env
composer dump-autoload
```

4. Create database in phpMyAdmin (**http://localhost/phpmyadmin**):
   - DB name: `Tradecraft`
   - Import `database/schema.sql`

5. Set a virtual host (Laragon Menu → Apache → sites-enabled) or use **Quick app > Add**:
   - Domain: `Tradecraft.test`
   - Document root: `C:/laragon/www/Tradecraft/public`

   Or edit `C:\laragon\etc\apache2\sites-enabled\Tradecraft.conf`:
   ```apache
   <VirtualHost *:80>
     ServerName Tradecraft.test
     DocumentRoot "C:/laragon/www/Tradecraft/public"
     <Directory "C:/laragon/www/Tradecraft/public">
       AllowOverride All
       Require all granted
     </Directory>
   </VirtualHost>
   ```
   Restart Apache.

6. Visit **http://Tradecraft.test**.
7. Register an account, then open `storage/mail/*.log` for the **verification link**.
8. Login and go to **Dashboard** and **Plans**.

## Security Notes
- Argon2id hashing via `password_hash()`.
- CSRF protection on all POST routes.
- Session/IP throttle (5 attempts / 60s) on login/register POST.
- Middleware guards protect `/app/*` routes.
- No external dependencies → easier deployment to shared/free hosting.

## Project Structure
```
app/
  Controllers/ (Auth, Dashboard, Plan)
  Core/ (App, Router)
  Middleware/ (Auth, Csrf, Throttle)
  Models/ (User, Plan, PlanEvent, Token)
  Support/ (Env, Session, DB, CSRF, Mailer, helpers.php)
  Views/ (layouts, auth, dashboard, plans)
public/
  index.php, .htaccess
database/
  schema.sql
storage/ (mail/logs/sessions/cache)
composer.json, .env.example
```

## Email in Dev
Emails are written to `storage/mail/*.log` with full verification/reset links.

## Production Tips
- Set `APP_DEBUG=false` in `.env`.
- Use real SMTP in `Mailer` (swap the file logger with `mail()` or PHPMailer).
- Enforce HTTPS and secure cookies at the web server.
- Configure proper DB credentials and a strong `CSRF_KEY`.


E. pre-trade Checklist
Market bias aligns? ✅

Setup confirmed? ✅

Risk/Reward > 2:1? ✅

Entry zone validated? ✅

Emotion < 6? ✅