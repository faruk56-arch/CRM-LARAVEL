<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductionResource;
use App\Production;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($category)
    {
        $productions = Production::where('category', $category)->get();
        $selected = $productions->where('selected', true)->map(function($el) {
            return $el->region;
        });

        return ['regions' => $productions, 'group', 'selected' => $selected];
    }


    public function update(Request $request, $category)
    {
        if (!$request->input('departement')) {
            return Production::where('category', $category)->where('selected', true)->update(['selected' => false]);
        }
        Production::where('category', $category)->whereNotIn('region', $request->input('departement'))->update(['selected' => false]);
        return Production::where('category', $category)->whereIn('region', $request->input('departement'))->update(['selected' => true]);
    }

    public function update_text(Request $request, $category, $region)
    {
        return Production::where('category', $category)->where('region', $region)->update(['text' => $request->input('text')]);
    }

    public function get(Request $request) {
        $department = [];
        if (!$request->input('departement')) abort(422);
        if (!$request->input('landings')) abort(422);

        foreach ($request->input('departement') as $item) {
            $explode = explode('-', $item);
            $department[] = $explode[1];
        }

        $landing_datas = \App\LandingData::whereNull('convert');
        if ($request->input('landings'))
            $landing_datas = $landing_datas->whereIn('landing_id', $request->input('landings'));
        if ($request->input('date_range'))
            $landing_datas = $landing_datas->whereDate('created_at', '>=', $request->input('date_range')[0])->whereDate('created_at', '<=', $request->input('date_range')[1]);
        $landing_datas = $landing_datas->get();

        if (!$landing_datas->all() || empty($landing_datas->all()))
            return [
                'sources' => [
                    ['key' => 'Facebook', 'value' => 0],
                    ['key' => 'Taboola', 'value' => 0],
                    ['key' => 'Google', 'value' => 0],
                    ['key' => 'Sms', 'value' => 0],
                    ['key' => 'Email', 'value' => 0],
                    ['key' => 'Outbrain', 'value' => 0],
                    ['key' => 'Internal', 'value' => 0],
                    ['key' => 'Autre', 'value' => 0],
                    ['key' => 'Total', 'value' => 0],
                ],
                'stats' => [
                    ['key' => 'Nombre de locataires', 'value' => 0],
                    ['key' => 'Pourcentage de Gaz/Fioul', 'value' => '0'],
                    ['key' => 'Pourcentage d\'Actif ou de Retraité', 'value' => '0'],
                ]
            ];

        $total_lead = array('taboola' => 0, 'facebook' => 0, 'google' => 0, 'autre' => 0, 'total' => 0, 'sms' => 0, 'email' => 0, 'outbrain' => 0, 'wrong' => 0, 'gaz' => 0,
            'fioul' => 0, 'actif' => 0, 'retraite' => 0, 'internal' => 0);
        $data = array();

        $count = 0;
        foreach ($landing_datas as $value) {
            $tmp = json_decode($value['data'], true);
            $key = false;
            foreach (['zip_code', 'zip', 'post_code', 'post-code', 'zip-code', 'code-postal'] as $el) {
                if (isset($tmp[$el])) {
                    $key = $el;
                }
            }

            if ($key) {
                $tmp_zip = substr($tmp[$key], 0, 2);

                if (in_array($tmp_zip, $department)) {
                    if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'google') {
                        $total_lead['google'] += 1;
                    } else if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'taboola') {
                        $total_lead['taboola'] += 1;
                    } else if (isset($tmp['utm_source']) && $tmp['utm_source'] == 'internal') {
                        $total_lead['internal'] += 1;
                    } else if (!isset($tmp['utm_source']) || $tmp['utm_source'] == 'facbook') {
                        $total_lead['facebook'] += 1;
                    } else if (!isset($tmp['utm_source']) || $tmp['utm_source'] == 'sms') {
                        $total_lead['sms'] += 1;
                    } else if (!isset($tmp['utm_source']) || $tmp['utm_source'] == 'email') {
                        $total_lead['email'] += 1;
                    } else if ((isset($tmp['utm_source']) && $tmp['utm_source'] == 'outbrain') || isset($tmp['utm_medium']) && $tmp['utm_medium'] == 'outbrain') {
                        $total_lead['outbrain'] += 1;
                    } else {
                        $total_lead['autre'] += 1;
                    }
                    $count = $count + 1;
                    $total_lead['total'] += 1;

                    if ($value['entry_status'] == 'trashed')
                        $total_lead['wrong'] += 1;
                    if (str_contains(strtolower($value['data']), 'fioul'))
                        $total_lead['fioul'] += 1;
                    if (str_contains(strtolower($value['data']), 'gaz'))
                        $total_lead['gaz'] += 1;
                    if (str_contains(strtolower($value['data']), 'actif'))
                        $total_lead['actif'] += 1;
                    if (str_contains(strtolower($value['data']), 'retrait'))
                        $total_lead['retraite'] += 1;
                }
            }
        }

        usort($data, function($a, $b) {
            if ($a['value'] == $b['value']) {
                return 0;
            }
            return ($a['value'] < $b['value']) ? 1 : -1;
        });

        $pourcent_gaz = round($total_lead['gaz'] * 100 / $total_lead['total']);
        $pourcent_fioul = round($total_lead['fioul'] * 100 / $total_lead['total']);

        $pourcent_actif = round($total_lead['actif'] * 100 / $total_lead['total']);
        $pourcent_retraite = round($total_lead['retraite'] * 100 / $total_lead['total']);
        return [
            'sources' => [
                ['key' => 'Facebook', 'value' => $total_lead['facebook']],
                ['key' => 'Taboola', 'value' => $total_lead['taboola']],
                ['key' => 'Google', 'value' => $total_lead['google']],
                ['key' => 'Sms', 'value' => $total_lead['sms']],
                ['key' => 'Email', 'value' => $total_lead['email']],
                ['key' => 'Outbrain', 'value' => $total_lead['outbrain']],
                ['key' => 'Internal', 'value' => $total_lead['internal']],
                ['key' => 'Autre', 'value' => $total_lead['autre']],
                ['key' => 'Total', 'value' => $total_lead['total']]
            ],
            'stats' => [
                ['key' => 'Nombre de locataires', 'value' => $total_lead['wrong']],
                ['key' => 'Pourcentage de Gaz/Fioul', 'value' => $pourcent_gaz.'%('.$total_lead['gaz'].') / '.$pourcent_fioul.'% ('.$total_lead['fioul'].')'],
                ['key' => 'Pourcentage d\'Actif ou de Retraité', 'value' => $pourcent_actif.'%('.$total_lead['actif'].') / '.$pourcent_retraite.'% ('.$total_lead['retraite'].')'],
            ]
        ];
    }
}