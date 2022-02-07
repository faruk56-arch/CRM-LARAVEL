<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class CustomExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private  $landings;
    private $start;
    private $end;
    private $status;
    private $userId;

    public function __construct($landings, $start, $end, $status, $userId)
    {
        $this->landings = $landings;
        $this->start = $start;
        $this->end = $end;
        $this->userId = $userId;
        $this->status = $status;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $data = [];
        if (!is_array($this->landings) || count($this->landings) == 0)
            $landings = \App\Landing::all();
        else
            $landings = \App\Landing::whereIn('id', $this->landings)->get();

        $keys = [
            'landing',
            'add_at', 'status', 'lastname', 'firstname', 'tel', 'email', 'zip', 'utm_medium', 'utm_source', 'utm_campaign', 'utm_term'
        ];

        foreach ($landings as $landing) {
            $dt = \App\LandingData::where('landing_id', $landing->id);
            if ($this->status)
                $dt = $dt->whereIn('entry_status', $this->status);
            if (!empty($this->start) && !empty($this->end))
                $dt = $dt->where('created_at', '>=', $this->start.'00:00:00')->where('created_at', '<=', $this->end.'23:59:59');

            $dt = $dt->orderBy('id', 'desc')->get();

            foreach ($dt as $item) {
                $cast = json_decode($item['data'], true);

                $sup = [];
                foreach ($cast as $key => $n) {
                    if (!in_array($key, $keys))
                        $keys[] = $key;

                    $sup[$key] = utf8_decode(str_replace(';', ' ', $n));
                }

                $data[] = array_merge([
                    'landing' => utf8_decode($landing->name),
                    'add_at' => $item->created_at,
                    'status' => $item->entry_status,
                    'lastname' => utf8_decode(\App\LandingData::lastname($cast)),
                    'firstname' => utf8_decode(\App\LandingData::firstname($cast)),
                    'tel' => utf8_decode(\App\LandingData::phone($cast)),
                    'email' => utf8_decode(\App\LandingData::email($cast)),
                    'zip' => \App\LandingData::zip_code($cast),
                    'utm_medium' => (isset($cast['utm_medium'])) ? $cast['utm_medium'] : '',
                    'utm_source' =>  (isset($cast['utm_source'])) ? $cast['utm_source'] : '',
                    'utm_term' => (isset($cast['utm_campaign'])) ? $cast['utm_campaign'] : '',
                    'utm_campaign' => (isset($cast['utm_campaign'])) ? $cast['utm_campaign'] : ''
                ],
                    $sup
                );
            }
        }


        $file_name = 'export-global-'.uniqid().'.csv';
        $file_path = '/tmp/'.$file_name;
        $keys = array_unique($keys);

        $fp = fopen($file_path, 'w');
        fputcsv($fp, array_unique($keys), ';');


        foreach ($data as $i) {
            $tmp= [];
            foreach ($keys as $k) {
                if (isset($i[$k]))
                    $tmp[$k] = $i[$k];
                else
                    $tmp[$k] = '';
            }
            fputcsv($fp, $tmp, ';');
        }

        fclose($fp);

        Storage::disk('s3')->put('leads/'.$file_name, file_get_contents($file_path));

        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $export = new \App\Exports;
        $export->filename = $file_name;
        $export->user_id = $this->userId;
        $export->token = $token;
        $export->rapport_id = 0;
        $export->count = count($data);
        $export->big_export = true;
        $export->save();
        $export->landings()->attach($landings->pluck('id'));
    }
}
