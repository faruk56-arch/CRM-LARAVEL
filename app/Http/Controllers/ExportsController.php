<?php

namespace App\Http\Controllers;

use App\Exports;
use App\Http\Requests\GenerateBigExport;
use App\Jobs\CustomExport;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ExportsController extends Controller
{
    public function get ($token) {
        try {
            $export = Exports::where('token', $token)->first();

            if (!$export)
                return response()->json(['message' => 'export_token_not_found'], 404);

            $path = 'leads/' . $export->filename;
            $name = basename($path);

            if (!Storage::get($path)) {
                return response()->json(['message' => 'export_not_found'], 404);
            }

            $file = Storage::get($path);
            $type = Storage::mimeType($path);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

            return response($file)
                ->header('Content-Type', Storage::mimeType($path))
                ->header('Content-Description', 'File Transfer')
                ->header('Content-Disposition', "attachment; filename={$name}")
                ->header('Filename', $name);

            return $response;
        } catch (\Exception $e) {
            return "Fichier généré sur une ancienne version de la plateforme, il n'est plus disponible.";
        }
    }

    public function index(Request $request) {
        $landing_id = $request->input('landing_id');
        $user_id = $request->input('user_id');
        $created_at = $request->input('created_at');
        $for_customer = $request->input('for_customer');

        $total_leads = 0;

        if (!$landing_id || count($landing_id) == 0) {
            $exports =  \App\Exports::where('deleted_at', null);
        } else {
            $exports = \App\Exports::whereHas('landings', function ($query) use($landing_id) {
                $query->whereIn('landing_id', $landing_id);
            });
        }

        $exports = $exports->where('user_id', '!=', 0);
        if ($user_id) {
            $exports = $exports->where('user_id', $user_id);
        }

        if ($created_at) {
            $tmp_created = explode('<>', $request->input('created_at'));
            if ($tmp_created) {
                $exports = $exports->whereBetween('created_at', [$tmp_created[0], $tmp_created[1]]);
            }
        }

        if ($for_customer) {
            $exports = $exports->where('for_customer', $for_customer);
        }

        $exports_list = $exports->orderBy('id', 'desc')->limit(450)->get();

        $array = array();
        $landings = array();
        $users = array();
        foreach (\App\Landing::all() as $lan) {
            $landings[$lan['id']] = $lan['name'];
        }
        foreach (\App\User::all() as $user) {
            $users[$user['id']] = $user['name'];
        }
        foreach ($exports_list as $list) {
            $tmp = $list;
            $name = '';

            if (count($list->landings) == count($landings))
                $name = 'toutes  ';
            else {
                foreach ($list->landings as $n) {
                    $name .= $n->name . ', ';
                }
            }
            $tmp['landing'] = substr($name, 0, -2);
            $tmp['landings_id'] = $list->landings->pluck('id');
            $tmp['for_customer'] = $list->for_customer;

            $total_leads += $list->count;

            unset($list->landings);
            $tmp['user'] = (isset($users[$list['user_id']])) ? $users[$list['user_id']] : '0';
            $tmp['created'] = date('Y-m-d H:i', strtotime($list['created_at']));
            $array[] = $tmp;
        }
        return ['data' => $array, 'users' => $users, 'total_leads' => $total_leads, 'landings' => $landings, 'for_customer' => \App\Exports::select('for_customer')->groupBy('for_customer')->get()];
    }

    public function insert($id) {
        $export = \App\Exports::find($id);
        if (!$export)
            abort(404);

        $data = \App\LandingData::where('export_id', $id)->update(['entry_status' => 'new', 'export_id' => null]);

        return [$data];
    }

    public function generate_big_export(Request $request) {
        dispatch_now(new CustomExport($request->input('landings'), $request->input('start'), $request->input('end'), $request->input('status'), Auth::id()));

        return ['message' => 'La demande d\'export a été effectuée, dès qu\'il sera disponible, il apparaîtra dans la liste ci-dessous.'];
;    }
}
