#!/usr/bin/env bash
# FleetOS — start/restart ALL realtime bridges on staging (aaPanel, systemd).
# Run ON THE SERVER:  bash scripts/staging-bridges.sh   (or the systemctl lines below)
#
# On staging the bridges are systemd services (NOT artisan serve — nginx+php-fpm
# serve HTTP). Redis is its own service (:6379 w/ password). The scheduled sweeps
# (activate-scheduled, fixed-sla-sweep, subscriptions-sweep) run via the cron
# `schedule:run`, so only these four run as daemons:

SERVICES="fleet-gateway fleet-events-relay fleet-dispatch-tick fleet-queue"

echo "→ enabling + (re)starting: $SERVICES"
systemctl enable  $SERVICES 2>/dev/null
systemctl restart $SERVICES

echo
echo "=== status ==="
for s in $SERVICES; do
  printf "%-22s " "$s"
  systemctl is-active "$s"
done

echo
echo "=== quick health ==="
redis-cli -p 6379 -a "$REDIS_PASSWORD" ping 2>/dev/null | sed 's/^/redis6379: /'
curl -s -o /dev/null -w "gateway6002: %{http_code}\n" "http://127.0.0.1:6002/socket.io/?EIO=4&transport=polling"
echo "cron safety net (activate-scheduled / sla / subscriptions):"
crontab -l 2>/dev/null | grep -i "schedule:run" || echo "  MISSING — add: * * * * * /www/server/php/82/bin/php /www/wwwroot/staging.fleetapp.net/artisan schedule:run >/dev/null 2>&1"
