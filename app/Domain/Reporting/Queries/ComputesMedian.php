<?php

namespace App\Domain\Reporting\Queries;

/**
 * PHP-computed median (sort + midpoint), not a MariaDB window function —
 * matches the aggregation approach already established for provider
 * response metrics, and sidesteps depending on a specific MariaDB
 * ordered-set-function version.
 */
trait ComputesMedian
{
    private function median(array $values): ?float
    {
        sort($values);
        $count = count($values);

        if ($count === 0) {
            return null;
        }

        $middle = intdiv($count, 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return (float) $values[$middle];
    }
}
