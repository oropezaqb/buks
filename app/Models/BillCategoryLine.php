<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillCategoryLine extends Model
{
    protected $guarded = [];
    public function account()
    {
        return $this->belongsTo(\App\Models\Account::class, 'account_id');
//        return $this->belongsTo(Account::class, 'account_id');
    }
}
