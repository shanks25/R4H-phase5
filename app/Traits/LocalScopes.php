<?php

namespace App\Traits;

trait LocalScopes
{
    public function scopeEso($query)
    {
        return $query->where('user_id', esoId());
    }
}
