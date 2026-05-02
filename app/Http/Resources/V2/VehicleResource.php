<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_no' => $this->registration_no,
            'chassis_no' => $this->chassis_no,
            'engine_no' => $this->engine_no,
            'description' => $this->description,
            'model' => $this->model,
            'color' => $this->color,
            'year' => $this->year,
            'true_franchise' => $this->true_franchise,
            'mhl_number' => $this->mhl_number,
            'status' => $this->status,
            'branches_visited' => $this->branches_visited,
            'last_service_date' => $this->last_service_date,
            'customer_id' => $this->primary_customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'odoo_id' => $this->odoo_id,
            'sync_status' => $this->sync_status,
            'last_synced_at' => $this->last_synced_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
