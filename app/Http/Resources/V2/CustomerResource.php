<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'customer_type' => $this->customer_type,
            'email' => $this->email,
            'phone' => $this->telp_1,
            'phone_2' => $this->telp_2,
            'address' => $this->full_address,
            'address_city' => $this->address_5 ?? $this->address_4 ?? $this->address_3,
            'source' => $this->source,
            'sources' => $this->sources,
            'data_quality_score' => $this->data_quality_score,
            'odoo_id' => $this->odoo_id,
            'sync_status' => $this->sync_status,
            'last_synced_at' => $this->last_synced_at,
            'vehicles_count' => $this->whenCounted('vehicles'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
