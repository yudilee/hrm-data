<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceHistory extends Model
{
    protected $table = 'service_histories';

    protected $fillable = [
        'CJOBN', 'CINVN', 'CNPOL', 'CHASN', 'CENGN', 
        'DRECV', 'DINVN', 'CCUST', 'ENAME', 'EADDR', 
        'ECITY', 'EPHON', 'ETYPE', 'DSTNK', 'EKMPOS',
        'ALBRS', 'ASPTS', 'ASSPS', 'ASUBS', 'AOTHS1', 'AOTHS2', 
        'DISC', 'ATAXS', 'AMTRS', 'PTAX'
    ];

    protected $casts = [
        'DRECV' => 'date',
        'DINVN' => 'date',
        'DSTNK' => 'date',
    ];

    public function labours()
    {
        return $this->hasMany(ServiceHistoryLabour::class, 'service_history_id', 'id');
    }

    public function parts()
    {
        return $this->hasMany(ServiceHistoryPart::class, 'service_history_id', 'id');
    }
}
