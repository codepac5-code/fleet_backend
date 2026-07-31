#!/usr/bin/env bash
# FleetOS — start ALL local realtime bridges (Windows / Git Bash).
# Usage:  bash scripts/dev-bridges.sh
# Stops:  bash scripts/dev-bridges.sh stop
#
# Brings up: Redis(6380) · gateway(6002) · backend(8000) · 5 daemons · adb reverse.

set -u
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

REDIS_SRV="/c/Program Files/Redis-7.4.1/redis-server"
REDIS_CLI="/c/Program Files/Redis-7.4.1/redis-cli"
ADB="/c/Users/Dell/AppData/Local/Android/Sdk/platform-tools/adb.exe"
LOG="$ROOT/storage/logs/bridges"
mkdir -p "$LOG"

if [ "${1:-}" = "stop" ]; then
  echo "Stopping bridges…"
  pkill -f "artisan fleet:" 2>/dev/null
  pkill -f "artisan queue:work" 2>/dev/null
  pkill -f "artisan serve" 2>/dev/null
  pkill -f "realtime-gateway/server.js" 2>/dev/null
  echo "Done (Redis left running)."
  exit 0
fi

# 1) Redis on 6380 (skip if already answering)
if "$REDIS_CLI" -p 6380 ping >/dev/null 2>&1; then
  echo "✓ Redis 6380 already up"
else
  echo "→ starting Redis 6380"
  ("$REDIS_SRV" --port 6380 --save "" --appendonly no >"$LOG/redis.log" 2>&1 &)
  sleep 1
fi

# 2) Gateway on 6002 (skip if the port answers)
if curl -s -o /dev/null "http://127.0.0.1:6002/socket.io/?EIO=4&transport=polling" 2>/dev/null; then
  echo "✓ gateway 6002 already up"
else
  echo "→ starting gateway 6002"
  ( cd "$ROOT/realtime-gateway" && \
    REDIS_HOST=127.0.0.1 REDIS_PORT=6380 REDIS_PASSWORD= FLEET_RT_PORT=6002 \
    FLEET_RT_PREFIX=rt: APP_URL=http://127.0.0.1:8000 FLEET_RT_CORS='*' \
    node server.js >"$LOG/gateway.log" 2>&1 & )
  sleep 1
fi

# 3) Backend on 0.0.0.0:8000 (LAN-reachable) — skip if answering
if curl -s -o /dev/null "http://127.0.0.1:8000/panel/login" 2>/dev/null; then
  echo "✓ backend 8000 already up"
else
  echo "→ starting backend 8000"
  ( php artisan serve --host=0.0.0.0 --port=8000 >"$LOG/backend.log" 2>&1 & )
  sleep 2
fi

# 4) Laravel daemons (the actual bridge is events-relay).
# Kill any previous ones first so re-running never stacks duplicate relays
# (a double relay double-publishes every event).
pkill -f "artisan fleet:" 2>/dev/null
pkill -f "artisan queue:work" 2>/dev/null
sleep 1
echo "→ starting daemons (relay · dispatch · scheduled · sla · queue)"
( php artisan fleet:events-relay     --daemon --sleep=2  >"$LOG/relay.log"     2>&1 & )
( php artisan fleet:dispatch-tick    --daemon --sleep=3  >"$LOG/dispatch.log"  2>&1 & )
( php artisan fleet:activate-scheduled --daemon --sleep=60 >"$LOG/scheduled.log" 2>&1 & )
( php artisan fleet:fixed-sla-sweep  --daemon --sleep=60 >"$LOG/sla.log"       2>&1 & )
( php artisan queue:work --queue=jobs,default --sleep=1 --tries=1 >"$LOG/queue.log" 2>&1 & )

# 5) adb reverse so a USB device reaches the laptop on 127.0.0.1
if "$ADB" get-state >/dev/null 2>&1; then
  "$ADB" reverse tcp:8000 tcp:8000 >/dev/null 2>&1
  "$ADB" reverse tcp:6002 tcp:6002 >/dev/null 2>&1
  echo "✓ adb reverse 8000+6002 set"
else
  echo "· no adb device (skip reverse — use LAN IP 192.168.1.104 instead)"
fi

sleep 2
echo
echo "=== health ==="
"$REDIS_CLI" -p 6380 ping | sed 's/^/redis6380: /'
curl -s -o /dev/null -w "gateway6002: %{http_code}\n" "http://127.0.0.1:6002/socket.io/?EIO=4&transport=polling"
curl -s -o /dev/null -w "backend8000: %{http_code}\n" "http://127.0.0.1:8000/panel/login"
echo "logs → $LOG"
