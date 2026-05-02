<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\MasterSupplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function collection()
    {
        return MasterSupplier::orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Address 1',
            'Address 2',
            'City',
            'Postal Code',
            'Phone',
            'Fax',
            'Contact Person',
            'Email',
            'Bank Name',
            'Bank Account No',
            'Bank Account Name',
            'Category',
            'Source',
        ];
    }

    public function map($supplier): array
    {
        return [
            $supplier->code,
            $supplier->name,
            $supplier->address_1,
            $supplier->address_2,
            $supplier->city,
            $supplier->postal_code,
            $supplier->phone,
            $supplier->fax,
            $supplier->contact_person,
            $supplier->email,
            $supplier->bank_name,
            $supplier->bank_account_no,
            $supplier->bank_account_name,
            $supplier->category,
            $supplier->source,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
        ]);

        $lastRow = $sheet->getHighestRow();
        $sheet->setAutoFilter("A1:O{$lastRow}");

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 30, 'C' => 25,
            'D' => 25, 'E' => 15, 'F' => 10,
            'G' => 18, 'H' => 18, 'I' => 20,
            'J' => 25, 'K' => 20, 'L' => 20,
            'M' => 20, 'N' => 15, 'O' => 15,
        ];
    }

    public function title(): string
    {
        return 'Master Suppliers';
    }
}
