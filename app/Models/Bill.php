<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('bills.show', $this);
    }
    public function categoryLines()
    {
        return $this->hasMany(\App\Models\BillCategoryLine::class);
    }
    public function itemLines()
    {
        return $this->hasMany(BillItemLine::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function purchases()
    {
        return $this->morphMany('App\Models\Purchase', 'purchasable');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\Models\JournalEntry', 'journalizable');
    }
    public function supplierCredits()
    {
        return $this->morphMany('App\Models\SupplierCredit', 'purchasable');
    }
    public function supplierCreditCLine()
    {
        return $this->morphMany('App\Models\SupplierCreditCLine', 'purchasable');
    }
    public function supplierCreditILine()
    {
        return $this->morphMany('App\Models\SupplierCreditILine', 'purchasable');
    }
    public function purchaseReturns()
    {
        return $this->morphMany('App\Models\PurchaseReturn', 'purchasable');
    }
    public function delete()
    {
        $res = parent::delete();
        if ($res==true) {
            $relations = $this->purchases;
            foreach ($relations as $relation) {
                $relation->delete();
            }
            if (!is_null($this->journalEntry)) {
                $this->journalEntry->delete();
            }
        }
    }
}
