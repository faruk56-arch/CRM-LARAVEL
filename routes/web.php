<?php

Route::group(['domain' => 'crm.leads-esygeco.xyz'], function () {
    Route::get('/', function() {
        return view('crm');
    });
});

Route::group(['domain' => 'staging-crm.leads-esygeco.xyz'], function () {
    Route::get('/', function() {
        return view('crm');
    });
});

Route::get('/', function() {
    return 'OK';
});

Route::get('/leadup-crm', function() {
    return view('crm');
});
