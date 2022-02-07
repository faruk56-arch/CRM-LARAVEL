<?php

namespace App\Http\Controllers;

use App\Rapport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RapportController extends Controller
{
    public function index(Request $request, $rule_id) {

        $rapport = \App\Rapport::where('customer_rule_id', $rule_id)->orderBy('id', 'desc')->get();
        $array = array();

        foreach ($rapport as $rap) {
            $tmp = $rap;
            $leads = \App\LandingData::where('rapport_id', $rap->id)->get();
            $tmp['count'] = count($leads);
            $test = [];
            foreach ($leads as $lead) {
                $test[$lead->id] = true;
            }
            $tmp['leads'] = array_keys($test);
            $array[] = $tmp;
        }

        return $array;
    }

}
