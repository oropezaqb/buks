<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCredit extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('supplier_credits.show', $this);
    }
    public function clines()
    {
        return $this->hasMany(SupplierCreditCLine::class);
    }
    public function ilines()
    {
        return $this->hasMany(SupplierCreditILine::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function purchaseReturns()
    {
        return $this->morphMany('App\Models\PurchaseReturn', 'returnablepurc');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\Models\JournalEntry', 'journalizable');
    }
    public function purchasable()
    {
        return $this->morphTo();
    }
    public function delete()
    {
        $res=parent::delete();
        if ($res==true) {
            $purchaseReturns = $this->purchaseReturns;
            foreach ($purchaseReturns as $purchaseReturn) {
                $purchaseReturn->delete();
            }
            if (!is_null($this->journalEntry)) {
                $this->journalEntry->delete();
            }
        }
    }
}
