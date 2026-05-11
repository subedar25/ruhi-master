#!/usr/bin/env bash
#
# Linux/macOS: the shell does not run scripts from the current directory by name.
# Use one of:
#   cd olddb && bash import_all_inserts.sh
#   cd olddb && chmod +x import_all_inserts.sh && ./import_all_inserts.sh
# From repo root:
#   bash olddb/import_all_inserts.sh
# Fresh import (truncate all bundled tables first — fixes ERROR 1062 duplicates on re-run):
#   bash olddb/import_all_inserts.sh --fresh
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INSERT_DIR="$SCRIPT_DIR/generated_table_inserts"
ENV_FILE="$SCRIPT_DIR/../.env"

FRESH=0
CLI_ARGS=()
while [[ $# -gt 0 ]]; do
  case "$1" in
    --fresh)
      FRESH=1
      shift
      ;;
    -h | --help)
      echo "Usage: $0 [--fresh] [database_name db_user [db_host] [db_port]]"
      echo "  --fresh   TRUNCATE all import target tables (reverse dependency order), then import."
      echo "            Use when re-running after a partial import (ERROR 1062 duplicate key)."
      echo "  No DB args: read DB_DATABASE, DB_USERNAME, DB_HOST, DB_PORT from ../.env"
      exit 0
      ;;
    *)
      CLI_ARGS+=("$1")
      shift
      ;;
  esac
