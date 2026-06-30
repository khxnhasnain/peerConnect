#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

CERT="storage/certs/cert.pem"
KEY="storage/certs/cert-key.pem"

if [[ ! -f "$CERT" || ! -f "$KEY" ]]; then
    echo "Missing TLS certificate files."
    echo "Generate them with:"
    echo "  mkdir -p storage/certs"
    echo "  mkcert -cert-file storage/certs/cert.pem -key-file storage/certs/cert-key.pem 192.168.1.41 localhost 127.0.0.1"
    exit 1
fi

if ! security find-certificate -c "mkcert" -a >/dev/null 2>&1; then
    echo ""
    echo "⚠️  mkcert is NOT installed in your Mac trust store yet."
    echo "    Run this once (you will be asked for your Mac password):"
    echo ""
    echo "      mkcert -install"
    echo ""
    echo "    Then restart your browser. Or open /dev/trust-cert for full instructions."
    echo ""
fi

CA_ROOT="$(mkcert -CAROOT)/rootCA.pem"
echo ""
echo "┌─────────────────────────────────────────────────────────────┐"
echo "│  HTTPS (camera works on phones) → port 8443               │"
echo "│  https://192.168.1.41:8443/meeting/YOUR_ROOM_CODE           │"
echo "├─────────────────────────────────────────────────────────────┤"
echo "│  HTTP (no camera on other devices) → port 8001             │"
echo "│  http://192.168.1.41:8001/meeting/YOUR_ROOM_CODE            │"
echo "├─────────────────────────────────────────────────────────────┤"
echo "│  ✗  https://192.168.1.41:8001  ← WRONG (no TLS on 8001)    │"
echo "└─────────────────────────────────────────────────────────────┘"
echo ""
echo "To trust the certificate on another device, install this CA root:"
echo "  $CA_ROOT"
echo "  iPhone: AirDrop/email the file → Settings → General → VPN & Device Management → install"
echo "  Android: Settings → Security → Install from storage"
echo ""

npm run serve:https
