<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\MasterVehicle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MasterVehicleExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = MasterVehicle::query()->with('customer');

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                    ->orWhere('chassis_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('engine_no', 'like', "%{$search}%");
            });
        }

        if (! empty($this->filters['franchise'])) {
            $query->where('true_franchise', $this->filters['franchise']);
        }

        if (! empty($this->filters['branch'])) {
            $query->whereJsonContains('branches_visited', $this->filters['branch']);
        }

        if (! empty($this->filters['year'])) {
            $query->whereYear('last_service_date', $this->filters['year']);
        }

        return $query->latest('last_service_date');
    }

    public function headings(): array
    {
        return [
            'Registration No',
            'Model',
            'Variant',
            'Description',
            'True Franchise',
            'Chassis No',
            'Engine No',
            'Branches Visited',
            'Last Service Date',
            'Customer Name',
            'Source File',
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->registration_no,
            $vehicle->model,
            $vehicle->variant,
            $vehicle->description,
            $vehicle->true_franchise,
            $vehicle->chassis_no,
            $vehicle->engine_no,
            implode(', ', $vehicle->branches_visited ?? []),
            $vehicle->last_service_date?->format('Y-m-d'),
            $vehicle->customer?->name,
            $vehicle->source,
        ];
    }
}
