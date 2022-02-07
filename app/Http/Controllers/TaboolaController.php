<?php

namespace App\Http\Controllers;

use App\Libs\CodePostalImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Libs\Taboola;
use Excel;

class TaboolaController extends Controller
{
    public function campaigns($account_name) {
        $taboola = new Taboola();
        $list = $taboola->campaigns($account_name);
        if ($list === false)
            abort(500);
        $arr = [];
        foreach ($list as $item) {
            $arr[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'advertiser_id' => $item['advertiser_id']
            ];
        }
        return $arr;
    }

    public function accounts() {
        $taboola = new Taboola();

        $result = $taboola->accounts();
        if ($result === false)
            abort(500);
        return $result;
    }

    public function postal_codes($account_name, $campaign_id) {
        $taboola = new Taboola();
        $result = $taboola->postal_codes($account_name, $campaign_id);
        if ($result === false)
            abort(500);
        if (isset($result) && isset($result['collection']) && is_array($result['collection']) && count($result['collection']) > 0)
            sort($result['collection']);
        return $result;
    }

    public function post(Request $request, $account_name, $campaign_id) {
        $type = $request->get('type');
        if ($type == 'INCLUDE' || $type == 'ALL') {
            $taboola = new Taboola();
            $arr = [];

            if ($request->hasFile('file')){

                $postal_codes = $taboola->get_post_codes();
                $data = Excel::toArray(new CodePostalImport, $request->file('file'));

                foreach ($data[0] as $item) {
                    if (!empty($item[0]) && strlen($item[0]) == 5 && in_array((string)$item[0], $postal_codes)) {
                        $arr[] = (string)$item[0];
                    }
                }

            }
            $arr = array_unique($arr);

            $result = $taboola->post_collections($account_name, $campaign_id, $type, $arr);

            if (isset($result['http_status']) && $result['http_status'] == 400)
                return response($result, 400);

            return [$result];
        }
        abort(500);
    }

}
