#!/bin/bash
set -e

BACKUP_DIR="/app/storage/app/backups"
RETENTION_DAYS=7
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/covar_backup_$TIMESTAMP.sql.gz"

mkdir -p "$BACKUP_DIR"

echo "[$(date)] Starting database backup..."

mysqldump \
    --no-tablespaces \
    --single-transaction \
    --quick \
    --lock-tables=false \
    -h "$MYSQLHOST" \
    -P "${MYSQLPORT:-3306}" \
    -u "$MYSQLUSER" \
    -p"$MYSQLPASSWORD" \
    "$MYSQLDATABASE" \
    | gzip > "$BACKUP_FILE"

echo "[$(date)] Backup created: $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"

# Rotate: delete backups older than RETENTION_DAYS
find "$BACKUP_DIR" -name "covar_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "[$(date)] Cleaned up backups older than $RETENTION_DAYS days."
echo "[$(date)] Current backups:"
ls -lh "$BACKUP_DIR"
