<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class CustomerController extends Controller
{

    public function index() {
        return \App\Customer::all();
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:customers',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        \App\Customer::create([
            'name' => $request->get('name'),
            'email' => $request->get('email')
        ]);

        return ['status' => 'created'];
    }
}
