# Extract and Re-import Old DB Data

Use this when `olddb/ruhicreation.sql` is updated and you want to:

- regenerate `olddb/generated_table_inserts/*.sql` (only bundled tables, `INSERT INTO r_*`), and
- import into DB after clearing old rows.

## 1) Extract table inserts from `ruhicreation.sql`

From project root:

```bash
python3 olddb/extract_all_table_inserts_from_dump.py
```

This writes into **`olddb/generated_table_inserts/`** (same path `olddb/import_all_inserts.sh` reads). Override paths with `--dump` and `--out` if needed.

## 2) Import into DB after clearing old data

From project root:

```bash
bash olddb/import_all_inserts.sh --fresh
```

`--fresh` truncates bundled tables first, then imports all insert files.

## 3) Recommended explicit command (host machine + docker-mapped MySQL)

If your `.env` is not set for host access, run with explicit args:

```bash
bash olddb/import_all_inserts.sh --fresh ruhi_data ruhi_master 127.0.0.1 3308
```

Format:

```bash
bash olddb/import_all_inserts.sh --fresh <db_name> <db_user> <db_host> <db_port>
```

## Full one-time flow

```bash
python3 olddb/extract_all_table_inserts_from_dump.py && bash olddb/import_all_inserts.sh --fresh
```
