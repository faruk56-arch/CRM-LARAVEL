<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast\Object_;
use Cocur\Slugify\Slugify;

class FacebookPage extends Model
{
    function __construct() {
        $facebook['access_token'] = 42;
        $facebook['page_id'] = 42;
    }

    static function facebook_endpoint_get($facebook_page_id, $endpoint_page, $pageid = false, $params) {

        $curl = curl_init();

        $facebook = FacebookPage::find($facebook_page_id);
        if (!$facebook)
            return false;

        $endpoint = 'https://graph.facebook.com/v3.2/' . (($pageid) ? $facebook['page_id'] . '/' : '') . $endpoint_page;
        $endpoint .= "?access_token=". $facebook['access_token'];
        $endpoint .= "&limit=1000000000";
        $endpoint .= $params;


        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($curl);
        curl_close($curl);

        return json_decode($return, true);

    }

    static public function syncLeads($form_id, $landing, $facebook_pages_id) {
        $init_forms = FacebookPage::facebook_endpoint_get($facebook_pages_id, $form_id.'/leads', false, "&fields=created_time,id,ad_id,ad_name,form_id,field_data");

        echo "\nSYNC START : $landing->name";
        echo "\n";


        if (isset($init_forms['error'])) {
            return false;
        }
        try {
            return FacebookPage::save_leads($init_forms['data'], $landing);
        } catch (Exception $e) {
            return false;
        }
    }

    static public function save_leads($data, $landing)
    {
        if (!$data || !is_array($data)) {
            echo "Aucun lead";
            return;
        }

        echo count($data);
        echo "\n";

        $leads_landing = \App\LandingData::where('landing_id', $landing->id)->get()->map(function ($el) {
            return $el->fb_lead_id;
        });

        foreach ($data as $key => $value) {
            if ($leads_landing->contains($value['id']))
                continue;

            $tmp_array = array();
            foreach ($value['field_data'] as $val) {
                $tmp_key = FacebookPage::slug($val['name']);
                $tmp_array[$tmp_key] = $val['values'][0];

                try {
                    if (in_array($tmp_key, ['phone', 'mobile', 'tel', 'phone-number', 'numero-de-telephone'])) {
                        $data = LandingData::phone($tmp_array);

                        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                        $number = $phoneUtil->parse($data, "FR");
                        if ($number->getCountryCode() == 33) {
                            $result = $phoneUtil->format($number, \libphonenumber\PhoneNumberFormat::NATIONAL);
                            $tmp_array[$tmp_key] = $result;
                        }
                    }
                } catch (\libphonenumber\NumberParseException $e) {
                    var_dump('error convert number');
                }
            }


            $lead = \App\LandingData::firstOrNew(['fb_lead_id' => $value['id']]);
            $lead->landing_id = $landing->id;
            $lead->fb_lead_id = $value['id'];
            $lead->created_at = \DateTime::createFromFormat(\DateTime::ISO8601, $value['created_time']);
            $lead->visitor = 'facebook';
            if ($lead->id) {
                continue;
            }

            if (in_array($landing->id, [26, 57, 23]) && ($tmp_array['votre-situation'] == 'locataire' || $tmp_array['votre-situation'] == 'locataire_')) {
                $lead->entry_status = 'trashed';
            } else {
                $lead->entry_status = 'new';
            }

            if (in_array($landing->id, [26, 57, 23])) {
                $tmp_array['votre-situation'] = 'propriétaire';
            }
            $lead->data = json_encode($tmp_array);

            $live = \App\Rapport::live($landing, $tmp_array);

            if ($live) {
                $lead->rapport_id = $live;
                $lead->entry_status = 'extracted';
                $lead->save();
                $result = \App\Rapport::export($lead, $landing, $live);
                if (!$result) {
                    $lead->rapport_id = 0;
                    $lead->entry_status = 'new';
                }
            } else {
                $lead->entry_status = 'new';
            }
            $lead->save();

        }
        return true;
    }

    static public function syncForm($facebook_page_id) {

        $forms = FacebookPage::facebook_endpoint_get($facebook_page_id, 'leadgen_forms', true, null);

        if (isset($forms['error'])) {
            var_dump($forms);
            return false;
        }

        try {
            $forms = $forms['data'];

            foreach ($forms as $key => $value) {

                if ($value['status'] == 'ACTIVE') {
                    $tmp_landing = Landing::where('source', $value['id'])->first();

                    if (!$tmp_landing) {
                        $new = new Landing;
                        $new->facebook_pages_id = $facebook_page_id;
                        $new->source = $value['id'];
                        $new->type = 'facebook_ads';
                        $new->name = $value['name'];
                        $new->rules = json_encode(array());
                        $new->forms = json_encode(array());
                        $new->save();
                    }
                }

            }
            var_dump(['good']);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    static public function slug($key) {
        $slugify = new Slugify();
        return $slugify->slugify($key);
    }

}
