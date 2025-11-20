<?php

namespace App\Support;

final class PowerModeClassifier
{
    private const OFF_THRESHOLD    = 30.0;
    private const IDLE_THRESHOLD   = 120.0;
    private const ACTIVE_THRESHOLD = 900.0;

    public static function classify(?float $power): ?string
    {
        if ($power === null) {
            return null;
        }

        $p = abs($power);

        if ($p < self::OFF_THRESHOLD) {
            return 'off';
        }
        if ($p < self::IDLE_THRESHOLD) {
            return 'idle';
        }
        if ($p < self::ACTIVE_THRESHOLD) {
            return 'active';
        }

        return 'peak';
    }
}
