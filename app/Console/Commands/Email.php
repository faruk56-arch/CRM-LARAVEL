<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Mail;
use Illuminate\Support\Facades\Cache;

class Email extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Cache::flush();
        $facebook_page = \App\FacebookPage::all();
        foreach ($facebook_page as $page) {
            var_dump($page->name);
            var_dump(\App\FacebookPage::syncForm($page->id));
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


        var_dump(Mail::send('emails.midnights', array('landings' => $array_landing, 'date' => Carbon::yesterday()->format('d-m-Y')), function($message)
        {
            $message->from(config('mail.username'), config('mail.name'));
            if (strlen(config('mail.midnightsmail')) > 2 && strlen(config('mail.midnightscc')) > 2) {
                $message->to(config('mail.midnightsmail'))->cc(config('mail.midnightscc'));
            } else {
                $message->to(config('mail.midnightsmail'));
            }
//            $message->to('antoine@hono-agency.fr');
            $message->subject('Rapport journalier (' . Carbon::yesterday()->format('d-m-Y')  . ') de la plateforme leads');
        }));
    }
}
