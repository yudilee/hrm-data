<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceHistoryPart extends Model
{
    protected $table = 'service_history_parts';

    protected $fillable = [
        'service_history_id', 'CJOBN', 'CINVN', 'CVCHR', 
        'CPART', 'EDESC', 'QRECV', 'ASPPRC', 'AFIFO', 'ADISCG'
    ];

    public function history()
    {
        return $this->belongsTo(ServiceHistory::class, 'service_history_id', 'id');
    }

    public function getDescriptionAttribute()
    {
        return $this->EDESC;
    }

    public function getCodeAttribute()
    {
        return $this->CPART;
    }

    public function getQuantityAttribute()
    {
        return $this->QRECV ?: 1;
    }
}
