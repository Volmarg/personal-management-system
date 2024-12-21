<?php

namespace App\Services\Chart;

use DateTimeInterface;

class LinearChartDataHandler
{
    /**
     * If You provide linear chart data sets like this:
     * Set 1:
     * - 2024-02-12
     * - 2024-02-14
     * - 2024-02-15
     *
     * Set 2:
     * - 2024-02-12
     * - 2024-02-13
     * - 2024-02-14
     * - 2024-02-15
     *
     * then the drawn lines are incorrect, the missing dates are not automatically filled with 0, and some values
     * are getting assigned to incorrect dates.
     *
     * This function provides default values for missing months in handled entries. This function is not optimised at all,
     * might turn out that it will get super slow over time, but for now it's just what it is, a quick hacky way to
     * make things work.
     *
     * @param DateTimeInterface|null $lowestDate
     * @param DateTimeInterface|null $highestDate
     * @param array                  $handledEntries
     * @param array                  $datesInGroups
     * @param string|int             $filledValue
     *
     * @return array
     */
    public static function fillMissingMonths(
        ?DateTimeInterface $lowestDate,
        ?DateTimeInterface $highestDate,
        array              $handledEntries,
        array              $datesInGroups,
        string|int         $filledValue = 0
    ): array {
        $allDateVariants = [];
        $currIteratedDate = $lowestDate;
        while ($lowestDate !== $highestDate && $lowestDate && $currIteratedDate <= $highestDate) {
            if (empty($allDateVariants)) {
                $allDateVariants[] = $currIteratedDate->format("Y-m");
            }

            $allDateVariants[] = $currIteratedDate->format("Y-m");
            $currIteratedDate  = $currIteratedDate->modify("+1 MONTH");
        }

        foreach ($allDateVariants as $dateVariant) {
            foreach ($datesInGroups as $group => $dates) {
                if (!in_array($dateVariant, $dates)) {
                    $datesInGroups[$group][]  = $dateVariant;
                    $handledEntries[$group][] = [
                        'value' => $filledValue,
                        'label' => $dateVariant,
                    ];
                }
            }
        }

        return $handledEntries;
    }
}