<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConvertController extends Controller
{
    public function convert(Request $request) {

        $validates = Validator::make($request->all(), [
            'landing' => 'required',
            'leads.*' => 'required',
        ]);


        if ($validates->fails())
            return response()->json(['message' => $validates->failed()], 422);
        if (!($landing = \App\Landing::find($request->input('landing'))))
            return response()->json(['message' => 'landing_not_found'], 422);


        $dataForm = \App\Landing::convertGetFields(json_decode($landing->forms, true));

        $arr = [];
        foreach ($request->input('leads') as $item) {
           if (!( $lead = \App\LandingData::find($item)))
               continue;
           $lead->entry_status = 'converted';
           $lead->save();
           $hour = \DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d", time() - 60 * 60 * 24).' '.date('H:i:s', strtotime($lead->created_at)));

            $landingConvert = json_decode($landing->convert, true);

            if (isset($landingConvert[$lead->landing_id])) {
                if ($landingConvert[$lead->landing_id]['keep_date']) {
                    $hour = $lead->created_at;
                }
            }

           $arr[] = [
               'landing_id' => $landing->id,
               'data' => json_encode(\App\Landing::convertFields($lead->landing_id, json_decode($lead->data, true), $dataForm, $landingConvert)),
               'visitor' => $lead->visitor,
               'entry_status' => 'new',
               'convert' => 'true',
               'fb_lead_id' => 'landing',
               'created_at' => $hour,
               'updated_at' => $hour
           ];

        }

        return [\App\LandingData::insert($arr), $arr];
    }
}
