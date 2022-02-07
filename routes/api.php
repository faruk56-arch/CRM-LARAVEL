<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('auth', 'APILoginController@login');

Route::post('/landings/data/{landing_id}', 'LandingDataController@create');
Route::middleware('jwt.auth')->get('/stats', 'LandingDataController@stats');
Route::middleware('jwt.auth')->get('/landings', 'LandingController@index');
Route::middleware('jwt.auth')->get('/landings/{id}', 'LandingController@show');
Route::middleware('jwt.auth')->post('/landings/{id}/import/excel', 'LandingDataController@import_excel');

Route::middleware('jwt.auth')->get('/landings_facebook', 'LandingController@index_facebook');
Route::middleware('jwt.auth')->get('/landings/data/{landing_id}', 'LandingDataController@show');
Route::middleware('jwt.auth')->get('/landings/data/departement/{landing_id}/{filter}/{date_1}/{date_2}/{zone_climatique}', 'LandingDataController@showDep');
Route::middleware('jwt.auth')->post('/landings/data', 'LandingDataController@update');
Route::middleware('jwt.auth')->delete('/landings/data', 'LandingDataController@delete');
Route::middleware('jwt.auth')->get('/facebook/sync_forms', 'FacebookPage@syncForm');
Route::middleware('jwt.auth')->post('/landings/data/{landing_id}/export', 'LandingDataController@export');
Route::middleware('jwt.auth')->get('/me', 'APILoginController@me');
Route::middleware('jwt.auth')->post('/exports/insert/{id}', 'ExportsController@insert');
Route::middleware('jwt.auth', 'admin')->post('/landings/convert/leads', 'ConvertController@convert');

Route::middleware('jwt.auth', 'admin')->get('/taboola/campaigns/{account_name}', 'TaboolaController@campaigns');
Route::middleware('jwt.auth', 'admin')->get('/taboola/campaigns/{account_name}/{campaign_id}/codes', 'TaboolaController@postal_codes');
Route::middleware('jwt.auth', 'admin')->post('/taboola/campaigns/{account_name}/{campaign_id}/codes', 'TaboolaController@post');
Route::middleware('jwt.auth', 'admin')->get('/taboola/accounts', 'TaboolaController@accounts');

Route::middleware('jwt.auth', 'admin')->get('/taboola/test', 'TaboolaController@test');

Route::middleware('jwt.auth', 'admin')->get('/users', 'User@users');
Route::middleware('jwt.auth', 'admin')->post('/users/admin/{id}', 'User@admin');
Route::middleware('jwt.auth', 'admin')->post('auth/register', 'APIRegisterController@register');
Route::middleware('jwt.auth', 'admin')->delete('/users/{id}', 'User@delete');
//Route::middleware('jwt.auth', 'admin')->get('/exports', 'ExportsController@index');
Route::middleware('jwt.auth', 'admin')->get('/customers', 'CustomerController@index');
Route::middleware('jwt.auth', 'admin')->post('/customers', 'CustomerController@create');
Route::middleware('jwt.auth', 'admin')->get('all/landings', 'LandingController@all');
Route::middleware('jwt.auth', 'admin')->post('/customers/rules', 'CustomersRuleController@create');
Route::middleware('jwt.auth', 'admin')->delete('/customers/rules/{id}', 'CustomersRuleController@delete');
Route::middleware('jwt.auth', 'admin')->get('/customers/rules', 'CustomersRuleController@index');
Route::middleware('jwt.auth', 'admin')->get('/rapports/rules/{rule_id}', 'RapportController@index');


Route::middleware('jwt.auth', 'admin')->get('/productions/{category}', 'ProductionController@index');
Route::middleware('jwt.auth', 'admin')->post('/productions/{category}/regions/data', 'ProductionController@get');
Route::middleware('jwt.auth', 'admin')->post('/productions/{category}/change_color', 'ProductionController@update');
Route::middleware('jwt.auth', 'admin')->post('/productions/{category}/{region}/change_text', 'ProductionController@update_text');
Route::middleware('jwt.auth', 'admin')->get('/exports', 'ExportsController@index');
Route::middleware('jwt.auth', 'admin')->post('/generate_big_export', 'ExportsController@generate_big_export');


Route::get('/exports/{token}/{filename}', 'ExportsController@get');


