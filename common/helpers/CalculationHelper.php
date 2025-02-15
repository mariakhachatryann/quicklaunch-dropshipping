<?php


namespace common\helpers;


class CalculationHelper
{

    public static function getPercent($count, $total)
    {
        if ($total > 0) {
            return ceil(($count * 100) / $total);
        }
        return 100;
    }

    public static function getTotalByPercent(float $total, ?float $percent)
    {
        $percent = ($total * $percent) / 100;
        return $total + $percent;
    }

}