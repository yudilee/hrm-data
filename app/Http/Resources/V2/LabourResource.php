<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->CJASA,
            'description' => $this->NJASA,
            'quantity' => $this->QJASA,
            'amount' => $this->NJUML,
        ];
    }
}
