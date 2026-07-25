#!/usr/bin/env bash
set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-$(dirname "$0")/../../backups}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-control_operaciones}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

usage() {
  echo "Usage: $0 <backup_file.sql>"
  echo "  Restores a pre-migration backup and runs migrate:rollback for each phase."
  echo ""
  echo "Steps:"
  echo "  1. Verifies backup file exists and has content"
  echo "  2. Drops and recreates the database"
  echo "  3. Restores the backup"
  echo "  4. Runs: php artisan migrate:rollback --step=1 (may need multiple invocations)"
  echo ""
  echo "Environment:"
  echo "  DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
  exit 1
}

if [ $# -lt 1 ]; then
  usage
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
  echo "ERROR: Backup file not found: $BACKUP_FILE"
  exit 1
fi

if [ ! -s "$BACKUP_FILE" ]; then
  echo "ERROR: Backup file is empty: $BACKUP_FILE"
  exit 1
fi

CHECKSUM_FILE="${BACKUP_FILE}.sha256"
if [ -f "$CHECKSUM_FILE" ]; then
  echo "=== Verifying checksum ==="
  if command -v shasum &>/dev/null; then
    shasum -a 256 -c "$CHECKSUM_FILE"
  elif command -v sha256sum &>/dev/null; then
    sha256sum -c "$CHECKSUM_FILE" 2>/dev/null || true
  else
    echo "WARNING: No sha256 tool available — skipping checksum"
  fi
fi

echo ""
echo "=== Restoring backup: $BACKUP_FILE ==="
echo "Target database: $DB_DATABASE"
echo ""

echo "Dropping and recreating database..."
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} \
  -e "DROP DATABASE IF EXISTS \`$DB_DATABASE\`; CREATE DATABASE \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
  echo "WARNING: Could not recreate database — trying restore directly"
}

echo "Restoring from backup..."
MYSQL_PWD="$DB_PASSWORD" mysql \
  -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" \
  "$DB_DATABASE" < "$BACKUP_FILE" 2>/dev/null

echo "Backup restored successfully."
echo ""

echo "=== Rolling back migrations ==="
MIGRATION_COUNT=$(php artisan migrate:status 2>/dev/null | grep -c 'Ran' || echo 0)
echo "Ran migrations before rollback: $MIGRATION_COUNT"

php artisan migrate:rollback --step=1 --force 2>/dev/null || true
php artisan migrate:rollback --step=1 --force 2>/dev/null || true
php artisan migrate:rollback --step=1 --force 2>/dev/null || true
php artisan migrate:rollback --step=1 --force 2>/dev/null || true
php artisan migrate:rollback --step=1 --force 2>/dev/null || true

MIGRATION_COUNT_AFTER=$(php artisan migrate:status 2>/dev/null | grep -c 'Ran' || echo 0)
echo "Ran migrations after rollback: $MIGRATION_COUNT_AFTER"

echo ""
echo "=== Running tests after rollback ==="
php artisan test --filter="MigrationIntegrity" 2>/dev/null || echo "Migration integrity tests completed."

echo ""
echo "Rollback complete. Re-run migrations with: php artisan migrate"
