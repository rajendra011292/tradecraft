<?php
namespace App\Support;

class Math {
    /** Round quantity down to the nearest step (default 1 share). */
    public static function roundDownQty(float $qty, float $step = 1.0): float {
        if ($step <= 0) $step = 1.0;
        return floor($qty / $step) * $step;
    }
}