done
# With `set -u`, empty `CLI_ARGS` + "${CLI_ARGS[@]}" errors on bash 3.2 (macOS).
if ((${#CLI_ARGS[@]} > 0)); then
  set -- "${CLI_ARGS[@]}"
else
  set --
fi

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
    echo "Usage: $0 [--fresh] [database_name db_user [db_host] [db_port]]"
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
    echo "Usage: $0 [--fresh] [database_name db_user [db_host] [db_port]]"
    echo "No args mode: read DB settings from .env"
    echo "Example: $0 --fresh ruhi-data root 127.0.0.1 3308"
    exit 1
  fi

  DB_NAME="$1"
  DB_USER="$2"
  DB_HOST="${3:-127.0.0.1}"
  DB_PORT="${4:-3306}"
fi

if [[ ! -d "$INSERT_DIR" ]]; then
  echo "Missing inserts directory: $INSERT_DIR"
  echo "Generate SQL files with: python3 \"$SCRIPT_DIR/extract_all_table_inserts_from_dump.py\""
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

# Target MySQL table per file (same index as FILES). Truncate uses reverse order.
TABLES=(
  "r_item_type"
  "r_design_category"
  "r_kstone_color"
  "r_kstone"
  "r_product"
  "r_design"
  "r_design_products"
  "r_collate_by_color"
  "r_design_product_item_kstone"
  "r_k_stone"
  "r_kstone_add_history"
  "r_kstone_history"
  "r_gs"
  "r_slot"
  "r_gs_order_by_color"
)

echo "Importing inserts into '$DB_NAME' on $DB_HOST:$DB_PORT as '$DB_USER'"
[[ "$FRESH" -eq 1 ]] && echo "Mode: --fresh (truncate import tables first)"
echo "You will be prompted for the MySQL password."

MYSQL_CNF=""
DOCKER_IMPORT_CNF="/tmp/.ruhi-import.cnf"

cleanup_cnf() {
  [[ -n "$MYSQL_CNF" && -f "$MYSQL_CNF" ]] && rm -f "$MYSQL_CNF"
}
trap cleanup_cnf EXIT

write_mysql_cnf() {
  MYSQL_CNF="$(mktemp)"
  chmod 600 "$MYSQL_CNF"
  cat >"$MYSQL_CNF" <<EOF
[client]
host=${DB_HOST}
port=${DB_PORT}
user=${DB_USER}
password=${MYSQL_PWD}
EOF
}

# After write_mysql_cnf: native mysql uses --defaults-extra-file; Docker copies the same file into the db container once.
sync_cnf_to_db_container() {
  local container="$1"
  docker cp "$MYSQL_CNF" "${container}:${DOCKER_IMPORT_CNF}" >/dev/null
}

# Host mysql client uses local MYSQL_CNF. Docker exec needs the same file inside the db container.
ensure_docker_cnf_planted() {
  command -v mysql >/dev/null 2>&1 && return 0
  local compose_root
  compose_root="$(cd "$SCRIPT_DIR/.." && pwd)"
  local compose_file="$compose_root/docker-compose.yml"
  if [[ -f "$compose_file" ]] && command -v docker >/dev/null 2>&1; then
    if docker compose version >/dev/null 2>&1; then
      sync_cnf_to_db_container "ruhi-master-db"
      return 0
    fi
    if command -v docker-compose >/dev/null 2>&1; then
      sync_cnf_to_db_container "ruhi-master-db"
      return 0
    fi
  fi
  local db_container="${MYSQL_DOCKER_CONTAINER:-ruhi-master-db}"
  if docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "$db_container"; then
    sync_cnf_to_db_container "$db_container"
  fi
  return 0
}

# Print mysql client stderr (saved to a temp file) and remove the file.
_report_mysql_stderr_and_rm() {
  local err_file="$1"
  if [[ -s "$err_file" ]]; then
    echo "---- MySQL ----" >&2
    cat "$err_file" >&2
  fi
  rm -f "$err_file"
}

run_mysql_import() {
  local sql_file="$1"
  local mysql_init_sql
  mysql_init_sql="SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''));"

  local err ec=0
  err="$(mktemp)"

  # Native mysql client (install: apt install mariadb-client / mysql-client)
  if command -v mysql >/dev/null 2>&1; then
    mysql --defaults-extra-file="$MYSQL_CNF" "$DB_NAME" \
      --init-command="$mysql_init_sql" <"$sql_file" 2>"$err" || ec=$?
    if [[ "$ec" -eq 0 ]]; then
      rm -f "$err"
      return 0
    fi
    _report_mysql_stderr_and_rm "$err"
    return "$ec"
  fi

  local compose_root
  compose_root="$(cd "$SCRIPT_DIR/.." && pwd)"
  local compose_file="$compose_root/docker-compose.yml"

  if [[ -f "$compose_file" ]] && command -v docker >/dev/null 2>&1; then
    if docker compose version >/dev/null 2>&1; then
      echo "Using docker compose exec db ..."
      docker compose -f "$compose_file" exec -T db \
        mysql --defaults-extra-file="$DOCKER_IMPORT_CNF" "$DB_NAME" \
        --init-command="$mysql_init_sql" <"$sql_file" 2>"$err" || ec=$?
      if [[ "$ec" -eq 0 ]]; then
        rm -f "$err"
        return 0
      fi
      _report_mysql_stderr_and_rm "$err"
      return "$ec"
    fi
    if command -v docker-compose >/dev/null 2>&1; then
      echo "Using docker-compose exec db ..."
      docker-compose -f "$compose_file" exec -T db \
        mysql --defaults-extra-file="$DOCKER_IMPORT_CNF" "$DB_NAME" \
        --init-command="$mysql_init_sql" <"$sql_file" 2>"$err" || ec=$?
      if [[ "$ec" -eq 0 ]]; then
        rm -f "$err"
        return 0
      fi
      _report_mysql_stderr_and_rm "$err"
      return "$ec"
    fi
    local db_container="${MYSQL_DOCKER_CONTAINER:-ruhi-master-db}"
    if docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "$db_container"; then
      echo "Using docker exec ${db_container} ..."
      docker exec -i "$db_container" \
        mysql --defaults-extra-file="$DOCKER_IMPORT_CNF" "$DB_NAME" \
        --init-command="$mysql_init_sql" <"$sql_file" 2>"$err" || ec=$?
      if [[ "$ec" -eq 0 ]]; then
        rm -f "$err"
        return 0
      fi
      _report_mysql_stderr_and_rm "$err"
      return "$ec"
    fi
  fi

  rm -f "$err"
  echo "Error: mysql client not found and no working Docker fallback."
  echo "Fix one of:"
  echo "  - apt install mariadb-client   (or mysql-client)"
  echo "  - Install Docker Compose v2 (docker compose plugin) or docker-compose v1"
  echo "  - Or set MYSQL_DOCKER_CONTAINER if your MySQL container name differs from ruhi-master-db"
  return 1
}

run_mysql_sql_pipe() {
  # stdin = SQL batch (used for TRUNCATE). Same transport as imports.
  local mysql_init_sql
  mysql_init_sql="SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''));"

  if command -v mysql >/dev/null 2>&1; then
    mysql --defaults-extra-file="$MYSQL_CNF" "$DB_NAME" --init-command="$mysql_init_sql"
    return
  fi

  local compose_root
  compose_root="$(cd "$SCRIPT_DIR/.." && pwd)"
  local compose_file="$compose_root/docker-compose.yml"

  if [[ -f "$compose_file" ]] && command -v docker >/dev/null 2>&1; then
    if docker compose version >/dev/null 2>&1; then
      docker compose -f "$compose_file" exec -T db \
        mysql --defaults-extra-file="$DOCKER_IMPORT_CNF" "$DB_NAME" --init-command="$mysql_init_sql"
      return
    fi
    if command -v docker-compose >/dev/null 2>&1; then
      docker-compose -f "$compose_file" exec -T db \
        mysql --defaults-extra-file="$DOCKER_IMPORT_CNF" "$DB_NAME" --init-command="$mysql_init_sql"
      return
    fi
    local db_container="${MYSQL_DOCKER_CONTAINER:-ruhi-master-db}"
    if docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "$db_container"; then
      docker exec -i "$db_container" \
        mysql --defaults-extra-file="$DOCKER_IMPORT_CNF" "$DB_NAME" --init-command="$mysql_init_sql"
      return
    fi
  fi
  return 1
}

truncate_import_tables() {
  local sql="SET FOREIGN_KEY_CHECKS=0;"
  local i
  for ((i = ${#TABLES[@]} - 1; i >= 0; i--)); do
    sql+=" TRUNCATE TABLE \`${TABLES[i]}\`;"
  done
  sql+=" SET FOREIGN_KEY_CHECKS=1;"
  printf '%s\n' "$sql" | run_mysql_sql_pipe
}

read -rsp "Enter MySQL password: " MYSQL_PWD
echo

write_mysql_cnf
ensure_docker_cnf_planted

if [[ "$FRESH" -eq 1 ]]; then
  echo "Truncating ${#TABLES[@]} tables (children first) ..."
  truncate_import_tables
  echo "Truncate done."
fi

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
