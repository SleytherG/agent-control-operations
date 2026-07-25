#!/bin/bash
# verify-seed-counts.sh — Verifies expected row counts after migrate:fresh --seed
# Feature: 010-migrate-postgresql-render
# Used by: T046 (rehearsal), T061 (cutover)

set -e

echo "=== Seed Count Verification ==="
echo ""

failures=0

check_count() {
    local table="$1"
    local expected_min="$2"
    local description="$3"

    local count
    count=$(php artisan tinker --execute="echo \App\Modules\IdentityAccess\Models\User::class; echo DB::table('$table')->count();" 2>/dev/null | tail -1 || echo "0")

    if [ "$count" -ge "$expected_min" ]; then
        echo "  ✓ $table: $count rows (min expected: $expected_min) — $description"
    else
        echo "  ✗ $table: $count rows (min expected: $expected_min) — $description [FAIL]"
        failures=$((failures + 1))
    fi
}

# Verify seeded tables
check_count "organizations" 1 "At least one organization (Red Principal)"
check_count "users" 2 "Admin + operator accounts"
check_count "agents" 3 "AG-CENTRO, AG-NORTE, AG-SUR"
check_count "user_agent_assignments" 1 "Operator assigned to at least one agent"
check_count "operation_types" 8 "8 operation types per organization"

# Tables expected to be empty (no data seeded yet)
for table in operations daily_closures daily_closure_operations auth_sessions auth_refresh_tokens session_events audit_logs password_resets; do
    echo "  - $table: (no seeds expected, row count not checked)"
done

echo ""
if [ "$failures" -eq 0 ]; then
    echo "=== All seed counts verified ==="
    exit 0
else
    echo "=== $failures verification(s) FAILED ==="
    exit 1
fi
