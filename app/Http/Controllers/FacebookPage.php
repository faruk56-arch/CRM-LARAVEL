<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacebookPage extends Controller
{
    public function index()
    {
        $landings = \App\Landing::where('type', 'facebook_ads')->get();

        return $landings;
    }

    public function syncForm() {

        $result = \App\FacebookPage::syncForm();

        if ($result)
            return response()->json(['message' => 'updated'], 200);
        return response()->json(['message' => 'error_please_contact_hono'], 422);
    }
}
