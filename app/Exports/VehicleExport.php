<?php

namespace App\Exports;

use App\Models\MasterVehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return MasterVehicle::with('customer:magic_cust,name,source')
            ->orderBy('registration_no')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Registration No',
            'Chassis No',
            'Engine No',
            'Brand',
            'Model',
            'Variant',
            'Description',
            'Status',
            'Reg Date',
            'Last Service',
            'Owner Name',
            'Owner ID (magic_cust)',
            'Source',
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->registration_no,
            $vehicle->chassis_no,
            $vehicle->engine_no,
            $vehicle->franc,
            $vehicle->model,
            $vehicle->variant,
            $vehicle->description,
            $vehicle->status,
            $vehicle->reg_date?->format('Y-m-d'),
            $vehicle->last_service_date?->format('Y-m-d'),
            $vehicle->customer?->name,
            $vehicle->customer_magic,
            $vehicle->source,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
        ]);

        $lastRow = $sheet->getHighestRow();
        $sheet->setAutoFilter("A1:M{$lastRow}");

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 22, 'C' => 22,
            'D' => 6, 'E' => 12, 'F' => 15,
            'G' => 30, 'H' => 8, 'I' => 12,
            'J' => 12, 'K' => 30, 'L' => 15,
            'M' => 15,
        ];
    }

    public function title(): string
    {
        return 'Master Vehicles';
    }
}
