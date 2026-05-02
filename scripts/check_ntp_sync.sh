#!/usr/bin/env bash
#
# check_ntp_sync.sh — Verify NTP time synchronization on Docker host
#
# Run this on each Docker host (mock Odoo host AND RTS host) to
# diagnose clock drift issues between containers.
#
# Usage:  bash check_ntp_sync.sh

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

pass() { echo -e "${GREEN}[PASS]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
fail() { echo -e "${RED}[FAIL]${NC} $1"; }

echo "============================================="
echo " NTP Time Sync Check"
echo " Host: $(hostname)"
echo " Date: $(date -u +'%Y-%m-%d %H:%M:%S UTC')"
echo "============================================="
echo ""

# 1. Check timedatectl
echo "--- timedatectl ---"
if command -v timedatectl &>/dev/null; then
    TIME_OUTPUT=$(timedatectl 2>/dev/null)
    echo "$TIME_OUTPUT"
    echo ""

    if echo "$TIME_OUTPUT" | grep -q "System clock synchronized: yes"; then
        pass "System clock is synchronized"
    else
        fail "System clock is NOT synchronized — ENABLE NTP:"
        echo "       sudo timedatectl set-ntp true"
        echo ""
    fi
else
    warn "timedatectl not found (non-systemd host?)"
    echo ""
fi

# 2. Check if time sync daemon is running
echo "--- Time Sync Daemon ---"
DAEMON_FOUND=false

if pidof chronyd &>/dev/null; then
    pass "chronyd is running"
    DAEMON_FOUND=true
elif systemctl is-active --quiet chronyd 2>/dev/null; then
    pass "chronyd service is active"
    DAEMON_FOUND=true
fi

if pidof ntpd &>/dev/null; then
    pass "ntpd is running"
    DAEMON_FOUND=true
elif systemctl is-active --quiet ntp 2>/dev/null || systemctl is-active --quiet ntpd 2>/dev/null; then
    pass "ntp/ntpd service is active"
    DAEMON_FOUND=true
fi

if pidof systemd-timesyncd &>/dev/null; then
    pass "systemd-timesyncd is running"
    DAEMON_FOUND=true
fi

if [ "$DAEMON_FOUND" = false ]; then
    fail "No time sync daemon detected. Install and enable one:"
    echo "       # For Debian/Ubuntu:"
    echo "       sudo apt install -y chrony"
    echo "       sudo systemctl enable --now chronyd"
    echo ""
    echo "       # For RHEL/CentOS:"
    echo "       sudo yum install -y chrony"
    echo "       sudo systemctl enable --now chronyd"
    echo ""
fi
echo ""

# 3. Check chronyc tracking (if available)
echo "--- Chrony Tracking ---"
if command -v chronyc &>/dev/null; then
    TRACKING=$(chronyc tracking 2>/dev/null || true)
    if [ -n "$TRACKING" ]; then
        echo "$TRACKING" | head -10
        echo ""

        # Check if stratum > 0 (synced)
        if echo "$TRACKING" | grep -q "Stratum"; then
            STRATUM=$(echo "$TRACKING" | grep "Stratum" | awk '{print $3}')
            if [ "$STRATUM" -gt 0 ] 2>/dev/null; then
                pass "Chrony is synced to stratum $STRATUM source"
            fi
        fi

        # Check root dispersion
        if echo "$TRACKING" | grep -q "Root dispersion"; then
            DISP=$(echo "$TRACKING" | grep "Root dispersion" | awk '{print $4}')
            DISP_SECONDS=$(echo "$DISP" | sed 's/s$//')
            if (( $(echo "$DISP_SECONDS < 1" | bc -l 2>/dev/null || echo 0) )); then
                pass "Root dispersion is low ($DISP) — clock is accurate"
            elif [ -n "$DISP_SECONDS" ]; then
                warn "Root dispersion is $DISP — clock may be drifting"
            fi
        fi
    else
        warn "chronyc tracking returned no output"
    fi
else
    warn "chronyc not available"
fi

# 4. Check ntpq (if available)
if command -v ntpq &>/dev/null; then
    echo ""
    echo "--- ntpq peers ---"
    ntpq -p 2>/dev/null || warn "ntpq -p failed"
fi

echo ""
echo "--- Current UTC timestamp ---"
echo "Unix: $(date +%s)"
echo "RFC:  $(date -u +'%Y-%m-%dT%H:%M:%SZ')"
echo ""
echo "============================================="
echo " Run this script on BOTH hosts and compare the"
echo " 'Current UTC timestamp' values. They should"
echo " differ by no more than a few seconds."
echo "============================================="
