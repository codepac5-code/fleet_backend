# FleetOS — aaPanel deploy & run sheet (staging.fleetapp.net)

Copy‑paste blocks for the aaPanel **Terminal**. Adjust the two variables at the
top, then run each section top to bottom.

```bash
# ── adjust these two, then paste the whole block once ──────────────
export SITE=/www/wwwroot/staging.fleetapp.net      # aaPanel site root of the Laravel app
export PHP=/www/server/php/82/bin/php              # php 8.2 binary path (check Panel → PHP version)
export COMPOSER="$PHP /usr/bin/composer"           # or: /www/server/php/82/bin/php /www/server/php/82/bin/composer
# ──────────────────────────────────────────────────────────────────
cd "$SITE"
```

---

## 1. Backend one‑time setup

```bash
cd "$SITE"

# dependencies (no dev packages on staging)
$COMPOSER install --no-dev --optimize-autoloader

# .env — create from example if missing, then edit in Panel → File manager
[ -f .env ] || cp .env.example .env
$PHP artisan key:generate

# storage symlink so uploaded media serves under /storage
$PHP artisan storage:link

# migrate the GLOBAL DB
$PHP artisan migrate --force

# The SY shard connects with the creds stored ON the SY node row (NOT .env).
# The seeded default is root/no-password (local only) — repoint it at a real
# MySQL user first, or provisioning fails with "Access denied for 'root'".
#   1) aaPanel → Databases → Add Database: name=fleet_sy, user=fleet_sy,
#      password=..., Access permission = All  (so fleet_sy@'%' works over TCP)
#   2) point the node at it:
$PHP artisan tinker --execute="App\Models\InfrastructureNode::where('country_code','SY')->update(['db_host'=>'127.0.0.1','db_port'=>3306,'db_name'=>'fleet_sy','db_user'=>'fleet_sy','db_pass'=>'YOUR_PASSWORD']);"

# now provision + migrate + seed the Syria shard
$PHP artisan fleet:shard-provision SY --seed

# cache config/routes/views for speed (re-run after any .env change)
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

# writable permissions (aaPanel runs php-fpm as user "www")
chown -R www:www "$SITE/storage" "$SITE/bootstrap/cache"
chmod -R 775 "$SITE/storage" "$SITE/bootstrap/cache"
```

### .env keys that must be set (File manager → .env)
```
APP_URL=https://staging.fleetapp.net          # critical: media URLs + gateway authorize
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fleet_global
DB_USERNAME=...
DB_PASSWORD=...

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=                                 # set if your Redis has auth

QUEUE_CONNECTION=database                        # then run the queue worker (section 4)
CACHE_STORE=redis                                # or file
SESSION_DRIVER=database
```

---

## 2. Redis (aaPanel → App Store → install "Redis")

```bash
redis-cli ping        # expect: PONG   (add -a <password> if AUTH is set)
```

---

## 3. Realtime gateway (Node, port 6002) — run under **PM2 Manager**

Install aaPanel plugin **"PM2 Manager"** (App Store). Then:

```bash
cd "$SITE/realtime-gateway"
npm install --omit=dev

# start with env inline (PM2 keeps it alive + restarts on boot)
FLEET_RT_PORT=6002 \
APP_URL=https://staging.fleetapp.net \
REDIS_HOST=127.0.0.1 REDIS_PORT=6379 REDIS_PASSWORD= \
FLEET_RT_CORS='*' \
pm2 start server.js --name fleet-gateway

pm2 save
pm2 startup      # run the line it prints, so PM2 survives a reboot
```

(Or add it in the PM2 Manager UI: project dir = `.../realtime-gateway`, startup file
= `server.js`, and set the same env vars.)

### Expose the gateway over HTTPS
The apps connect on the default Socket.IO path `/socket.io`. In aaPanel →
**Website → staging.fleetapp.net → Config (nginx)**, add inside `server { }`
**above** the Laravel `location /`:

