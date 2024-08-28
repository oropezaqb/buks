<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    public function path()
    {
        return route('company_users.show', $this);
    }
}
