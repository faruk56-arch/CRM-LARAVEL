<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LeadFb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fb:syncs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Facebook';

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
        $landings = \App\Landing::where('type', 'facebook_ads')->get();
        foreach ($landings as $landing) {
            \App\FacebookPage::syncLeads($landing->source, $landing, $landing->facebook_pages_id);
        }
    }
}
