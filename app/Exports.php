<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exports extends Model
{

    public function landings() {
        return $this->belongsToMany(Landing::class, 'export_landings', 'export_id', 'landing_id');
    }
}
