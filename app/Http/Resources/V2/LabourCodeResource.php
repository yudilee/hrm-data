<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabourCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model_prefix' => $this->model_prefix,
            'code' => $this->code,
            'labour_key' => $this->labour_key,
            'description' => $this->description,
            'group_name' => $this->group_name,
            'time_hours' => (float) $this->time_hours,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
