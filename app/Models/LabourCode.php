<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_prefix',
        'group_name',
        'labour_key',
        'code',
        'description',
        'time_hours',
    ];

    /**
     * Scope a query to only include labour codes for a given model prefix.
     */
    public function scopeOfPrefix($query, $prefix)
    {
        return $query->where('model_prefix', $prefix);
    }
}
