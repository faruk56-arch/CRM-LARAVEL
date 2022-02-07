<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacebookWebhook extends Controller
{
    public function webhook(Request $request)
    {

        $data = $request->all();
        file_put_contents('ok.text', json_encode($data));
    }
}
