<?php

namespace App;

use App\Libs\Convert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandingData extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'forms',
        'entry_status',
        'import_from'
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class, 'landing_id')->withDefault();
    }

    public static function search_engine($request) {
        $arr = array();
        foreach ($request as $key => $search) {
            if ($key == '_limit' || $key == 'created_at')
                continue;
            if (in_array($key, ['entry_status', 'updated_at', 'ip', 'landing'])) {
                $arr[] = "$key = \"" . $search . "\"";
            } else {
                (string)$search_value = str_replace(',', ';', (string)$search);
                (string)$search_value = str_replace('/', ';', (string)$search_value);
                $el = explode(';', (string)$search_value);
                $tmp = '( ';
                foreach ($el as $item) {
                    if (empty($item))
                        continue;
                    $item = strtolower($item);
                    if (in_array($key, ['zip_code', 'zip', 'post_code', 'post-code', 'zip-code', 'code-postal']))
                        $tmp .= "LOWER(JSON_EXTRACT(data, '$.\"$key\"')) LIKE '\"".$item."%\"'";
                    else
                        $tmp .= "LOWER(JSON_EXTRACT(data, '$.\"$key\"')) LIKE '\"%".$item."%\"'";
                    if ( next( $el ) || !empty(next( $el )) ) {
                        $tmp .= ' OR ';
                    }
                }
                $tmp .= ' )';
                $arr[] = $tmp;

            }
        }
        return $arr;
    }

    public static function validatePhone($phone) {

        $ch = curl_init();

        $qs = "access_key=07e5e28ca619d94b0210bf885b74784c&country_code=FR&format=1&&number=" . urlencode($phone);
        $url = "http://apilayer.net/api/validate?" . $qs;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = json_decode(curl_exec($ch));
        curl_close($ch);

        if (isset($data['valid']) && $data['valid'] == true)
            return true;
        return false;
    }

    public static function zone_france($source) {
        $zone =
            [
                null,
                'H1c', 'H1a', 'H1c', 'H2d', 'H1c', 'H3', 'H2d', 'H1b', 'H2c', 'H1b', 'H3', 'H2c', 'H3',
                'H1a', 'H1c', 'H2b', 'H2b', 'H2b', 'H1C', 'H3', 'H1c', 'H2a', 'H1c', 'H2c', 'H1c', 'H2d',
                'H1a', 'H1a', 'H2a', 'H3', 'H2c', 'H2c', 'H2c', 'H3', 'H2a', 'H2b', 'H2b', 'H1c',
                'H1c', 'H2c', 'H2b', 'H1c', 'H1c', 'H2b', 'H1b', 'H2c', 'H2c', 'H2d', 'H2b', 'H2a', 'H1b',
                'H1b', 'H2b', 'H1b', 'H1b', 'H2a', 'H1b', 'H1b', 'H1a', 'H1a', 'H1a', 'H1a', 'H1c', 'H2c',
                'H2c', 'H3', 'H1b', 'H1b', 'H1c', 'H1b', 'H1c', 'H2b', 'H1c', 'H1c', 'H1a', 'H1a', 'H1a',
                'H1a', 'H2b', 'H1a', 'H2c', 'H2c', 'H3', 'H2d', 'H2b', 'H2b', 'H1c', 'H1b', 'H1b', 'H1b',
                'H1a', 'H1a', 'H1a', 'H1a', 'H1a'
            ];
        $zipe_code = \App\LandingData::zip_code($source);
        $zipe_code = substr($zipe_code, 0, 2);
        if (isset($zone[(int)$zipe_code]))
            return $zone[(int)$zipe_code];
        return 'Hors zone';
    }

    public static function zip_code($source) {
        $test_data = ['zip_code', 'zip', 'post_code', 'post-code', 'zip-code', 'code-postal'];
        $key = false;
        foreach ($test_data as $test) {
            if (array_key_exists($test, $source))
                $key = $test;
        }

        if (!$key)
            return 0;
        return $source[$key];
    }

    public static function firstname($source) {
        $convert = new Convert();
        return $convert->firstname($source);


        $test_data = ['firstname', 'name', 'full-name'];
        $key = false;
        foreach ($test_data as $test) {
            if (array_key_exists($test, $source))
                $key = $test;
        }

        if (!$key)
            return 0;
        return $source[$key];
    }

    public static function lastname($source) {
        $convert = new Convert();
        return $convert->lastname($source);

        $test_data = ['lastname'];
        $key = false;
        foreach ($test_data as $test) {
            if (array_key_exists($test, $source))
                $key = $test;
        }

        if (!$key)
            return 0;
        return $source[$key];
    }

    public static function phone($source) {
        $test_data = ['phone', 'mobile', 'tel', 'phone-number', 'numero-de-telephone'];
        $key = false;
        foreach ($test_data as $test) {
            if (array_key_exists($test, $source))
                $key = $test;
        }

        if (!$key)
            return 0;
        return $source[$key];
    }

    public static function email($source) {
        $test_data = ['email', 'e-mail'];
        $key = false;
        foreach ($test_data as $test) {
            if (array_key_exists($test, $source))
                $key = $test;
        }

        if (!$key)
            return 0;
        return $source[$key];
    }

    public static function dd($cast, $id) {
        if (in_array($id, [1, 17])) {
            return [
                'type_habitation' => (isset($cast['type_habitation'])) ? utf8_decode($cast['type_habitation']) : null,
                'own_state' => utf8_decode($cast['own_state']),
                'conso' => utf8_decode($cast['conso']),
                'professional_situation' => utf8_decode($cast['professional_situation']),
                'address' => utf8_decode($cast['address']),
                'city' => utf8_decode($cast['city']),
                'country' => utf8_decode($cast['country']),
                'heating' => (isset($cast['heating'])) ? utf8_decode($cast['heating']) : '',
            ];
        }
        if (in_array($id, [3, 6, 7])) {
            return [
                'own_state' => utf8_decode($cast['votre-situation']),
            ];
        }

        if ($id == 4) {
            return [
                'superficie-de-votre-habitation' => $cast['superficie-de-votre-habitation'],
                'heating' => $cast['votre-maison-est-chauffee'],
                'avez-vous-un-sous-sol-ou-un-vide-sanitaire' => $cast['avez-vous-un-sous-sol-ou-un-vide-sanitaire']
            ];
        }

        if (in_array($id, [9, 16, 18, 13])) {
            return [
                'type_habitation' => utf8_decode($cast['type_habitation']),
                'own_state' => utf8_decode($cast['own_state']),
                'conso' => utf8_decode($cast['conso']),
                'address' => utf8_decode($cast['address']),
                'city' => utf8_decode($cast['city']),
                'country' => utf8_decode($cast['country']),
                ];
        }

        if ($id == 10) {
            return [
                'type_habitation' => utf8_decode($cast['type_habitation']),
                'own_state' => utf8_decode($cast['own_state']),
                'conso' => utf8_decode($cast['conso']),
                'address' => utf8_decode($cast['address']),
                'city' => utf8_decode($cast['city']),
                'country' => utf8_decode($cast['country']),
                'family_situations' => utf8_decode($cast['family_situations']),
            ];
        }

        if ($id == 14) {
            return [
                'chauffage_collectif' => utf8_decode($cast['chauffage_collectif']),
                'statut' => utf8_decode($cast['statut']),
            ];
        }

        if (in_array($id, [8, 12, 58])) {
            return [
                'superficie-de-votre-habitation' => utf8_decode($cast['superficie']),
                'heating' => utf8_decode($cast['chauffage']),
                'avez-vous-un-sous-sol-ou-un-vide-sanitaire' => utf8_decode($cast['sous_sol_vide_sanitaire']),
            ];
        }

        if (in_array($id, [26, 24])) {
            return [
                'own_state' => isset($cast['votre-situation']) ? utf8_decode($cast['votre-situation']) : null,
//                'heating' => utf8_decode($cast['chauffage']),
//                'avez-vous-un-sous-sol-ou-un-vide-sanitaire' => utf8_decode($cast['sous_sol_vide_sanitaire']),
            ];
        }

        if (in_array($id, [57])) {
            return [
                'own_state' => utf8_decode($cast['votre-situation']),
                'heating' => utf8_decode($cast['mode-de-chauffage']),
            ];
        }

        if (in_array($id, [19])) {
            return [
                'age_chaudiere' => utf8_decode($cast['age_chaudiere']),
                'surface_logement' => utf8_decode($cast['surface_logement']),
                'type_habitation' => utf8_decode($cast['type_bien']),
                'heating' => utf8_decode($cast['mode_chauffage']),
                'own_state' => utf8_decode($cast['proprietaire']),
                'conso' => utf8_decode($cast['consommation']),
            ];
        }
        return [];
    }
}
