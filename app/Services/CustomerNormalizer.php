<?php

declare(strict_types=1);

namespace App\Services;

class CustomerNormalizer
{
    public function normalizeName(?string $name): string
    {
        if (! $name) {
            return '';
        }

        $n = strtoupper(trim($name));

        $titles = ['MR', 'MRS', 'MS', 'H', 'HJ', 'DR', 'DRS', 'IR', 'PROF', 'KOL', 'MAY', 'CAPT', 'IBU', 'BPK', 'BAPAK'];
        $pattern = '/^('.implode('|', $titles).')\.?\s+/i';
        $n = preg_replace($pattern, '', $n);

        $entities = ['PT', 'CV', 'UD', 'PO'];
        $entityPattern = '/^('.implode('|', $entities).')\.?\s+/i';
        $n = preg_replace($entityPattern, '', $n);

        return preg_replace('/[.,\s]/', '', $n);
    }

    public function canonicalPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $p = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($p, '+62')) {
            $p = '0'.substr($p, 3);
        } elseif (str_starts_with($p, '62') && strlen($p) > 9) {
            $p = '0'.substr($p, 2);
        }

        return $p !== '' ? $p : null;
    }

    public function detectPhoneType(?string $phone): string
    {
        $p = $this->canonicalPhone($phone);
        if (! $p) {
            return 'unknown';
        }

        if (str_starts_with($p, '08')) {
            return 'mobile';
        }

        if (preg_match('/^0[2-9][0-9]/', $p)) {
            return 'landline';
        }

        return 'unknown';
    }
}
