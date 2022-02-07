<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mail;
use Illuminate\Support\Facades\Cache;

class Automation extends Controller
{

    public  function  facebook() {
        $landings = \App\Landing::where('type', 'facebook_ads')->get();
        foreach ($landings as $landing) {
            \App\FacebookPage::syncLeads($landing->source, $landing->id, $landing->facebook_pages_id);
        }

        $facebook_page = \App\FacebookPage::all();
        foreach ($facebook_page as $page) {
            \App\FacebookPage::syncForm($page->id);

        }
        return ['status' => 'ok'];
    }


        \App\Rapport::createRapportAtMidnight();


    public function sendEmailMidnight() {

        Cache::flush();
        if (date('H') != 00 || date('H') != 0) {
            abort(404);
        }
        $facebook_page = \App\FacebookPage::all();
        foreach ($facebook_page as $page) {
            \App\FacebookPage::syncForm($page->id);
        }
        \App\Rapport::createRapportAtMidnight();

        $array_landing = array();
        $landings = \App\Landing::all()->sortByDesc("type");

        foreach ($landings as $landing) {
            $array_landing[$landing['id']] = array(
                'name' => $landing['name'],
                'source' => $landing['source'],
                'type' => ($landing['type'] == 'facebook_ads' ? 'Formulaire facebook' : 'Landing'),
                'data' => array(
                    'total' => \App\LandingData::where('landing_id', $landing['id'])->whereDate('created_at', Carbon::yesterday())->count(),
                    'new' => \App\LandingData::where('landing_id', $landing['id'])->whereDate('created_at', Carbon::yesterday())->where('entry_status', 'new')->count(),
                    'archived' => \App\LandingData::where('landing_id', $landing['id'])->whereDate('created_at', Carbon::yesterday())->where('entry_status', 'archived')->count(),
                    'trashed' => \App\LandingData::where('landing_id', $landing['id'])->whereDate('created_at', Carbon::yesterday())->where('entry_status', 'trashed')->count(),
                    'extracted' => \App\LandingData::where('landing_id', $landing['id'])->whereDate('created_at', Carbon::yesterday())->where('entry_status', 'extracted')->count()
                )
            );
        }

        Mail::send('emails.midnights', array('landings' => $array_landing, 'date' => Carbon::yesterday()->format('d-m-Y')), function($message)
        {
            $message->from(config('mail.username'), config('mail.name'));
            if (strlen(config('mail.midnightsmail')) > 2 && strlen(config('mail.midnightscc')) > 2) {
                $message->to(config('mail.midnightsmail'))->cc(config('mail.midnightscc'));
            } else {
                $message->to(config('mail.midnightsmail'));
            }
            $message->subject('Rapport journalier (' . Carbon::yesterday()->format('d-m-Y')  . ') de la plateforme leads');
        });
    }
    //
}