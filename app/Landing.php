<?php

namespace App;

use App\Libs\Convert;
use Illuminate\Database\Eloquent\Model;

class Landing extends Model
{
    protected $fillable = [
        'type',
        'forms'
    ];

    public function customersRules() {
        return $this->belongsToMany('App\CustomersRule', 'landing_customers_rules');
    }


    public static function get_keys($landings) {
        $keys = ['id', 'created_at', 'updated_at', 'landing', 'visitor', 'convert'];
        foreach ($landings as $landing) {
            $landing_keys = array_keys(json_decode($landing->forms, true));
            $keys = array_merge($keys, $landing_keys);
            if ($landing->zone_climatique)
                $keys = array_merge($keys, ['zone_climatique']);
            if ($landing->type == 'facebook_ads') {
               if (($landingData = \App\LandingData::where('landing_id', $landing->id)->first())) {
                   $test = json_decode($landingData->data, true);
                   $keys = array_merge($keys, array_keys($test));
               }
            }
        }
        unset($keys['entry_status']);
        return array_values(array_unique($keys));
    }


//    CONVERT FUNCTIONS

    public static function convertGetFields($data) {
        $array = [];
        $convert = new Convert();

        foreach ($data as $key => $item) {
            if (in_array($key, $convert->keep_fields()))
                $array[$key] = 'kept_data';
            else if ($key != 'entry_status')
                $array[$key] = 'generate_data';
        }
        return $array;
    }

    public static function convertFields($original_landing, $data, $fields, $landingConvert) {
        $arr = array();
        $convert = new Convert();
        $keep = [];
        $landingConvertField = $landingConvert['default'];

        if (isset($landingConvert[$original_landing])) {
            $keep = $landingConvert[$original_landing]['rules'];
        }

        foreach ($fields as $field => $action) {
            if ($action == 'kept_data') {
                $arr[$field] = $convert->{str_replace('-', '_', $field)}($data);
            } else if (isset($keep[$field])) {
                $arr[$field] = $convert->keep($data, $keep[$field]);
            } else {
                $arr[$field] = $convert->randomField($field, $landingConvertField);
            }
        }

        return $arr;
    }

}
