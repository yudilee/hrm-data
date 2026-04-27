<?php

namespace App\Exports;

use App\Models\MasterCustomer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Group customers by normalized name + phone.
     * Same person across branches → single row with aggregated sources.
     */
    public function collection()
    {
        $query = MasterCustomer::withCount('vehicles');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telp_1', 'like', "%{$search}%")
                  ->orWhere('telp_2', 'like', "%{$search}%")
                  ->orWhere('full_address', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['source'])) {
            $query->where('source', $this->filters['source']);
        }

        $all = $query->orderBy('name')->get();

        // Group by normalized_name + phone (compound key for true dedup)
        $groups = [];
        foreach ($all as $customer) {
            $norm = MasterCustomer::normalizeName($customer->name);
            $phone = $this->normalizePhone($customer->telp_1);

            // Only group by phone if phone actually exists, otherwise each is unique
            if ($norm && $phone) {
                $key = $norm . '|' . $phone;
            } else {
                $key = 'solo_' . $customer->magic_cust; // unique key = no grouping
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'canonical' => $customer,
                    'sources'   => [],
                    'magic_ids' => [],
                    'vehicles_total' => 0,
                ];
            }

            // Keep the most complete record as canonical (prefer customer_import, then by data completeness)
            $current = $groups[$key]['canonical'];
            if ($this->completenessScore($customer) > $this->completenessScore($current)) {
                $groups[$key]['canonical'] = $customer;
            }

            if ($customer->source) {
                $groups[$key]['sources'][] = $customer->source;
            }
            $groups[$key]['magic_ids'][] = $customer->magic_cust;
            $groups[$key]['vehicles_total'] += $customer->vehicles_count;
        }

        // Build the final collection
        $result = collect();
        foreach ($groups as $group) {
            $c = $group['canonical'];
            $c->_sources = implode(', ', array_unique($group['sources']));
            $c->_magic_ids = implode(', ', $group['magic_ids']);
            $c->_vehicles_total = $group['vehicles_total'];
            $c->_is_multi = count(array_unique($group['sources'])) > 1;
            $result->push($c);
        }

        return $result->sortBy('name')->values();
    }

    public function headings(): array
    {
        return [
            'Customer Name',
            'Company',
            'Email',
            'Phone 1',
            'Phone 2',
            'Address',
            'City',
            'Vehicles',
            'Sources',
            'Multi-Branch',
            'Legacy IDs (magic_cust)',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->company_name,
            $customer->email,
            $customer->telp_1,
            $customer->telp_2,
            $customer->full_address,
            $customer->address_5,
            $customer->_vehicles_total,
            $customer->_sources,
            $customer->_is_multi ? 'YES' : '',
            $customer->_magic_ids,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4338CA'],
            ],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        ]);

        // Auto-filter
        $lastRow = $sheet->getHighestRow();
        $sheet->setAutoFilter("A1:K{$lastRow}");

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, 'B' => 25, 'C' => 25,
            'D' => 18, 'E' => 18, 'F' => 40,
            'G' => 15, 'H' => 10, 'I' => 35,
            'J' => 12, 'K' => 30,
        ];
    }

    public function title(): string
    {
        return 'Master Customers';
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        // Strip everything except digits
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) < 7) return null;
        // Normalize leading country code
        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }
        return $digits;
    }

    private function completenessScore(MasterCustomer $c): int
    {
        $score = 0;
        // Prefer customer_import source
        if (str_starts_with($c->source ?? '', 'HRM')) $score += 100;
        if ($c->source === 'customer_import') $score += 50;
        if ($c->email) $score += 10;
        if ($c->telp_1) $score += 10;
        if ($c->full_address) $score += 5;
        if ($c->company_name) $score += 5;
        if ($c->vehicles_count > 0) $score += $c->vehicles_count;
        return $score;
    }
}
