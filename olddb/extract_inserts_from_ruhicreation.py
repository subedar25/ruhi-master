#!/usr/bin/env python3
"""
Regenerate olddb/table_inserts/*.sql from olddb/ruhicreation.sql.
Only overwrites files listed in JOBS (existing import bundle).
"""
from __future__ import annotations

from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
DUMP_PATH = SCRIPT_DIR / "ruhicreation.sql"
INSERT_DIR = SCRIPT_DIR / "table_inserts"

# (source_table_in_dump, output_filename, target_table_for_app)
JOBS: list[tuple[str, str, str]] = [
    ("item_type", "r_item_type_inserts.sql", "r_item_type"),
    ("design_category", "r_design_category_inserts.sql", "r_design_category"),
    ("kstone_color", "r_kstone_color_inserts.sql", "r_kstone_color"),
    ("kstone", "r_kstone_inserts.sql", "r_kstone"),
    ("product", "r_product_inserts.sql", "r_product"),
    ("design", "r_design_inserts.sql", "r_design"),
    ("design_products", "r_design_products_inserts.sql", "r_design_products"),
    ("collate_by_color", "r_collate_by_color_inserts.sql", "r_collate_by_color"),
    ("design_product_item_kstone", "r_design_product_item_kstone_inserts.sql", "r_design_product_item_kstone"),
    ("k_stone", "r_k_stone_inserts.sql", "r_k_stone"),
    ("kstone_add_history", "kstone_add_history_inserts.sql", "r_kstone_add_history"),
    ("kstone_history", "r_kstone_history_inserts.sql", "r_kstone_history"),
    ("gs", "r_gs_inserts.sql", "r_gs"),
    ("slot", "r_slot_inserts.sql", "r_slot"),
    ("gs_order_by_color", "r_gs_order_by_color_inserts.sql", "r_gs_order_by_color"),
]


def extract_insert_blocks(lines: list[str], source_table: str) -> list[list[str]]:
    prefix = f"INSERT INTO `{source_table}`"
    chunks: list[list[str]] = []
    i = 0
    n = len(lines)
    while i < n:
        line = lines[i]
        if line.startswith(prefix):
            chunk = [line]
            i += 1
            while i < n:
                chunk.append(lines[i])
                if lines[i].strip().endswith(");"):
                    i += 1
                    break
                i += 1
            chunks.append(chunk)
            continue
        i += 1
    return chunks


def main() -> None:
    if not DUMP_PATH.is_file():
        raise SystemExit(f"Missing dump file: {DUMP_PATH}")

    text = DUMP_PATH.read_text(encoding="utf-8", errors="replace")
    lines = text.splitlines()

    for source, filename, target in JOBS:
        out_path = INSERT_DIR / filename
        if not out_path.is_file():
            print(f"skip (no existing file): {filename}")
            continue

        blocks = extract_insert_blocks(lines, source)
        if not blocks:
            print(f"WARNING: no INSERT INTO `{source}` found — leaving {filename} unchanged")
            continue

        rewritten: list[str] = []
        for block_lines in blocks:
            first = block_lines[0].replace(f"`{source}`", f"`{target}`", 1)
            rewritten.append("\n".join([first] + block_lines[1:]))

        body = "\n\n".join(rewritten)
        header = f"-- Insert statements for table `{target}`\n\n"
        content = header + body
        if not content.endswith("\n"):
            content += "\n"
        out_path.write_text(content, encoding="utf-8")
        print(f"OK {filename}  ({len(blocks)} INSERT block(s), target `{target}`)")


if __name__ == "__main__":
    main()
