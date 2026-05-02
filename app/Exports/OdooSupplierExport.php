<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\MasterSupplier;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OdooSupplierExport implements FromQuery, WithChunkReading, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = MasterSupplier::query();

        if (! empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%")
                    ->orWhere('contact_person', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('name');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'is_individual',
            'is_company',
            'Branch',
            'Complete Name',
            'Country',
            'Email',
            'Street',
            'Is PKP',
            'Is PKS',
            'Phone',
            'PKS Document Number',
            'Salesperson',
            'Is ATPM',
            'Document Type',
            'NIK',
            'TKU',
            'Account AR',
            'Account AP',
            'supplier rank',
            'customer rank',
        ];
    }

    public function map($supplier): array
    {
        $address = trim(($supplier->address_1 ?? '').' '.($supplier->address_2 ?? ''));

        return [
            'FALSE',
            'TRUE',
            $this->deriveBranch($supplier->source),
            $supplier->name,
            'Indonesia',
            $supplier->email,
            $address,
            '',
            '',
            $supplier->phone,
            '',
            '',
            'FALSE',
            '',
            '',
            '',
            '',
            '',
            1,
            0,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(2, 1);

                $hints = [
                    'C' => 'nama branch',       'D' => 'nama vendor',
                    'E' => 'negara',             'F' => 'email',
                    'G' => 'alamat',             'H' => 'apakah pkp/tidak',
                    'I' => 'apakah pks/tidak',  'J' => 'no telp',
                    'K' => 'no pks',             'L' => 'nama salesperson',
                    'M' => 'apakah atpm/tidak', 'N' => 'type document',
                    'O' => 'no NIK',            'P' => 'no TKU',
                    'Q' => 'dgn coa ar',        'R' => 'coa ap',
                ];
                foreach ($hints as $col => $hint) {
                    $sheet->setCellValue("{$col}2", $hint);
                }
                $sheet->getStyle('A2:T2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4FA']],
                ]);

                $sheet->freezePane('A3');
                $sheet->setAutoFilter('A1:T1');
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 12, 'C' => 28, 'D' => 32, 'E' => 12,
            'F' => 28, 'G' => 40, 'H' => 10, 'I' => 10, 'J' => 20,
            'K' => 22, 'L' => 20, 'M' => 10, 'N' => 16, 'O' => 20,
            'P' => 25, 'Q' => 15, 'R' => 15, 'S' => 14, 'T' => 14,
        ];
    }

    public function title(): string
    {
        return 'Vendor';
    }

    private function deriveBranch(?string $source): string
    {
        return match ($source) {
            'HRMSBY PC', 'HRMSBY CV' => 'Hartono Motor Surabaya',
            'HRMJKT CV' => 'Hartono Motor Jakarta',
            'HRMDPS PC', 'HRMDPS CV' => 'Hartono Motor Denpasar',
            'HRMSMG PC', 'HRMSMG CV' => 'Hartono Motor Semarang',
            default => '',
        };
    }
}
