<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomersRule extends Model
{
    protected $fillable = [
        'customer_id',
        'leads_number',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'sunday',
        'name',
        'zip',
        'hours'
    ];

    protected $casts = ['zip' => 'array', 'hours' => 'array'];


    public function landings() {
        return $this->belongsToMany('App\Landing', 'landing_customers_rules');
    }

}
