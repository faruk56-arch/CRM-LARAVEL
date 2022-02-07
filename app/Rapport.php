<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use PDF;
use App\Exports\LeadsExport;
use Auth;
use Excel;
use Illuminate\Support\Facades\Response;
use Storage;
use Mail;

class Rapport extends Model
{
    protected $fillable = [
        'customer_id',
        'leads_number',
        'customer_rule_id',
        'date',
        'zip',
        'hours'
    ];

    protected $casts = ['zip' => 'array', 'hours' => 'array'];

    public static function live($landing, $data) {
        if (($rules = $landing->customersRules->where(strtolower(date('l')), 1)->where('status', true))) {
            $rules = $rules->shuffle();

            $date_now = str_replace(':', '', date('H:i'));

            foreach ($rules as $rule) {

                $search_rapport = \App\Rapport::where('status', true)->where('customer_rule_id', $rule->id)->where('date', date('Y-m-d'))->first();

                if (!$search_rapport) {
                    $search_rapport = \App\Rapport::create([
                        'customer_id' => $rule->customer_id,
                        'customer_rule_id' => $rule->id,
                        'zip' => $rule->zip,
                        'hours' => $rule->hours,
                        'date' => date('Y-m-d'),
                        'leads_number' => $rule->leads_number
                    ]);
                }

                $count_exports = \App\LandingData::where('rapport_id', $search_rapport->id)->whereDate('created_at', Carbon::today())->count();

                if ($search_rapport->leads_number <= $count_exports) {
                    continue;
                }

                if ($search_rapport->hours && count($search_rapport->hours) == 2) {
                    $date_start = str_replace(':', '', $search_rapport->hours[0]);
                    $date_end = str_replace(':', '', $search_rapport->hours[1]);

//                    var_dump($date_now);
//                    var_dump($date_start);
//                    var_dump($date_end);
                    if (!($date_now >= $date_start && $date_now <= $date_end))
                        continue;
                }

                if (count($search_rapport->zip) != 0) {
                    $post_code_key = null;
                    if (isset($data['zip'])) {
                        $post_code_key = 'zip';
                    } else if (isset($data['post-code'])) {
                        $post_code_key = 'post-code';
                    }

                    if ($post_code_key) {
                        $search_zip = false;
                        foreach ($search_rapport->zip as $i) {
                            $r = strpos((string)$data[$post_code_key], (string)$i);
                            if ($r == 0 && $r !== false) {
                                $search_zip = $i;
                            }
                        }
                        if ($search_zip === false)
                            continue;

                    }
                }


                return $search_rapport->id;
            }
            return false;
        }

        return false;
    }

    public static function createRapportAtMidnight() {
        $rules = \App\CustomersRule::where('status', true)->get();

        foreach ($rules as $rule) {

            if ($rule[strtolower(date('l'))]) {
                $test = \App\Rapport::firstOrNew(array(
                    'customer_rule_id' => $rule->id,
                    'date' => date('Y-m-d'),
                ), array(
                    'customer_id' => $rule->customer_id,
                    'leads_number' => $rule->leads_number,
                    'zip' => $rule->zip,
                    'hours' => $rule->hours
                ));

                $test->save();
            }
        }
    }

    public static function export($data, $landing, $rapport_id) {
        if (!$data || !$landing || !$rapport_id)
            return false;
        $leads = array();
        $json = json_decode($data['data'], true);
        unset($json['url_presale']);
        unset($json['utm_source']);
        unset($json['utm_medium']);
        unset($json['utm_campaign']);
        $zone_climatique = [];
        if ($landing->zone_climatique)
            $zone_climatique['zone_climatique'] = \App\LandingData::zone_france($json);
        $leads[] = array_merge(array('id' => $data['id'], 'created' => date('Y-m-d H:i', strtotime($data['created_at']))), $zone_climatique, $json);
        $array_keys = array_keys($leads[0]);
        $file_id = sha1(uniqid());
        $date = date('d-m-Y H:i:s');

        $path = '/leads/'.$file_id;
        $pdf = PDF::loadView('pdfview', array('leads' => $leads, 'keys' => $array_keys, 'date' => $date));
        \Illuminate\Support\Facades\Storage::disk('s3')->put(
            $path.'.pdf',
            $pdf->output()
        );
        Excel::store(new LeadsExport($array_keys, $leads), 'leads/'.$file_id.'.xlsx');

        $rapport = \App\Rapport::where('id', $rapport_id)->first();


        Mail::send('emails.leads', array('data' => $data), function($message) use($data, $rapport, $landing, $path)
        {
            $message->from(config('mail.username'), config('mail.name'));
            $message->to($rapport->customer->email);
            $message->subject('Nouveau lead ('. $data->id .') - '. $landing->sub_title);
            $name = $landing->sub_title . '('. $data->id .')';
            $message->attach($path.'.pdf', ['as' => $name.'.pdf', 'mime' => 'application/pdf']);
            $message->attach($path.'.xlsx', ['as' => $name.'.xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        });

        return true;
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer')->withDefault();
    }
}