```nginx
location /socket.io/ {
    proxy_pass http://127.0.0.1:6002;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_read_timeout 3600s;
}
```
Save → reload nginx. Now `SOCKET_URL=https://staging.fleetapp.net` works.

---

## 4. Background daemons — run under **Supervisor Manager**

Install aaPanel plugin **"Supervisor Manager"** (App Store), then add these as
programs (UI fields shown). All use the same run user **www**.

| Name | Run dir | Start command |
|------|---------|---------------|
| fleet-events-relay | `$SITE` | `/www/server/php/82/bin/php artisan fleet:events-relay --daemon` |
| fleet-dispatch-tick | `$SITE` | `/www/server/php/82/bin/php artisan fleet:dispatch-tick --daemon` |
| fleet-queue | `$SITE` | `/www/server/php/82/bin/php artisan queue:work --tries=3 --sleep=1` |

> `dispatch-tick` is **not** in the scheduler, so it must run here or dispatch
> never advances. `events-relay` also runs here for sub‑second delivery (the
> cron in section 5 is only the fallback).

CLI equivalent (if you prefer editing supervisor directly):
```bash
cat >/etc/supervisor/conf.d/fleet.conf <<EOF
[program:fleet-events-relay]
command=$PHP artisan fleet:events-relay --daemon
directory=$SITE
user=www
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stdout_logfile=$SITE/storage/logs/relay.log
redirect_stderr=true

[program:fleet-dispatch-tick]
command=$PHP artisan fleet:dispatch-tick --daemon
directory=$SITE
user=www
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stdout_logfile=$SITE/storage/logs/dispatch.log
redirect_stderr=true

[program:fleet-queue]
command=$PHP artisan queue:work --tries=3 --sleep=1
directory=$SITE
user=www
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stdout_logfile=$SITE/storage/logs/queue.log
redirect_stderr=true
EOF
supervisorctl reread
supervisorctl update
supervisorctl status
```

---

## 5. Cron — the safety net (aaPanel → **Cron** → Shell Script, every 1 minute)

Command:
```bash
/www/server/php/82/bin/php /www/wwwroot/staging.fleetapp.net/artisan schedule:run
```
This runs `fleet:events-relay`, `fleet:activate-scheduled`, `fleet:fixed-sla-sweep`
once a minute (see `routes/console.php`) — catch‑up only; the daemons above do the
real‑time work.

Raw crontab equivalent:
```
* * * * * /www/server/php/82/bin/php /www/wwwroot/staging.fleetapp.net/artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Verify

```bash
curl -s https://staging.fleetapp.net/up ; echo
curl -s https://staging.fleetapp.net/realtime/shard ; echo
pm2 status
supervisorctl status
tail -n 30 "$SITE/storage/logs/dispatch.log"
tail -n 30 "$SITE/storage/logs/relay.log"
```

---

## 7. Build the apps against staging (run on the dev machine, not aaPanel)

Fleet Ride (rider):
```bash
flutter build apk --release \
  --dart-define=LIVE=true \
  --dart-define=API_BASE_URL=https://staging.fleetapp.net/user \
  --dart-define=SOCKET_URL=https://staging.fleetapp.net \
  --dart-define=SOCKET_COUNTRY=SY
```

Fleet DriverX (driver):
```bash
flutter build apk --release \
  --dart-define=USE_MOCK=false \
  --dart-define=API_BASE_URL=https://staging.fleetapp.net \
  --dart-define=REALTIME_URL=https://staging.fleetapp.net \
  --dart-define=COUNTRY=SY
```

---

## Redeploy after a code push
```bash
cd "$SITE"
git pull                                  # or re-upload
$COMPOSER install --no-dev --optimize-autoloader
$PHP artisan migrate --force
$PHP artisan fleet:shard-provision SY     # if new shard migrations landed
$PHP artisan config:cache && $PHP artisan route:cache && $PHP artisan view:cache
supervisorctl restart all
pm2 restart fleet-gateway
```
