<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayorContract extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    public function methods()
    {
        return $this->hasMany(PayorContractMethod::class, 'contract_id');
    }
    // public function methodids()
    // {
    //     return $this->methods->pluck('method_id');
    // }
}
