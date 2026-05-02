<?php

declare(strict_types=1);

namespace App\Utils;

class BranchUtil
{
    public const BRANCH_CODES = [
        'HRMSBY PC', 'HRMSBY CV', 'HRMJKT CV', 'HRMDPS PC', 'HRMDPS CV', 'HRMSMG PC', 'HRMSMG CV',
    ];

    public static function codeToFullName(string $code): string
    {
        return match ($code) {
            'HRMSBY PC', 'HRMSBY CV' => 'Hartono Motor Surabaya',
            'HRMJKT CV' => 'Hartono Motor Jakarta',
            'HRMDPS PC', 'HRMDPS CV' => 'Hartono Motor Denpasar',
            'HRMSMG PC', 'HRMSMG CV' => 'Hartono Motor Semarang',
            default => $code,
        };
    }

    public static function codeToSimpleBranch(string $code): string
    {
        return match ($code) {
            'SBY' => 'Hartono Motor Surabaya',
            'JKT' => 'Hartono Motor Jakarta',
            'DPS' => 'Hartono Motor Denpasar',
            'SMG' => 'Hartono Motor Semarang',
            default => '',
        };
    }
}
