<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivedPaymentLine extends Model
{
    protected $guarded = [];
    public function receivedPayment()
    {
        return $this->belongsTo(ReceivedPayment::class);
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
