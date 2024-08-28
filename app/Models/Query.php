<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('queries.show', $this);
    }
    public function ability()
    {
        return $this->belongsTo(Ability::class);
    }
}
