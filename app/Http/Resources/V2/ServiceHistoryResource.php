<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->CINVN,
            'vehicle_id' => $this->vehicle_id,
            'chassis_no' => $this->CHASN,
            'plate_no' => $this->CNPOL,
            'customer_code' => $this->CCUST,
            'date_received' => $this->DRECV,
            'date_invoiced' => $this->DINVN,
            'km_in' => $this->NKMIN,
            'km_out' => $this->NKMOT,
            'branch' => $this->branch,
            'total_amount' => $this->NTOT,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'labours' => LabourResource::collection($this->whenLoaded('labours')),
            'parts' => PartResource::collection($this->whenLoaded('parts')),
            'odoo_id' => $this->odoo_id,
            'sync_status' => $this->sync_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
