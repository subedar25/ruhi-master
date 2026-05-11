# Import old Ruhi data (`olddb/table_inserts`)

This describes how to load legacy SQL insert bundles into the application database used by Laravel.

## Prerequisites

1. **Database schema** — Run Laravel migrations so tables such as `r_item_type`, `r_kstone`, `r_collate_by_color`, etc. exist:

   ```bash
   php artisan migrate
   ```

2. **Insert files** — Place or generate files under `olddb/table_inserts/` (see “Regenerating inserts from a dump” below).

3. **MySQL client** — Either the `mysql` CLI on your machine, or Docker so the script can run `mysql` inside the `db` container (see `olddb/import_all_inserts.sh`).

## Connection settings (important)

The import script reads **`DB_DATABASE`**, **`DB_USERNAME`**, **`DB_HOST`**, and **`DB_PORT`** from the project **`.env`** (unless you pass arguments).

- **Laravel inside Docker** uses hostnames like `ruhi-master-db` and port `3306` (container network).
- **Running the import script on your Mac or Linux host** must talk to MySQL where it is published on the host. With this repo’s `docker-compose.yml`, the DB service maps **`3308:3306`**, so from the host you typically need:

  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3308`
  - Same `DB_DATABASE` and credentials as the database you open in TablePlus or that Laravel uses (for example `ruhi_data` / `ruhi_master` per `docker-compose.yml`).

If the script reports success but you see **no rows** in the app or in a GUI connected to Docker, you are almost certainly importing into a **different MySQL instance or port** than the one you are viewing. Fix `.env` for host-side imports or pass explicit arguments (next section).

## Commands

From the **repository root**:

```bash
bash olddb/import_all_inserts.sh
```

You will be prompted for the MySQL password (unless you rely entirely on defaults-extra-file / Docker; the script documents behavior in its header comments).

### Fresh import (truncate bundled tables first)

Use this when you need to re-run after partial imports or **duplicate key (ERROR 1062)** errors:

```bash
bash olddb/import_all_inserts.sh --fresh
```

`--fresh` truncates the same legacy tables the script imports into (in safe order, with foreign key checks disabled), then runs each insert file.

### Explicit database connection (recommended when `.env` is tuned for containers)

Example aligned with Docker Compose mapping on the host:

```bash
bash olddb/import_all_inserts.sh --fresh ruhi_data ruhi_master 127.0.0.1 3308
```

Adjust database name, user, host, and port to match your environment.

### Help

```bash
bash olddb/import_all_inserts.sh --help
```

## Regenerating inserts from `ruhicreation.sql`

If you have a full dump at `olddb/ruhicreation.sql`, regenerate the insert bundle into `olddb/table_inserts/`:

```bash
python3 olddb/extract_all_table_inserts_from_dump.py
```

Then run the import script as above.

## Troubleshooting

| Symptom | What to check |
|--------|----------------|
| **ERROR 1062** (duplicate key) | Re-run with `--fresh`, or truncate the affected tables, then import again. |
| **ERROR 1048** (column cannot be null) on `r_collate_by_color` | Legacy rows may contain `NULL` in quantity columns. Ensure migration `2026_05_03_000000_make_r_collate_by_color_qty_columns_nullable.php` has been applied, then re-import. |
| **Success but no visible data** | Confirm you query the same database, host, and **port** as the script (very often `127.0.0.1:3308` from the host vs `3306` inside Docker). |
| **Column / value mismatch** on `r_collate_by_color` | Regenerate with `extract_all_table_inserts_from_dump.py` or ensure `INSERT` columns match the Laravel migrations (explicit column list vs table column order). |

On failure, the import script prints a **MySQL** section with the server error line; use that as the source of truth.

## Insert bundle contents

The script processes these files in order (see `FILES` in `olddb/import_all_inserts.sh`):  
`r_item_type`, design/kstone/product/design pipeline tables, `r_collate_by_color`, k-stone history, `r_gs`, `r_slot`, `r_gs_order_by_color`, etc.

Keep this path when adding new tables to the bundle so parent rows import before dependent rows.
