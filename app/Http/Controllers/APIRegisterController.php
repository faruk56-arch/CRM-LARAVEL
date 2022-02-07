<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;
use Mail;

class APIRegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255|unique:users',
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $password = uniqid();
        $google2fa = app('pragmarx.google2fa');
        $key = $google2fa->generateSecretKey();

        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $request->get('email'),
            $key
        );

        User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'oauth' => $key,
            'password' => bcrypt($password),
        ]);

        \Illuminate\Support\Facades\Mail::send('emails.new_user', array('qr' => $key, 'password' => $password, 'email' => $request->get('email')), function($message) use ($request)
        {
            $message->from(config('mail.username'), config('mail.name'));
            $message->to($request->get('email'));
            $message->subject('Création de votre compte');
        });
        return ['status' => 'created'];
    }
}
