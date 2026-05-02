<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\MasterCustomer;
use App\Utils\BranchUtil;
use Illuminate\Support\Facades\DB;
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

class OdooCustomerExport implements FromQuery, WithChunkReading, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected array $filters;

    protected array $customerBranchMap = [];

    protected bool $isExpanded;

    protected array $branchColumns = BranchUtil::BRANCH_CODES;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->isExpanded = ! empty($this->filters['expanded_branches']);
        $this->buildCustomerBranchMap();
    }

    /**
     * Build a customer_id => branch_name map from their vehicles' branches_visited.
     * Uses a single query — used as fallback for legacy customers with no HRM source.
     */
    protected function buildCustomerBranchMap(): void
    {
        $rows = DB::table('master_vehicles')
            ->whereNotNull('primary_customer_id')
            ->whereNotNull('branches_visited')
            ->where('branches_visited', '!=', '[]')
            ->where('branches_visited', '!=', 'null')
            ->select('primary_customer_id', 'branches_visited', 'last_service_date')
            ->orderByDesc('last_service_date')
            ->get();

        foreach ($rows as $row) {
            $cid = $row->primary_customer_id;
            // Keep only the most-recent vehicle's first branch per customer
            if (isset($this->customerBranchMap[$cid])) {
                continue;
            }

            $branches = json_decode($row->branches_visited, true) ?? [];
            if (! empty($branches)) {
                $this->customerBranchMap[$cid] = $this->branchCodeToName($branches[0]);
            }
        }
    }

    protected function branchCodeToName(string $code): string
    {
        return BranchUtil::codeToSimpleBranch($code);
    }

    public function query()
    {
        $query = MasterCustomer::query();

        if (! empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('telp_1', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%")
                    ->orWhere('full_address', 'like', "%{$s}%");
            });
        }

        if (! empty($this->filters['source'])) {
            $query->where('source', $this->filters['source']);
        }

        if (! empty($this->filters['customer_type'])) {
            $query->where('customer_type', $this->filters['customer_type']);
        }

        if (! empty($this->filters['city'])) {
            $city = $this->filters['city'];
            $query->where(function ($q) use ($city) {
                $q->where('address_5', $city)
                    ->orWhere('address_4', $city)
                    ->orWhere('address_3', $city);
            });
        }

        if (! empty($this->filters['vehicle_status'])) {
            if ($this->filters['vehicle_status'] === 'with_vehicles') {
                $query->has('vehicles');
            } elseif ($this->filters['vehicle_status'] === 'no_vehicles') {
                $query->doesntHave('vehicles');
            }
        }

        if (! empty($this->filters['quality'])) {
            match ($this->filters['quality']) {
                'high' => $query->where('data_quality_score', '>', 60),
                'medium' => $query->whereBetween('data_quality_score', [21, 60]),
                'low' => $query->where('data_quality_score', '<=', 20),
                default => null,
            };
        }

        if (! empty($this->filters['visit_period'])) {
            $years = (int) $this->filters['visit_period'];
            if (in_array($years, [1, 2, 3, 5])) {
                $cutoff = now()->subYears($years);
                $query->where(function ($q) use ($cutoff) {
                    $q->whereHas('serviceHistories', function ($sq) use ($cutoff) {
                        $sq->where('DINVN', '>=', $cutoff)
                            ->where('DINVN', '<=', now()->addYear());
                    })->orWhereHas('vehicles', function ($vq) use ($cutoff) {
                        $vq->where('last_service_date', '>=', $cutoff)
                            ->where('last_service_date', '<=', now()->addYear());
                    });
                });
            }
        }

        if (! empty($this->filters['multi_branch']) && $this->filters['multi_branch'] == '1') {
            $query->whereHas('vehicles', function ($q) {
                $q->whereRaw('JSON_LENGTH(branches_visited) > 1');
            });
        }

        if (! empty($this->filters['branch_source'])) {
            $query->whereJsonContains('sources', $this->filters['branch_source']);
        }

        return $query->orderBy('name');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        $base = [
            'is_individual',
            'is_company',
        ];

        if ($this->isExpanded) {
            $base = array_merge($base, $this->branchColumns);
        } else {
            $base[] = 'Branch';
        }

        return array_merge($base, [
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
        ]);
    }

    public function map($customer): array
    {
        $isCompany = $customer->customer_type === 'company';
        $isIndividual = ! $isCompany;

        $sources = $customer->sources ?? [];

        $branchData = [];
        if ($this->isExpanded) {
            foreach ($this->branchColumns as $bc) {
                // Check if this specific branch is in sources, or if canonical source matches
                $hasBranch = in_array($bc, $sources) || $customer->source === $bc;

                // Fallback to vehicle lookup if no sources are set and it matches the map
                if (! $hasBranch && empty($sources) && isset($this->customerBranchMap[$customer->id])) {
                    if ($this->branchCodeToFullName($bc) === $this->customerBranchMap[$customer->id]) {
                        // This fallback is tricky because vehicle map gives full name,
                        // and one full name maps to multiple codes (PC/CV). We'll set TRUE for both if it matches full name.
                        $hasBranch = true;
                    }
                }
                $branchData[] = $hasBranch ? 'TRUE' : 'FALSE';
            }
        } else {
            // Primary: use sources[] (all registered branches), comma-joined
            // Fallback 1: canonical HRM source code
            // Fallback 2: vehicle branch lookup map
            if (! empty($sources)) {
                // Map codes to full names and join
                $branch = implode(', ', array_map(
                    fn ($s) => $this->branchCodeToFullName($s),
                    array_filter($sources)
                ));
            } else {
                $branch = $this->deriveBranch($customer->source)
                       ?: ($this->customerBranchMap[$customer->id] ?? '');
            }
            $branchData[] = $branch;
        }

        return array_merge([
            $isIndividual ? 'TRUE' : 'FALSE',
            $isCompany ? 'TRUE' : 'FALSE',
        ], $branchData, [
            $customer->name,
            'Indonesia',
            $customer->email,
            $customer->full_address,
            '',
            '',
            $customer->telp_1,
            '',
            '',
            'FALSE',
            '',
            '',
            '',
        ]);
    }

    private function branchCodeToFullName(string $code): string
    {
        return BranchUtil::codeToFullName($code);
    }

    public function styles(Worksheet $sheet): array
    {
        $range = $this->isExpanded ? 'A1:V1' : 'A1:P1';
        $sheet->getStyle($range)->applyFromArray([
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

                // Insert hint row between header and data
                $sheet->insertNewRowBefore(2, 1);

                if ($this->isExpanded) {
                    $hints = [
                        'C' => 'nama branch',       'D' => 'nama branch',
                        'E' => 'nama branch',       'F' => 'nama branch',
                        'G' => 'nama branch',       'H' => 'nama branch',
                        'I' => 'nama branch',       'J' => 'nama customer',
                        'K' => 'negara',             'L' => 'email',
                        'M' => 'alamat',             'N' => 'apakah pkp/tidak',
                        'O' => 'apakah pks/tidak',  'P' => 'no telp',
                        'Q' => 'no pks',             'R' => 'nama salesperson',
                        'S' => 'apakah atpm/tidak', 'T' => 'type document',
                        'U' => 'no NIK',            'V' => 'no TKU',
                    ];
                    $range = 'A2:V2';
                    $filterRange = 'A1:V1';
                } else {
                    $hints = [
                        'C' => 'nama branch',       'D' => 'nama customer',
                        'E' => 'negara',             'F' => 'email',
                        'G' => 'alamat',             'H' => 'apakah pkp/tidak',
                        'I' => 'apakah pks/tidak',  'J' => 'no telp',
                        'K' => 'no pks',             'L' => 'nama salesperson',
                        'M' => 'apakah atpm/tidak', 'N' => 'type document',
                        'O' => 'no NIK',            'P' => 'no TKU',
                    ];
                    $range = 'A2:P2';
                    $filterRange = 'A1:P1';
                }

                foreach ($hints as $col => $hint) {
                    $sheet->setCellValue("{$col}2", $hint);
                }

                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4FA']],
                ]);

                $sheet->freezePane('A3');
                $sheet->setAutoFilter($filterRange);
            },
        ];
    }

    public function columnWidths(): array
    {
        if ($this->isExpanded) {
            return [
                'A' => 12, 'B' => 12, 'C' => 15, 'D' => 15, 'E' => 15,
                'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 32,
                'K' => 12, 'L' => 28, 'M' => 40, 'N' => 10, 'O' => 10,
                'P' => 20, 'Q' => 22, 'R' => 20, 'S' => 10, 'T' => 16,
                'U' => 20, 'V' => 25,
            ];
        }

        return [
            'A' => 12, 'B' => 12, 'C' => 28, 'D' => 32, 'E' => 12,
            'F' => 28, 'G' => 40, 'H' => 10, 'I' => 10, 'J' => 20,
            'K' => 22, 'L' => 20, 'M' => 10, 'N' => 16, 'O' => 20,
            'P' => 25,
        ];
    }

    public function title(): string
    {
        return 'Customer';
    }

    private function deriveBranch(?string $source): string
    {
        return BranchUtil::codeToFullName($source ?? '');
    }
}
