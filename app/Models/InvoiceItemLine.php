<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItemLine extends Model
{
    protected $guarded = [];
    protected $table = 'invoice_item_lines';
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
