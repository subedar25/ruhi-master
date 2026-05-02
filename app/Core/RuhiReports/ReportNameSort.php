<?php

namespace App\Core\RuhiReports;

/**
 * Sort key for product/design names with a hyphen + numeric suffix, matching legacy MySQL:
 *
 *   ORDER BY LEFT(P.product_name,LOCATE('-',P.product_name)),
 *            CAST(SUBSTRING(P.product_name,LOCATE('-',P.product_name)+1) AS SIGNED)
 *
 * {@see hyphenNameTuple()} + {@see compareTuples()} implement the same ordering in PHP for in-memory rows.
 */
final class ReportNameSort
{
    /**
     * @return array{0: string, 1: int}
     */
    public static function hyphenNameTuple(string $name): array
    {
        $pos = strpos($name, '-');
        if ($pos === false) {
            $leading = '';
            $afterHyphen = $name;
        } else {
            $mysqlLocate = $pos + 1;
            $leading = substr($name, 0, $mysqlLocate);
            $afterHyphen = substr($name, $mysqlLocate);
        }

        if (preg_match('/^\s*(-?\d+)/', $afterHyphen, $m)) {
            $num = (int) $m[1];
        } else {
            $num = 0;
        }

        return [$leading, $num];
    }

    /**
     * @param  array{0: string, 1: int}  $a
     * @param  array{0: string, 1: int}  $b
     */
    public static function compareTuples(array $a, array $b): int
    {
        if ($a[0] !== $b[0]) {
            return $a[0] <=> $b[0];
        }

        return $a[1] <=> $b[1];
    }
}
