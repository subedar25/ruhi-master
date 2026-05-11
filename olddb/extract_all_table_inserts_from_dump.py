#!/usr/bin/env python3
"""
Split olddb/ruhicreation.sql into one .sql file per **app-imported** table only.

- Tables are limited to the same set as olddb/import_all_inserts.sh (Collet/Kundan data bundle).
- Each INSERT is rewritten from legacy dump names (e.g. `k_stone`) to Laravel DB names with **r_**
  prefix (e.g. `r_k_stone`).

Usage:
  python3 olddb/extract_all_table_inserts_from_dump.py
  python3 olddb/extract_all_table_inserts_from_dump.py --dump olddb/ruhicreation.sql --out olddb/table_inserts

INSERT blocks: line starts with INSERT INTO `name`; continuation until a line ends with ");"
(same heuristic as the older extract_inserts_from_ruhicreation.py).
"""
from __future__ import annotations

import argparse
import re
from collections import defaultdict
from pathlib import Path

INSERT_START = re.compile(r"^INSERT\s+INTO\s+`([^`]+)`", re.IGNORECASE)

# Dump table name -> target MySQL table (must match olddb/import_all_inserts.sh TABLES order / names).
IMPORT_TABLE_MAP: tuple[tuple[str, str], ...] = (
    ("item_type", "r_item_type"),
    ("design_category", "r_design_category"),
    ("kstone_color", "r_kstone_color"),
    ("kstone", "r_kstone"),
    ("product", "r_product"),
    ("design", "r_design"),
    ("design_products", "r_design_products"),
    ("collate_by_color", "r_collate_by_color"),
    ("design_product_item_kstone", "r_design_product_item_kstone"),
    ("k_stone", "r_k_stone"),
    ("kstone_add_history", "r_kstone_add_history"),
    ("kstone_history", "r_kstone_history"),
    ("gs", "r_gs"),
    ("slot", "r_slot"),
    ("gs_order_by_color", "r_gs_order_by_color"),
)

SOURCE_TO_TARGET: dict[str, str] = {src: tgt for src, tgt in IMPORT_TABLE_MAP}

# Output basename per target table; defaults to `{target}_inserts.sql`. Overrides must match
# olddb/import_all_inserts.sh FILES (e.g. kstone_add_history uses legacy filename without r_).
TARGET_TO_INSERT_FILENAME: dict[str, str] = {
    "r_kstone_add_history": "kstone_add_history_inserts.sql",
}

# Must match database/migrations/*create_r_collate_by_color_table.php column order.
_COLLATE_BY_COLOR_COLS = (
    "`id`, `design_product_id`, `color_id`, `only_red_qty`, `red_qty`, "
    "`green_qty`, `only_green_qty`, `white_qty`"
)


def safe_filename(table: str) -> str:
    t = re.sub(r"[^0-9A-Za-z_]+", "_", table)
    return f"{t}_inserts.sql"


def output_insert_filename(target: str) -> str:
    return TARGET_TO_INSERT_FILENAME.get(target, safe_filename(target))


def ensure_collate_explicit_columns(block: str, target: str) -> str:
    """
    Legacy dumps often use INSERT INTO t VALUES (...). Tie VALUES to our migration column list.
    """
    if target != "r_collate_by_color":
        return block
    pattern = re.compile(
        rf"(INSERT INTO `{re.escape(target)}`)\s+VALUES\s",
        re.IGNORECASE,
    )
    return pattern.sub(rf"\1 ({_COLLATE_BY_COLOR_COLS}) VALUES ", block, count=1)


def rewrite_insert_table(block: str, source: str, target: str) -> str:
    """Replace INSERT INTO `source` with INSERT INTO `target` on the first line only."""
    lines = block.splitlines(keepends=True)
    if not lines:
        return block
    first = lines[0]

    def _repl(m: re.Match[str]) -> str:
        return m.group(1) + target + m.group(2)

    first_new = re.sub(
        rf"(INSERT\s+INTO\s+`){re.escape(source)}(`)",
        _repl,
        first,
        count=1,
        flags=re.IGNORECASE,
    )
    return first_new + "".join(lines[1:])


def iter_insert_blocks(path: Path):
    """Yield (table_name, full_insert_sql) for each INSERT block in the dump."""
    with path.open(encoding="utf-8", errors="replace") as f:
        current_table: str | None = None
        buffer: list[str] = []

        for line in f:
            if current_table is None:
                m = INSERT_START.match(line)
                if m:
                    current_table = m.group(1)
                    buffer = [line]
                    if line.strip().endswith(");"):
                        yield current_table, "".join(buffer)
                        current_table = None
                        buffer = []
            else:
                buffer.append(line)
                if line.strip().endswith(");"):
                    yield current_table, "".join(buffer)
                    current_table = None
                    buffer = []


def main() -> None:
    ap = argparse.ArgumentParser(
        description="Extract INSERTs only for import-bundle tables; rewrite to r_* names.",
    )
    ap.add_argument(
        "--dump",
        type=Path,
        default=Path(__file__).resolve().parent / "ruhicreation.sql",
        help="Path to full SQL dump (default: olddb/ruhicreation.sql)",
    )
    ap.add_argument(
        "--out",
        type=Path,
        default=Path(__file__).resolve().parent / "table_inserts",
        help="Output directory (created if missing; same default as import_all_inserts.sh)",
    )
    args = ap.parse_args()

    if not args.dump.is_file():
        raise SystemExit(f"Dump not found: {args.dump}")

    args.out.mkdir(parents=True, exist_ok=True)

    # source_table -> list of rewritten INSERT blocks
    by_source: dict[str, list[str]] = defaultdict(list)
    counts: dict[str, int] = defaultdict(int)

    for source_table, block in iter_insert_blocks(args.dump):
        if source_table not in SOURCE_TO_TARGET:
            continue
        text = block.strip()
        if not text:
            continue
        target = SOURCE_TO_TARGET[source_table]
        rewritten = rewrite_insert_table(text, source_table, target)
        rewritten = ensure_collate_explicit_columns(rewritten, target)
        by_source[source_table].append(rewritten)
        counts[source_table] += 1

    if not by_source:
        raise SystemExit(
            "No INSERT blocks found for any import-bundle table. "
            f"Expected one of: {', '.join(SOURCE_TO_TARGET.keys())}",
        )

    # Write in import order; filenames align with import_all_inserts.sh FILES.
    written = 0
    for source, target in IMPORT_TABLE_MAP:
        blocks = by_source.get(source)
        if not blocks:
            print(f"WARNING: no INSERT INTO `{source}` in dump — skipping {target}")
            continue
        out_path = args.out / output_insert_filename(target)
        header = (
            f"-- INSERT statements for table `{target}` (from dump table `{source}`)\n"
            f"-- Source: {args.dump.name}\n"
            f"-- Blocks: {len(blocks)}\n\n"
        )
        body = "\n\n".join(blocks) + "\n"
        out_path.write_text(header + body, encoding="utf-8")
        written += 1
        print(f"OK {out_path.name:55s}  {len(blocks):5d} INSERT(s)  ({source} -> {target})")

    print(f"\nWrote {written} file(s) under {args.out} (import bundle only, r_ prefix applied).")


if __name__ == "__main__":
    main()
