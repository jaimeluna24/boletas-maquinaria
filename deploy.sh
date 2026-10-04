#!/bin/sh
set -e
git pull
docker compose -f compose.prod.yaml up -d --build
docker image prune -f
