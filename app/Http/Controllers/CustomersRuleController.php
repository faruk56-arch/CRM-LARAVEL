<?php

namespace App\Http\Controllers;

use App\CustomersRule;
use Illuminate\Http\Request;
use Validator;

class CustomersRuleController extends Controller
{

    public function index(Request $request) {

        if ($request->input('customer_id')) {
            $rules = \App\CustomersRule::where('status', true)->where('customer_id', $request->input('customer_id'))->with('landings:name')->get();
        } else {
            $rules = \App\CustomersRule::where('status', true)->with('landings:name')->get();
        }
        return $rules;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
            'leads_number' => 'required|integer|min:1',
            'landings' => 'required|array',
            'name' => 'required|string',
            'hours' => 'required|array',
            'zip' => 'array',
            'landings.*' => 'exists:landings,id'
        ]);

        if ($validator->fails()) {
            return ['status' => false, 'error' => $validator->errors()];
        }

        $customer_rule = \App\CustomersRule::create([
            'customer_id' => $request->input('customer_id'),
            'name' => $request->input('name'),
            'zip' => $request->input('zip'),
            'hours' => $request->input('hours'),
            'leads_number' => $request->input('leads_number'),
            'monday' => ((!$request->input('monday')) ? false : true),
            'tuesday' => ((!$request->input('tuesday')) ? false : true),
            'wednesday' => ((!$request->input('wednesday')) ? false : true),
            'thursday' => ((!$request->input('thursday')) ? false : true),
            'friday' => ((!$request->input('friday')) ? false : true),
            'sunday' => ((!$request->input('sunday')) ? false : true),
            'saturday' => ((!$request->input('saturday')) ? false : true)
        ]);

        if ($customer_rule) {
            foreach ($request->input('landings') as $landing) {
                \App\LandingCustomersRule::create(['landing_id' => $landing, 'customers_rule_id' => $customer_rule->id]);
            }
        }

        if ($request->input(strtolower(date('l')))) {
            \App\Rapport::create([
                'customer_id' => $request->input('customer_id'),
                'zip' => $request->input('zip'),
                'hours' => $request->input('hours'),
                'customer_rule_id' => $customer_rule->id,
                'date' => date('Y-m-d'),
                'leads_number' => $customer_rule->leads_number
            ]);
        }

        return ['status' => true];
    }

    public function delete($id) {
        $result = \App\CustomersRule::where('id', $id)->first();

        if ($result) {
            \App\Rapport::where('customer_rule_id', $result->id)->update(array('status' => false));
            $result->status = false;
            $result->save();
        }
        return 'saved';
    }

}
