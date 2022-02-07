<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class User extends Controller
{
    public function users() {
        return \App\User::all();
    }

    public function delete($id) {
        \App\User::destroy($id);
        return 'deleted';
    }

    public function admin($id) {
        $user = \App\User::where('id', $id)->first();
        if (!$user)
            abort(404);
        $user->admin = !$user->admin;
        return [$user->save()];
    }

}
