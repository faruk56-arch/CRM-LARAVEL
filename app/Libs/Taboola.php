<?php

namespace App\Libs;

use Illuminate\Support\Facades\Cache;

class Taboola {

    public function __construct() {
        $this->access_token = $this->get_access_token();
        $this->endpoint = 'https://backstage.taboola.com/backstage/api/1.0/';

    }

    public function get_access_token() {
        $data = [
            'client_id' => '1e1fe4994a5947e183fb61108698e599',
            'client_secret' => '2ee620b0336e43338cdf6dc220658c20',
            'grant_type' => 'client_credentials'
        ];

        $cache = Cache::get('taboola_access_token');
        if ($cache) {
            return $cache;
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://backstage.taboola.com/backstage/oauth/token");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return false;
        } else {
            if (($data = json_decode($response, true))) {
                Cache::put('taboola_access_token', $data['access_token'], 600);
                return $data['access_token'];
            }
            return false;
        }

    }

    public function accounts()  {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, 'https://backstage.taboola.com/backstage/api/1.0/users/current/allowed-accounts/');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));
        $output = curl_exec($c);
        curl_close($c);

        if($output === false)
            return false;
        else {
            if (($data = json_decode($output, true)) && isset($data['results']))
                return $data['results'];
            return false;
        }
    }

    public function post_collections($account_name, $campaign_id, $type, $collections) {
        $c = curl_init();

        $data = [
            'type' => $type,
            'collection' => $collections
        ];

        curl_setopt($c, CURLOPT_URL, $this->endpoint . $account_name . '/campaigns/'.$campaign_id.'/targeting/postal_code');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token, 'Content-Type: application/json'));
        $output = curl_exec($c);
        curl_close($c);
        if($output === false)
            return false;
        else {
            if (($data = json_decode($output, true)))
                return $data;
            return false;
        }
    }

    public function postal_codes($account_name, $campaign_id) {
        $c = curl_init();

        curl_setopt($c, CURLOPT_URL, $this->endpoint . $account_name . '/campaigns/'.$campaign_id.'/targeting/postal_code');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));
        $output = curl_exec($c);
        curl_close($c);
        if($output === false)
            return false;
        else {
            if (($data = json_decode($output, true)))
                return $data;
            return false;
        }
    }

    public function campaigns($account_name) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $this->endpoint . $account_name . '/campaigns');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));
        $output = curl_exec($c);
        curl_close($c);
        if($output === false)
            return false;
        else {
            if (($data = json_decode($output, true)))
                return $data['results'];
            return false;
        }
    }

    public function get_post_codes() {
        $c = curl_init();

        $cache = Cache::get('tabool_postal_codes');
        if ($cache) {
            return $cache;
        }

        curl_setopt($c, CURLOPT_URL, $this->endpoint . '/resources/countries/fr/postal');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));
        $output = curl_exec($c);
        curl_close($c);
        if($output === false)
            return false;
        else {
            if (($data = json_decode($output, true)) && isset($data, $data['results'])) {
                $arr = [];
                foreach ($data['results'] as $item)
                    $arr[] = (string)$item['name'];
                Cache::put('tabool_postal_codes', $arr, 172800);
                return $arr;
            }
            return false;
        }
    }
}
