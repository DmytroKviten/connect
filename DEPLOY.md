# VPS deploy (git pull flow)

Steps tested for a typical Laravel + Vite/Vue stack on Ubuntu/Debian. Adapt paths/usernames to your server.

1) Prepare server once  
- Install system deps: `sudo apt update && sudo apt install -y nginx redis-server supervisor git unzip nodejs npm php php-cli php-fpm php-mbstring php-xml php-mysql php-curl php-zip`  
- Install composer: `curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer`  
- Install correct Node LTS (via nvm or distro) to match local `package.json`.

2) Clone / update code  
- `cd /var/www && git clone <repo-url> app` (first time) or `git pull` inside the repo on next deploys.

3) Environment  
- Copy env: `cp .env.example .env` then set DB, cache, app URL, queue, mail, JWT/token secrets.  
- Generate key: `php artisan key:generate`.

4) Install dependencies  
- PHP: `composer install --no-dev --optimize-autoloader`  
- JS: `npm ci`  
- Frontend build: `npm run build`

5) Database & cache  
- `php artisan migrate --force`  
- Optimize: `php artisan config:cache && php artisan route:cache && php artisan view:cache`

6) Permissions  
- `sudo chown -R www-data:www-data storage bootstrap/cache`  
- `sudo chmod -R ug+rw storage bootstrap/cache`

7) Web server (Nginx example)  
- Point the server block root to `public/` and ensure `index.php` is the fallback.  
- Reload: `sudo systemctl reload nginx`

8) Background workers (if queues/websockets)  
- Use Supervisor or systemd to run `php artisan queue:work --sleep=3 --tries=3`.

9) Recurring tasks  
- Cron: `* * * * * cd /var/www/app && php artisan schedule:run >> /dev/null 2>&1`

10) Smoke check  
- `php artisan test` (or your suite)  
- Hit `/login` and `/monitoring` behind auth to confirm chart data loads (API endpoints must be reachable).

Deployment tips  
- Keep `.env` out of git.  
- For zero-downtime, deploy to a new release dir and symlink `current -> release_x`.  
- If SSL is needed, put Nginx behind Certbot (`sudo certbot --nginx`).  
- If you change asset pipeline, always re-run `npm run build`.
