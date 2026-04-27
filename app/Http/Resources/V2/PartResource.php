<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'part_no'     => $this->CPART,
            'description' => $this->NPART,
            'quantity'    => $this->QPART,
            'amount'      => $this->NJUML,
        ];
    }
}
