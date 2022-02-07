<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingCustomersRule extends Model
{
    protected $fillable = [
        'customers_rule_id',
        'landing_id'
    ];
}
