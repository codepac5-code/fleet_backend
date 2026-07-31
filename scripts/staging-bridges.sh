#!/usr/bin/env bash
# FleetOS — start/restart ALL realtime bridges on staging (aaPanel, systemd).
# Run ON THE SERVER:  bash scripts/staging-bridges.sh   (or the systemctl lines below)
#
# On staging the bridges are systemd services (NOT artisan serve — nginx+php-fpm
# serve HTTP). Redis is its own service (:6379 w/ password). The scheduled sweeps
# (activate-scheduled, fixed-sla-sweep, subscriptions-sweep) run via the cron
# `schedule:run`, so only these four run as daemons:

SERVICES="fleet-gateway fleet-events-relay fleet-dispatch-tick fleet-queue"
SITE="/www/wwwroot/staging.fleetapp.net"
PHP="/www/server/php/82/bin/php"
CRON="* * * * * $PHP $SITE/artisan schedule:run >/dev/null 2>&1"

# Read Redis creds from the app .env so the health check authenticates.
RPORT="$(grep -E '^REDIS_PORT=' "$SITE/.env" 2>/dev/null | cut -d= -f2- | tr -d ' "'"'"'')"
RPASS="$(grep -E '^REDIS_PASSWORD=' "$SITE/.env" 2>/dev/null | cut -d= -f2- | tr -d ' "'"'"'')"
RPORT="${RPORT:-6379}"

echo "→ enabling + (re)starting: $SERVICES"
systemctl enable  $SERVICES 2>/dev/null
systemctl restart $SERVICES
sleep 2

echo
echo "=== status ==="
for s in $SERVICES; do
  printf "%-22s " "$s"
  systemctl is-active "$s"
done

# Install the cron safety net if it isn't there (activate-scheduled / sla / subs).
if crontab -l 2>/dev/null | grep -qF "artisan schedule:run"; then
  echo "cron: ✓ schedule:run present"
else
  ( crontab -l 2>/dev/null; echo "$CRON" ) | crontab -
  echo "cron: → installed schedule:run"
fi

echo
echo "=== quick health ==="
if [ -n "$RPASS" ]; then
  redis-cli -p "$RPORT" -a "$RPASS" ping 2>/dev/null | sed "s/^/redis$RPORT: /"
else
  redis-cli -p "$RPORT" ping 2>/dev/null | sed "s/^/redis$RPORT: /"
fi
printf "gateway6002: "; curl -s -o /dev/null -w "%{http_code}\n" "http://127.0.0.1:6002/socket.io/?EIO=4&transport=polling"
printf "listening on 6002: "; (ss -ltn 2>/dev/null | grep -q ':6002' && echo yes) || echo "NO — check: journalctl -u fleet-gateway -n 40 --no-pager"
