<?php

namespace App\Http\Controllers;

use PDF;
use Auth;
use Excel;
use Storage;
use App\Landing;
use Carbon\Carbon;
use App\LandingData;
use App\Exports\LeadsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\LandingDataResource;
use App\Http\Resources\LandingDataCollection;

class LandingDataController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $landing_id)
    {

        if (!in_array($landing_id, [11, 12, 13])) {
            $spamSearch = \App\LandingData::where('visitor', $request->ip())->where('created_at', '>', Carbon::now()->subMinutes(10)->toDateTimeString());

            if ($spamSearch->count() > 4) {
                $spamSearch->update(['entry_status' => 'trashed']);
                return response()->json(['message' => ' ressource not found'], 404);
            }
        }

        $landing = \App\Landing::find($landing_id);

        if ($landing && $landing['type'] == 'landing') {
            $validate_form = json_decode($landing->forms, true);

            $validates = Validator::make($request->all(), $validate_form);


            if ($validates->fails())
                return response()->json(['message' => $validates->failed()], 422);

            foreach ($request->all() as $key => $value) {
                if (!isset($validate_form[$key]))
                    return response()->json(['message' => 'could_not_validate_data'], 422);
            }

            if ($validates) {
                $rules = json_decode($landing->rules, true);
                $datas = \App\LandingData::where('landing_id', $landing_id)->get();
                foreach ($datas as $value) {
                    $tmp = json_decode($value['data'], true);

                    if ($tmp) {
                        foreach ($rules['unique'] as $val) {
                            if (isset($val)) {

                                if ($tmp[$val] == $request->input($val))
                                    return response()->json(['message' => 'invalid data'], 422);
                            }
                        }
                    }
                }

                $data = new \App\LandingData();
                $data->landing_id = $landing_id;
                $data->visitor = $request->ip();
                $data->fb_lead_id = 0;
                $data->rapport_id = 0;
                $data->entry_status = 'new';
                $data_customer = $request->all();
                if ($landing->auto_trash == true) {
                    if ((isset($data_customer['own_state']) && $data_customer['own_state'] == 'Locataire') || (isset($data_customer['type_habitation']) && $data_customer['type_habitation'] == 'Appartement')) {
                        $data->entry_status = 'trashed';
                    }
                }
                $data->data = json_encode($data_customer);

                // LIVE

                if ($data->entry_status == 'new') {
                    $live = \App\Rapport::live($landing, $data_customer);

                    if ($live) {
                        $data->rapport_id = $live;
                        $data->entry_status = 'extracted';
                        $data->save();

                        $result = \App\Rapport::export($data, $landing, $live);
                        if (!$result) {
                            $data->rapport_id = 0;
                            $data->entry_status = 'new';
                        }
                    }
                }

                // END LIVE

                $data->save();
                return response()->json(['message' => 'saved'], 201);
            }
        } else
            return response()->json(['message' => 'could_not_validate_data'], 422);
    }

    public function showDep(Request $request, $landing_id, $filter, $date_1, $date_2, $zone_climatique)
    {
        if (!($zone_climatique == 'true' || $zone_climatique == 'false'))
            return response()->json(['message' => 'no_lead_entry'], 404);

        $landinds_ids = explode(',', $landing_id);
        $landing = \App\Landing::whereIn('id', $landinds_ids)->first();
        if (!$landing)
            return response()->json(['message' => 'unable_landing'], 422);

        if ($filter == 'convertis') {
            $date_1 = date('Y-m-d', strtotime($date_1) - 86400);
            $date_2 = date('Y-m-d', strtotime($date_2) - 86400);
            $landing_datas = \App\LandingData::whereIn('landing_id', $landinds_ids)->where(['convert' => 'true'])->whereDate('created_at', '>=', $date_1)->whereDate('created_at', '<=', $date_2)->get();
        } else if ($filter == 'stock')
            $landing_datas = \App\LandingData::whereIn('landing_id', $landinds_ids)->where('entry_status', 'new')->whereDate('created_at', '>=', $date_1)->whereDate('created_at', '<=', $date_2)->get();
        else if ($filter == 'production_extracted')
            $landing_datas = \App\LandingData::whereIn('landing_id', $landinds_ids)->where('entry_status', 'extracted')->whereDate('created_at', '>=', $date_1)->whereDate('created_at', '<=', $date_2)->get();
        else
            $landing_datas = \App\LandingData::whereIn('landing_id', $landinds_ids)->whereNull('convert')->whereDate('created_at', '>=', $date_1)->whereDate('created_at', '<=', $date_2)->get();

        if (!$landing_datas->all() || empty($landing_datas->all()))
            return response()->json(['message' => 'no_lead_entry'], 404);


        $total_lead = array('taboola' => 0, 'facebook' => 0, 'google' => 0, 'autre' => 0, 'total' => 0, 'sms' => 0, 'email' => 0, 'outbrain' => 0, 'internal' => 0);
        $data = array();
        $key = false;
        $tmp = json_decode($landing_datas[0]['data'], true);
        $test_data = ['zip_code', 'zip', 'post_code', 'post-code', 'zip-code', 'code-postal'];
        foreach ($test_data as $test) {
            if (array_key_exists($test, $tmp))
                $key = $test;
        }
        if (!$key)
            return response()->json(['message' => 'no_key_zip_found'], 404);

        foreach ($landing_datas as $value) {
            $tmp = json_decode($value['data'], true);

            if (isset($tmp[$key])) {
                $tmp_zip = substr($tmp[$key], 0, 2);
                $total_lead['total'] += 1;
                if ($zone_climatique == 'true')
                    $tmp_zip = \App\LandingData::zone_france($tmp);

                if (!isset($data[$tmp_zip])) {
                    $facebook = (!isset($tmp['utm_source']) || $tmp['utm_source'] == 'facbook') ? 1 : 0;
                    $taboola = (isset($tmp['utm_source']) && $tmp['utm_source'] == 'taboola') ? 1 : 0;
                    $google = (isset($tmp['utm_source']) && $tmp['utm_source'] == 'google') ? 1 : 0;
                    $internal = (isset($tmp['utm_source']) && $tmp['utm_source'] == 'internal') ? 1 : 0;
                    $autre = (isset($tmp['utm_source']) && empty($tmp['utm_source'])) ? 1 : 0;

                    $sms = (isset($tmp['utm_source']) && $tmp['utm_source'] == 'sms') ? 1 : 0;
                    $email = (isset($tmp['utm_source']) && $tmp['utm_source'] == 'email') ? 1 : 0;
                    $outbrain = ((isset($tmp['utm_source']) && $tmp['utm_source'] == 'outbrain') || (isset($tmp['utm_medium']) && $tmp['utm_medium'] == 'outbrain')) ? 1 : 0;

                    $total_lead['facebook'] += $facebook;
                    $total_lead['taboola'] += $taboola;
                    $total_lead['google'] += $google;
                    $total_lead['autre'] += $autre;
                    $total_lead['sms'] += $sms;
                    $total_lead['internal'] += $internal;
                    $total_lead['email'] += $email;
                    $total_lead['outbrain'] += $outbrain;
                    $arr = array(
                        'value' => 1,
                        'facebook' => $facebook,
                        'google' => $google,
                        'taboola' => $taboola,
                        'sms' => $sms,
                        'email' => $email,
                        'outbrain' => $outbrain,
                        'internal' => $internal,
                        'autre' => $autre,
                    );

                    $tmp_arr = [];
                    if ($zone_climatique == 'true')
                        $tmp_arr['zone_climatique'] = intval($tmp_zip);
                    else
                        $tmp_arr['departement'] = intval($tmp_zip);

                    $data[intval($tmp_zip)] = array_merge($tmp_arr, $arr);
                } else {
                    $data[intval($tmp_zip)]['value'] += 1;

                    if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'google') {
                        $data[$tmp_zip]['google'] += 1;
                        $total_lead['google'] += 1;
                    } else if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'taboola') {
                        $data[$tmp_zip]['taboola'] += 1;
                        $total_lead['taboola'] += 1;
                    } else if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'internal') {
                        $data[$tmp_zip]['internal'] += 1;
                        $total_lead['internal'] += 1;
                    } else if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'sms') {
                        $data[$tmp_zip]['sms'] += 1;
                        $total_lead['sms'] += 1;
                    } else if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'email') {
                        $data[$tmp_zip]['email'] += 1;
                        $total_lead['email'] += 1;
                    } else if ((isset($tmp['utm_source']) && $tmp['utm_source'] == 'outbrain') || (isset($tmp['utm_medium']) && $tmp['utm_medium'] == 'outbrain')) {
                        $data[$tmp_zip]['outbrain'] += 1;
                        $total_lead['outbrain'] += 1;
                    } else if (!isset($tmp['utm_source']) || $tmp['utm_source'] == 'facbook') {
                        $data[$tmp_zip]['facebook'] += 1;
                        $total_lead['facebook'] += 1;
                    } else {
                        $data[$tmp_zip]['autre'] += 1;
                        $total_lead['autre'] += 1;
                    }
                }
            } else {

                if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'google') {
                    $total_lead['google'] += 1;
                } else if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'taboola') {
                    $total_lead['taboola'] += 1;
                } else if (!isset($tmp['utm_source']) || $tmp['utm_source'] == 'facbook') {
                    $total_lead['facebook'] += 1;
                } else {
                    $total_lead['autre'] += 1;
                }
            }
        }

        usort($data, function ($a, $b) {
            if ($a['value'] == $b['value']) {
                return 0;
            }
            return ($a['value'] < $b['value']) ? 1 : -1;
        });

        $data[] = array(
            'departement' => 'Total',
            'value' => $total_lead['total'],
            'facebook' => $total_lead['facebook'],
            'taboola' => $total_lead['taboola'],
            'google' => $total_lead['google'],
            'sms' => $total_lead['sms'],
            'email' => $total_lead['email'],
            'outbrain' => $total_lead['outbrain'],
            'internal' => $total_lead['internal'],
            'autre' => $total_lead['autre']
        );

        return $data;
    }

    public function show(Request $request, $landing_id)
    {
        $landinds_ids = explode(',', $landing_id);
        $landings = \App\Landing::whereIn('id', $landinds_ids)->get();
        if ($landings->count() <= 0)
            return response()->json(['message' => 'unable_landing'], 422);

        $limit = $request->input('_limit');
        if (empty($limit))
            $limit = 400;

        $landing_datas =  \App\LandingData::whereIn('landing_id', $landinds_ids)->orderBy('updated_at', 'desc');


        if ($request->input('created_at')) {
            $tmp_created = explode('<>', $request->input('created_at'));
            $landing_datas = $landing_datas->whereBetween('created_at', [$tmp_created[0], $tmp_created[1]]);
        }

        foreach (\App\LandingData::search_engine($request->all()) as $item) {
            $landing_datas = $landing_datas->whereRaw($item);
        }


        $import_from = \App\LandingData::select('import_from')->groupBy('import_from')->get();
        $import_from->push(['import_from' => 'facbook']);
        $import_from->push(['import_from' => 'taboola']);
        $import_from->push(['import_from' => 'google']);
        $import_from->push(['import_from' => 'internal']);
        $import_from->push(['import_from' => 'sms']);
        $import_from->push(['import_from' => 'email']);
        $import_from->push(['import_from' => 'outbrain']);


        return ['keys' => \App\Landing::get_keys($landings), 'import_from' => $import_from->toArray(), 'for_customer' => \App\Exports::select('for_customer')->groupBy('for_customer')->get(), 'data' => new LandingDataCollection($landing_datas->limit($limit)->get())];
    }

    public function shoeeew(Request $request, $landing_id)
    {
        $landinds_ids = explode(',', $landing_id);
        $landings = \App\Landing::whereIn('id', $landinds_ids)->get();
        if ($landings->count() <= 0)
            return response()->json(['message' => 'unable_landing'], 422);
        $limit = $request->input('_limit');
        if (empty($limit))
            $limit = 400;
        $data = array();
        if ($request->input('created_at')) {
            $tmp_created = explode('<>', $request->input('created_at'));
            $landing_datas = \App\LandingData::whereIn('landing_id', $landinds_ids)->where('entry_status', $request->input('entry_status'))->whereBetween('created_at', [$tmp_created[0], $tmp_created[1]])->orderBy('updated_at', 'desc')->take($limit + 6000)->get();
        } else {
            $landing_datas = \App\LandingData::whereIn('landing_id', $landinds_ids)->where('entry_status', $request->input('entry_status'))->orderBy('updated_at', 'desc')->take($limit + 6000)->get();
        }
        if (!$landing_datas->all() || empty($landing_datas->all()))
            return response()->json(['message' => 'no_lead_entry'], 404);
        foreach ($landing_datas as $key => $value) {
            $tmp = json_decode($value['data'], true);
            $tmp_utm = (isset($tmp['utm_source'])) ? $tmp['utm_source'] : null;
            if ($tmp_utm == null && !empty($value['fb_lead_id'])) {
                $tmp_utm = 'facebook';
            }
            $utm = array();
            if ($value->landing->type != 'facebook_ads') {
                $utm = array('utm_source' => $tmp_utm, 'utm_medium' => ((isset($tmp['utm_medium'])) ? $tmp['utm_medium'] : null), 'utm_campaign' => ((isset($tmp['utm_campaign'])) ? $tmp['utm_campaign'] : null));
            }
            $zone_climatique = [];
            if ($value->landing->zone_climatique)
                $zone_climatique['zone_climatique'] = \App\LandingData::zone_france($tmp);
            $data[] = array_merge(array('id' => $value['id'], 'landing'  => $value->landing->name, 'convert'  => $value->convert, 'entry_status' => $value['entry_status'], 'created_at' => date('Y-m-d H:i', strtotime($value['created_at'])), 'updated_at' => date('Y-m-d H:i', strtotime($value['updated_at'])), 'visitor' => $value['visitor']), $tmp, $zone_climatique, $utm);
        }
        $new_array = $data;
        $requests = $request->all();
        unset($requests['created_at']);
        foreach ($requests as $search_key => $search_value) {
            if ($search_key != '_limit') {
                $tmp = $new_array;
                $new_array = array();
                foreach ($tmp as $val) {
                    if (empty($search_value) || !isset($val[$search_key]))
                        $new_array[] = $val;
                    else {
                        (string)$search_value = str_replace(',', ';', (string)$search_value);
                        (string)$search_value = str_replace('/', ';', (string)$search_value);
                        $search = explode(';', (string)$search_value);
                        foreach ($search as $test_search) {
                            if (empty($test_search))
                                continue;
                            $r = strpos(strtoupper((string)$val[$search_key]), strtoupper($test_search));
                            if ($r !== false) {
                                $included = in_array($search_key, ['zip_code', 'zip', 'post_code', 'post-code', 'zip-code', 'code-postal']);
                                if (($included == true && $r == 0) || $included == false) {
                                    $new_array[] = $val;
                                }
                            }
                        }
                    }
                }
            }
        }

        return ['keys' => \App\Landing::get_keys($landings), 'data' => array_slice($new_array, 0, $limit)];
    }


    public function update(Request $request)
    {
        $validates = Validator::make($request->all(), [
            'leads' => 'required',
            'entry_status' => 'in:new,archived,extracted,trashed'
        ]);


        if ($validates->fails())
            return response()->json(['message' => 'invalid_type'], 422);

        \App\LandingData::whereIn('id', $request->input('leads'))->update(array('entry_status' => $request->input('entry_status')));
        return response()->json(['message' => 'entry_status_updated_to_' . $request->input('entry_status')], 200);
    }

    public function delete(Request $request)
    {
        $validates = Validator::make($request->all(), [
            'leads' => 'required'
        ]);


        if ($validates->fails())
            return response()->json(['message' => 'invalid_type'], 422);

        \App\LandingData::whereIn('id', $request->input('leads'))->delete();
        return response()->json(['message' => 'leads deleted'], 200);
    }

    public function stats()
    {
        $date = new Carbon();

        $today = \App\LandingData::whereDate('updated_at', Carbon::today())->count();
        $week = \App\LandingData::where('updated_at', '>', $date->subWeek())->count();
        $month = \App\LandingData::where('updated_at', '>', $date->subMonth())->count();
        return array('today' => $today, 'week' => $week, 'month' => $month);
    }

    public function cmp($a, $b)
    {
        if ($a['departement'] == $b['departement']) {
            return 0;
        }
        return ($a['departement'] < $b['departement']) ? -1 : 1;
    }


    public function export(Request $request, $landing_id)
    {
        if ($request->input('rapport')) {
            $landing = \App\Landing::where('id', 1)->first();
            $slug = str_slug('Rapport', '-');
        } else {
            $landing = \App\Landing::where('id', $landing_id)->first();
            if (!$landing)
                return response()->json(['message' => 'unable_landing'], 422);
            $slug = str_slug($landing->name, '-');
        }
        $validates = Validator::make($request->all(), array(
            'bookTypee' => 'in:txt,csv,pdf,xlxs',
            'leads' => 'required'
        ));
        if ($validates->fails())
            return response()->json(['message' => $validates->failed()], 422);
        $array = $request->input('leads');
        $array = array_unique($array);
        $filename = $request->input('filename');
        $booktype = $request->input('bookType');
        $date = date('d-m-Y H:i:s');
        $filename = (isset($filename) && !empty($filename)) ? str_slug($filename) : $slug . '-' . count($array);
        $filename = $filename . '--' . str_slug($date, '-');
        if (!is_array($array))
            return response()->json(['message' => 'unable_leads'], 422);

        $leads = [];
        $array_keys_r = [];
        $cnt = 0;
        foreach ($array as $lead) {
            $tmp = \App\LandingData::where('id', $lead)->first();
            if ($tmp) {
                $json = json_decode($tmp['data'], true);
                unset($json['url_presale']);
                unset($json['utm_source']);
                unset($json['utm_medium']);
                unset($json['utm_campaign']);
                $zone_climatique = [];
                if ($landing->zone_climatique)
                    $zone_climatique['zone_climatique'] = \App\LandingData::zone_france($json);
                $leads[] = array_merge(array('id' => $tmp['id'], 'created' => date('Y-m-d H:i', strtotime($tmp['created_at']))), $zone_climatique, $json);
                $array_keys_r = array_merge($array_keys_r, array_keys($leads[$cnt]));
                $cnt++;
            }
        }

        if (!count($leads))
            return response()->json(['message' => 'unable_leads'], 422);
        $array_keys = array_unique($array_keys_r);

        if ($booktype == 'pdf') {
            $pdf = PDF::loadView('pdfview', array('leads' => $leads, 'keys' => $array_keys, 'date' => $date));
            $filename .= '.pdf';
            \Illuminate\Support\Facades\Storage::disk('s3')->put(
                'leads/' . $filename,
                $pdf->output()
            );
        } elseif ($booktype == 'txt' || $booktype == 'csv') {
            $glue = ($booktype == 'txt') ? "\t" : ',';
            $fileText = implode($glue, $array_keys) . "\r\n";
            foreach ($leads as  $lead) {
                foreach ($array_keys as $t_key) {
                    $fileText .= (isset($lead[$t_key])) ? $lead[$t_key] : (($booktype == 'txt') ? "\t" : "");
                    $fileText .= $glue;
                }
                $fileText .= "\r\n";
            }
            $filename .= '.' . $booktype;
            Storage::put('leads/' . $filename, $fileText);
        } else {

            $filename .= '.xlsx';
            Excel::store(new LeadsExport($array_keys, $leads), 'leads/' . $filename);
        }

        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $export = new \App\Exports;
        $export->filename = $filename;
        $export->user_id = ($request->input('rapport')) ? 0 : Auth::id();
        $export->token = $token;
        $export->rapport_id = ($request->input('rapport')) ? $request->input('rapport') : 0;
        $export->count = count($array);
        $export->for_customer = $request->input('for_customer');
        $export->save();
        $export->landings()->attach([$landing->id]);


        \App\LandingData::whereIn('id', $array)->update(array('export_id' => $export->id));

        return ['token' => $token, 'filename' => $filename, 'url' => url('/api/exports/')];
    }

    public function import_excel(Request $request, $id)
    {

        $landing = Landing::where('id', $id)->first();
        if (!$landing)
            abort(403);

        $arr = [];
        $data = collect($request->input('data'));
        foreach ($request->input('columns') as $item) {
            $i = 0;
            $columns = $data->where('column', $item['name'])->first()['data'];
            if (!$columns || !count($columns)) {
            } else {
                foreach ($data->where('column', $item['name'])->first()['data'] as $el) {
                    $arr[$i++][$item['name']] = $el;
                }
            }
        }


        foreach ($arr as $key => $item) {
            foreach (json_decode($landing->forms) as $key_form => $value) {
                if (!isset($arr[$key][$key_form])) {
                    $arr[$key][$key_form] = '';

                    if ($key_form == 'utm_medium' || $key_form == 'utm_source') {
                        $arr[$key][$key_form] = $request->input('form_import_from');
                    }
                }
            }
        }

        $bulkArray = [];

        foreach ($arr as $key => $item) {

            $status = 'new';
            if ($landing->auto_trash == true) {
                if ((isset($item['own_state']) && $item['own_state'] == 'Locataire') || (isset($item['type_habitation']) && $item['type_habitation'] == 'Appartement')) {
                    $status = 'trashed';
                }
            }

            $bulkArray[] = [
                'landing_id' => $landing->id,
                'visitor' => 'import',
                'created_at' => time(),
                'updated_at' => time(),
                'rapport_id' => 0,
                'entry_status' => $status,
                'import_from' => $request->input('form_import_from'),
                'data' => json_encode($item)
            ];
        }

        LandingData::insert($bulkArray);

        return ['ok'];
    }
}
