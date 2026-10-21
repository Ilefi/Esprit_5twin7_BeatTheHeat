#!/bin/sh
# Smoke test of a running deployment: the health route and a few key pages must answer 200.
# Usage: docker/smoke-test.sh [base-url]   (default http://localhost:8080)
set -e

BASE_URL="${1:-http://localhost:8080}"

for path in /up / /produits /produits/huile-olive-sfax /tracabilite /login; do
    status=$(curl -s -o /dev/null -w '%{http_code}' "$BASE_URL$path")
    echo "$status $path"
    [ "$status" = "200" ] || { echo "Smoke test failed on $path"; exit 1; }
done

echo "Smoke test passed on $BASE_URL"
