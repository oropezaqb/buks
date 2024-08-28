<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function path()
    {
        return route('companies.show', $this);
    }
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    public function employ($user)
    {
        if (is_string($user)) {
            $user = User::whereName($user)->firstOrFail();
        }
        $this->users()->sync($user, false);
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    public function currentCompany()
    {
        return $this->hasMany(CurrentCompany::class);
    }
    public function getSearchResult(): SearchResult
    {
        $url = route('companies.show', $this);
        return new SearchResult(
            $this,
            $this->name,
            $url
        );
    }
    public function abilities()
    {
        return $this->hasMany(Ability::class);
    }
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
