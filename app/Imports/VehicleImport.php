<?php

namespace App\Imports;

use App\Models\MasterVehicle;
use App\Models\MasterCustomer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Carbon\Carbon;

class VehicleImport implements ToModel, WithHeadingRow, WithUpserts
{
    protected string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function model(array $row)
    {
        $customerMagic = (int) ($row['customer_magic'] ?? 0);

        // Handle placeholder customer if needed
        if ($customerMagic > 0) {
            $this->ensureCustomerExists($customerMagic, $row);
        }

        return new MasterVehicle([
            'magic'             => (int) ($row['magic'] ?? 0),
            'registration_no'   => $this->cleanStr($row['registration_no'] ?? null),
            'franc'             => $this->cleanStr($row['franc'] ?? null),
            'model'             => $this->cleanStr($row['model'] ?? null),
            'variant'           => $this->cleanStr($row['variant'] ?? null),
            'description'       => $this->cleanStr($row['description'] ?? null),
            'chassis_no'        => $this->cleanStr($row['chassis_no'] ?? null),
            'mhl_number'        => $this->cleanStr($row['mhl_number'] ?? null),
            'engine_no'         => $this->cleanStr($row['engine_no'] ?? null),
            'user_id'           => $this->cleanStr($row['user_id'] ?? null),
            'status'            => $this->cleanStr($row['status'] ?? null),
            'progress_code'     => isset($row['progress_code']) ? (int) $row['progress_code'] : null,
            'customer_magic'    => $customerMagic ?: null,
            'reg_date'          => $this->parseDate($row['reg_date'] ?? null),
            'created_date'      => $this->parseDate($row['ceated_date'] ?? null), // Note the typo 'ceated' from the Excel/Python script
            'last_edited_date'  => $this->parseDate($row['last_edited_date'] ?? null),
            'last_service_date' => $this->parseDate($row['last_service_date'] ?? null),
            'source'            => $this->source,
        ]);
    }

    public function uniqueBy()
    {
        return 'magic';
    }

    private function ensureCustomerExists($magic, $row)
    {
        if (!MasterCustomer::where('magic_cust', $magic)->exists()) {
            $a1 = $this->cleanStr($row['address1'] ?? null);
            $a2 = $this->cleanStr($row['address2'] ?? null);
            $a3 = $this->cleanStr($row['address3'] ?? null);
            $a4 = $this->cleanStr($row['address4'] ?? null);
            $a5 = $this->cleanStr($row['address5'] ?? null);

            $fullAddress = collect([$a1, $a2, $a3, $a4, $a5])
                ->filter()
                ->implode(', ');

            MasterCustomer::create([
                'magic_cust'   => $magic,
                'name'         => $this->cleanStr($row['surname'] ?? 'Unknown Customer'),
                'address_1'    => $a1,
                'address_2'    => $a2,
                'address_3'    => $a3,
                'address_4'    => $a4,
                'address_5'    => $a5,
                'full_address' => $fullAddress ?: null,
                'telp_1'       => $this->cleanPhone($row['phone1'] ?? null),
                'telp_2'       => $this->cleanPhone($row['phone2'] ?? null),
                'telp_3'       => $this->cleanPhone($row['phone3'] ?? null),
                'telp_4'       => $this->cleanPhone($row['phone4'] ?? null),
                'source'       => $this->source,
            ]);
        }
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
            if (is_numeric($val)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val);
            }
            return Carbon::parse($val);
        } catch (\Exception $e) {
            return null;
        }
    }
}
