# FleetOS — Production run-list (NEW v1 system)

What must run on the host for the mobile apps + panels to work correctly.

## First-boot bootstrap (fresh DB)

On the very first deploy, after configuring `.env` (DB creds + the `SEED_*` vars below):

```bash
php artisan migrate --force                                         # global tables
php artisan db:seed --class="Database\Seeders\ProductionSeeder" --force   # global defaults (idempotent)
php artisan fleet:shard-provision SY                                # migrate the country shard DB
php artisan fleet:shard-provision SY --seed                         # seed shard base catalog (services, doc types)
```

`ProductionSeeder` (global) creates: **all panel permissions + super-admin/office/employee roles**, **one super-admin login**, **currencies** (USD default), the **5 subscription plans**, sane **site settings** (dispatch/OTP/currency), and **one active country InfrastructureNode** whose shard DB creds default to the primary DB (single-DB deploy works out of the box). All seeders are idempotent (`firstOrCreate`) — safe to re-run.

Configure via `.env` before seeding:

| Var | Default | Purpose |
|---|---|---|
| `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD` | `admin@fleetos.app` / `ChangeMe!2026` | super-admin login — **change the password** |
| `SEED_COUNTRY_CODE` / `SEED_COUNTRY_NAME` | `SY` / `Syria` | first country node |
| `SEED_BILLING_MODE` | `commission` | `commission` (Syria) or `subscription` (USA) |
| `SEED_CURRENCY_CODE` / `SEED_CURRENCY_SYMBOL` | `USD` / `$` | node currency |
| `SEED_COUNTRY_LAT` / `SEED_COUNTRY_LNG` / `SEED_COUNTRY_CITY` | Damascus | map center for the shard |

For a second country (e.g. USA subscription mode) add its `InfrastructureNode` from the panel (Region billing page) or DB, then `fleet:shard-provision US [--seed]`.

> Per-shard seeding: `fleet:shard-provision <ISO2> --seed` runs `DatabaseSeeder` on that shard, which routes to `ShardSeeder` (base services + document types). Plain `db:seed` (global connection) routes to `ProductionSeeder`.

## Long-running processes (Supervisor)

Install [`supervisor/fleetos.conf`](supervisor/fleetos.conf) (edit the `{{APP_PATH}}` / `{{APP_USER}}` / `{{LOG_PATH}}` placeholders), then:

```bash
sudo mkdir -p /var/log/fleetos && sudo chown {{APP_USER}} /var/log/fleetos
sudo cp deploy/supervisor/fleetos.conf /etc/supervisor/conf.d/fleetos.conf
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start fleetos:*
```

| Process | Command | Why it's critical |
|---|---|---|
| Events relay | `fleet:events-relay --daemon` | drains the per-shard outbox → Redis `rt:` + push notifications. **No realtime/notifications without it.** |
| Dispatch tick | `fleet:dispatch-tick --daemon` | expires stale ride offers (TTL≈20s) + re-offers to the next driver. **Dispatch stalls without it.** |
| Scheduled activation | `fleet:activate-scheduled --daemon` | holds fare + dispatches scheduled bookings ~2h before pickup (`--lead=7200`). |
| Queue worker | `queue:work` | ShouldQueue jobs (dispatch/reminders/mail). |
| Realtime gateway | `node realtime-gateway/server.js` | the socket server the apps connect to (port `FLEET_RT_PORT`=6002); psubscribes Redis `rt:*`. |

Relay / tick / activation each iterate **every active country shard** internally — one instance covers all shards.

## Infrastructure

- **Redis** — required (`rt:` pubsub between relay ↔ gateway; recommended for cache/queue/OTP/presence). For best performance set `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`.
- **MySQL** — main DB + one DB per country shard.
- **Node 18+** — for the realtime gateway (`npm install` in `realtime-gateway/`).

## No Laravel scheduler needed

There are no `schedule:run` tasks; the periodic work runs as the daemons above. (If you prefer cron over daemons, the same commands run once without `--daemon`, but keep them as daemons — a 1-minute cron is too slow for 20-second offer TTLs.)

## On every deploy

```bash
php artisan fleet:shard-provision --all   # apply pending migrations to every shard (idempotent)
php artisan queue:restart                 # reload workers with new code
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

> Pending shard migrations from recent work: `driver_safety_events`, `driver_applications`, `add_busy_reason_to_driver_presence` — applied by `shard-provision --all`.

## Optional

- Panel live-order board uses Laravel broadcasting (`OrderBoardUpdated` on `panel-orders-*`), currently `BROADCAST_CONNECTION=log` (off). Set a real broadcaster (Redis/Reverb) to enable it — separate from the app `rt:` gateway.
- Legacy `./server.js` and `./socket-server/server.js` serve the OLD mobile flow only — not needed for the new system.
- Housekeeping: `media-library:clean` periodically.
