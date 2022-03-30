<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    protected $table = 'county_states';

    public function county()
    {
        return $this->hasMany(City::class, 'state_id')->orderBy('name', 'ASC');
    }
}
