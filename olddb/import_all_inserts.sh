#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INSERT_DIR="$SCRIPT_DIR/table_inserts"
ENV_FILE="$SCRIPT_DIR/../.env"

read_env() {
  local key="$1"
  local value
  value="$(sed -n "s/^${key}=//p" "$ENV_FILE" | head -n 1)"
  value="${value%\"}"
  value="${value#\"}"
  value="${value%\'}"
  value="${value#\'}"
  printf '%s' "$value"
}

if [[ $# -eq 0 ]]; then
  if [[ ! -f "$ENV_FILE" ]]; then
    echo "Missing .env file: $ENV_FILE"
    echo "Usage: $0 [database_name db_user [db_host] [db_port]]"
    exit 1
  fi

  DB_NAME="$(read_env "DB_DATABASE")"
  DB_USER="$(read_env "DB_USERNAME")"
  DB_HOST="$(read_env "DB_HOST")"
  DB_PORT="$(read_env "DB_PORT")"

  [[ -z "$DB_NAME" ]] && DB_NAME="ruhi-data"
  [[ -z "$DB_USER" ]] && DB_USER="root"
  [[ -z "$DB_HOST" ]] && DB_HOST="127.0.0.1"
  [[ -z "$DB_PORT" ]] && DB_PORT="3306"
else
  if [[ $# -lt 2 ]]; then
    echo "Usage: $0 [database_name db_user [db_host] [db_port]]"
    echo "No args mode: read DB settings from .env"
    echo "Example: $0 ruhi-data root 127.0.0.1 3308"
    exit 1
  fi

  DB_NAME="$1"
  DB_USER="$2"
  DB_HOST="${3:-127.0.0.1}"
  DB_PORT="${4:-3306}"
fi

if [[ ! -d "$INSERT_DIR" ]]; then
  echo "Missing inserts directory: $INSERT_DIR"
  exit 1
fi

FILES=(
  "r_item_type_inserts.sql"
  "r_design_category_inserts.sql"
  "r_kstone_color_inserts.sql"
  "r_kstone_inserts.sql"
  "r_product_inserts.sql"
  "r_design_inserts.sql"
  "r_design_products_inserts.sql"
  "r_collate_by_color_inserts.sql"
  "r_design_product_item_kstone_inserts.sql"
  "r_k_stone_inserts.sql"
  "kstone_add_history_inserts.sql"
  "r_kstone_history_inserts.sql"
  "r_gs_inserts.sql"
  "r_slot_inserts.sql"
  "r_gs_order_by_color_inserts.sql"
)

echo "Importing inserts into '$DB_NAME' on $DB_HOST:$DB_PORT as '$DB_USER'"
echo "You will be prompted for the MySQL password."

run_mysql_import() {
  local sql_file="$1"
  local mysql_init_sql
  mysql_init_sql="SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''));"

  if command -v mysql >/dev/null 2>&1; then
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$MYSQL_PWD" "$DB_NAME" \
      --init-command="$mysql_init_sql" < "$sql_file"
    return
  fi

  if command -v docker >/dev/null 2>&1; then
    local compose_root
    compose_root="$(cd "$SCRIPT_DIR/.." && pwd)"
    if [[ -f "$compose_root/docker-compose.yml" ]]; then
      echo "Local mysql client not found, using docker compose db service..."
      docker compose -f "$compose_root/docker-compose.yml" exec -T db \
        mysql -u"$DB_USER" -p"$MYSQL_PWD" "$DB_NAME" \
        --init-command="$mysql_init_sql" < "$sql_file"
      return
    fi
  fi

  echo "Error: mysql client not found and docker fallback unavailable."
  echo "Install mysql client, or run this project with docker compose."
  exit 1
}

read -rsp "Enter MySQL password: " MYSQL_PWD
echo

FAILED_FILES=()
SUCCESS_COUNT=0

for file in "${FILES[@]}"; do
  path="$INSERT_DIR/$file"
  if [[ ! -f "$path" ]]; then
    echo "Skipping missing file: $file"
    continue
  fi

  echo "Running: $file"
  if run_mysql_import "$path"; then
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
  else
    echo "Failed: $file"
    FAILED_FILES+=("$file")
  fi
done

echo
echo "Import completed. Success: $SUCCESS_COUNT / ${#FILES[@]}"
if [[ ${#FAILED_FILES[@]} -gt 0 ]]; then
  echo "Failed files:"
  for f in "${FAILED_FILES[@]}"; do
    echo " - $f"
  done
  exit 1
fi

echo "All table insert files imported successfully."

