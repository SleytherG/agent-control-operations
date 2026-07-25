#!/usr/bin/env bash
set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-$(dirname "$0")/../../backups}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/pre_migration_${TIMESTAMP}.sql"
CHECKSUM_FILE="$BACKUP_FILE.sha256"

mkdir -p "$BACKUP_DIR"

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-control_operaciones}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

TABLES=(
  organizations users auth_sessions auth_refresh_tokens session_events
  audit_logs regions provinces districts
  stores banks bank_agents user_bank_agent_assignments
  operation_types operations
  daily_closures daily_closure_operations
  migrations
)

COUNT_SQL=""
for t in "${TABLES[@]}"; do
  COUNT_SQL+="SELECT '$t' AS table_name, COUNT(*) AS row_count FROM \`$t\` UNION ALL "
done
COUNT_SQL="${COUNT_SQL% UNION ALL }"

echo "=== Pre-migration backup ==="
echo "Timestamp: $TIMESTAMP"
echo "Database:  $DB_DATABASE"
echo ""

echo "=== Row counts (baseline) ==="
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_DATABASE" -e "$COUNT_SQL" 2>/dev/null || {
  echo "WARNING: Could not execute row-count query — check credentials/connectivity"
}

echo ""
echo "=== Dumping database ==="
MYSQL_PWD="$DB_PASSWORD" mysqldump \
  -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --add-drop-table \
  --complete-insert \
  --result-file="$BACKUP_FILE" \
  "$DB_DATABASE" 2>/dev/null

if [ ! -s "$BACKUP_FILE" ]; then
  echo "ERROR: Backup file is empty — aborting"
  exit 1
fi

shasum -a 256 "$BACKUP_FILE" > "$CHECKSUM_FILE"

echo "Backup:  $BACKUP_FILE"
echo "Checksum: $CHECKSUM_FILE"
echo "Size:    $(du -h "$BACKUP_FILE" | cut -f1)"
echo ""
echo "=== Integrity checks ==="
DUMP_TABLES=$(grep -c '^CREATE TABLE' "$BACKUP_FILE" 2>/dev/null || echo 0)
EXPECTED_TABLES=${#TABLES[@]}
echo "Tables in dump: $DUMP_TABLES (expected >= $((EXPECTED_TABLES - 1)))"
echo ""
echo "Backup complete."
