<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ContactSheetExport implements FromQuery, WithMapping, WithHeadings, WithTitle, WithChunkReading
{
    protected $query;
    protected $title;
    protected $type;

    public function __construct($query, string $title, string $type)
    {
        $this->query = $query;
        $this->title = $title;
        $this->type = $type;
    }

    public function query()
    {
        return $this->query;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            'is_individual',
            'name',
            'company_id/id',
            'street',
            'street2',
            'city',
            'zip',
            'phone',
            'mobile',
            'email',
            'vat',
            'property_payment_term_id/id',
            'property_supplier_payment_term_id/id',
            'property_account_receivable_id/id',
            'property_account_payable_id/id',
            'ref'
        ];
    }

    public function map($row): array
    {
        $isIndividual = 'True';
        if ($this->type === 'supplier') {
            $isIndividual = 'False';
        } else {
            $name = strtoupper((string)($row->name ?? ''));
            if (preg_match('/\b(PT|CV|UD|TOKO|PO)\b/i', $name)) {
                $isIndividual = 'False';
            }
        }

        return [
            $isIndividual,                           // is_individual
            $row->name ?? '',                        // name
            '',                                      // company_id/id
            $row->address_1 ?? '',                   // street
            $row->address_2 ?? '',                   // street2
            $row->city ?? '',                        // city
            $row->zip ?? '',                         // zip
            $row->phone ?? '',                       // phone
            $row->mobile ?? '',                      // mobile
            $row->email ?? '',                       // email
            '',                                      // vat
            '',                                      // property_payment_term_id/id
            '',                                      // property_supplier_payment_term_id/id
            '',                                      // property_account_receivable_id/id
            '',                                      // property_account_payable_id/id
            $row->id_ref ?? '',                      // ref
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
