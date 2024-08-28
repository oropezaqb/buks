<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */

class SalesReturn extends Model
{
    protected $guarded = [];
    public function returnablesale()
    {
        return $this->morphTo();
    }
}
