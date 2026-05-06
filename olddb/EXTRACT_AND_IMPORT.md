# Extract and Re-import Old DB Data

Use this when `olddb/ruhicreation.sql` is updated and you want to:
- regenerate `olddb/table_inserts/*.sql` (only bundled tables), and
- import into DB after clearing old rows.

## 1) Extract table inserts from `ruhicreation.sql`

From project root:

```bash
python3 olddb/extract_inserts_from_ruhicreation.py
```

This script updates only files that already exist in `olddb/table_inserts/`.

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
python3 olddb/extract_inserts_from_ruhicreation.py && bash olddb/import_all_inserts.sh --fresh
```

