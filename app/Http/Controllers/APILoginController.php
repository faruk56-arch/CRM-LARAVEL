<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use JWTFactory;
use JWTAuth;
use App\User;
use Illuminate\Support\Facades\Auth;

class APILoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required',
            'code' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);;
        }
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $user = \App\User::where('email', $request->input('email'))->first();
        if (!$user)
            return response()->json(['error' => 'could_not_create_token'], 500);
        $google2fa = app('pragmarx.google2fa');
        if ($google2fa->verifyKey($user['oauth'], $request->input('code'))) {
            return response()->json(compact('token'));
        }
        return response()->json(['error' => 'invalid_credentials'], 401);
    }

    public function me() {
        $user = Auth::user();

        return ['admin' => $user->admin, 'email' => $user->email, 'name' => $user->name];
    }
}
