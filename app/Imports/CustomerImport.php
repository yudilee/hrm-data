<?php

namespace App\Imports;

use App\Models\MasterCustomer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Carbon\Carbon;

class CustomerImport implements ToModel, WithHeadingRow, WithUpserts
{
    public function model(array $row)
    {
        $a1 = $this->cleanStr($row['address_1'] ?? null);
        $a2 = $this->cleanStr($row['address_2'] ?? null);
        $a3 = $this->cleanStr($row['address_3'] ?? null);
        $a4 = $this->cleanStr($row['address_4'] ?? null);
        $a5 = $this->cleanStr($row['address_5'] ?? null);

        $fullAddress = collect([$a1, $a2, $a3, $a4, $a5])
            ->filter()
            ->implode(', ');

        return new MasterCustomer([
            'magic_cust'   => (int) ($row['magic_cust'] ?? 0),
            'name'         => $this->cleanStr($row['nama_customer'] ?? null),
            'address_1'    => $a1,
            'address_2'    => $a2,
            'address_3'    => $a3,
            'address_4'    => $a4,
            'address_5'    => $a5,
            'full_address' => $fullAddress ?: null,
            'company_name' => $this->cleanStr($row['company_name'] ?? null),
            'magic_comp'   => (int) ($row['magic_comp'] ?? 0),
            'email'        => $this->cleanStr($row['e_mail_address'] ?? null),
            'dept'         => $this->cleanStr($row['dept'] ?? null),
            'title'        => $this->cleanStr($row['title'] ?? null),
            'telp_1'       => $this->cleanPhone($row['telp_01'] ?? null),
            'telp_2'       => $this->cleanPhone($row['telp_02'] ?? null),
            'telp_3'       => $this->cleanPhone($row['telp_03'] ?? null),
            'telp_4'       => $this->cleanPhone($row['telp_04'] ?? null),
            'date_created' => $this->parseDate($row['date_created'] ?? null),
            'source'       => 'customer_import',
        ]);
    }

    public function uniqueBy()
    {
        return 'magic_cust';
    }

    private function cleanStr($val)
    {
        if (is_null($val)) return null;
        $s = trim((string)$val);
        return $s !== '' ? $s : null;
    }

    private function cleanPhone($val)
    {
        $s = $this->cleanStr($val);
        if (!$s || in_array($s, ['0', '-', 'N/A', 'n/a'])) return null;
        return $s;
    }

    private function parseDate($val)
    {
        if (!$val) return null;
        try {
            // Excel dates can be numeric or strings
            if (is_numeric($val)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val);
            }
            return Carbon::parse($val);
        } catch (\Exception $e) {
            return null;
        }
    }
}
