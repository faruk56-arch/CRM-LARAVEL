<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $landings = \App\Landing::where('type', 'landing')->where('show', true)->get();

        return $landings;
    }

    public function show($id)
    {
        $landing = \App\Landing::where('id', $id)->first();

        return $landing;
    }

    public function index_facebook()
    {
        $landings = \App\Landing::where('type', 'facebook_ads')->where('show', true)->get();

        return $landings;
    }

    public function all()
    {
        return \App\Landing::where('show', true)->get();
    }

}
