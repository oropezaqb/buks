<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCreditILine extends Model
{
    protected $guarded = [];
    protected $table = 'supplier_credit_ilines';
    public function purchasable()
    {
        return $this->morphTo();
    }
}
