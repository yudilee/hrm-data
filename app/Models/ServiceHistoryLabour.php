<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceHistoryLabour extends Model
{
    protected $table = 'service_history_labours';

    protected $fillable = [
        'service_history_id', 'CJOBN', 'CINVN', 'CDJOB', 
        'EMJOB', 'QHOUR', 'TAKEN', 'NET', 'DISC'
    ];

    public function history()
    {
        return $this->belongsTo(ServiceHistory::class, 'service_history_id', 'id');
    }

    public function getDescriptionAttribute()
    {
        return $this->EMJOB;
    }

    public function getCodeAttribute()
    {
        return $this->CDJOB;
    }
}
