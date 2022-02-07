<?php
/**
 * Created by IntelliJ IDEA.
 * User: antoinevideau
 * Date: 2019-10-29
 * Time: 16:53
 */

namespace App\Libs;


class Convert
{

    public $list_mail = [
        'email', 'e-mail'
    ];

    public $list_names = [
        'name', 'full-name', 'prenom-et-nom'
    ];

    public $list_zip = [
        'zip', 'post-code', 'code-postal', 'zip_code', 'post_code', 'code-postal'
    ];

    public $list_phone = [
        'phone', 'phone-number', 'tel', 'numero-de-telephone'
    ];

    public $list_keep = [
        'firstname', 'lastname', 'address', 'city', 'country', 'utm_source', 'utm_medium', 'utm_campaign'
    ];

    public function keep_fields() {
        return array_merge($this->list_mail, $this->list_zip, $this->list_names, $this->list_phone, $this->list_keep);
    }

    public function search($data, $list) {
        foreach ($list as $item) {
            if (isset($data[$item]))
                return $item;
        }
    }

    public function keep($data, $field, $stop = true) {
        if (isset($data[$field]))
            return $data[$field];

        if ($stop)
            return '.';
        return null;
    }

    public function firstname($data) {
        if (isset($data['firstname'])) {
            return $data['firstname'];
        } else if (($search = $this->search($data, $this->list_names))) {
            $explode = explode(' ', $data[$search]);
            return (isset($explode[0])) ? $explode[0] : $data[$search];
        }

        return '.';
    }

    public function lastname($data) {
        if (isset($data['lastname'])) {
            return $data['lastname'];
        } else if (($search = $this->search($data, $this->list_names))) {
            $explode = explode(' ', $data[$search]);
            return (isset($explode[1])) ? $explode[1] : $data[$search];
        }
        return '.';
    }

    // NAME

    public function name($data) {
        if (isset($data['lastname']) && isset($data['firstname']))
            return $data['lastname'] . ' '.  $data['firstname'];
        else if (($search = $this->search($data, $this->list_names))) {
            return $data[$search];
        }
        return '.';
    }

    public function full_name($data) {
        return $this->name($data);
    }

    public function prenom_et_nom($data) {
        return $this->name($data);
    }

    // END NAME

    public function email($data) {
        if (($search = $this->search($data, $this->list_mail))) {
            return $data[$search];
        }
        return '.';
    }

    public function e_mail($data) {
        return $this->email($data);
    }

    // PHONE

    public function phone($data) {
        if (($search = $this->search($data, $this->list_phone))) {
            return $data[$search];
        }
        return '.';
    }

    public function tel($data) {
        return $this->phone($data);
    }

    public function phone_number($data) {
        return $this->phone($data);
    }

    public function numero_de_telephone($data) {
        return $this->phone($data);
    }

    // PHONE END

    public function address($data) {
        return $this->keep($data, 'address');
    }

    public function city($data) {
        return $this->keep($data, 'city');
    }

    public function country($data) {
        return $this->keep($data, 'country');
    }

    public function utm_source($data) {
        return $this->keep($data, 'utm_source', false);
    }

    public function utm_medium($data) {
        return $this->keep($data, 'utm_medium', false);
    }

    public function utm_campaign($data)
    {
        return $this->keep($data, 'utm_campaign', false);
    }

    // ZIP

    public function zip($data) {
        if (($search = $this->search($data, $this->list_zip))) {
            return $data[$search];
        }
        return '.';
    }

    public function zip_code($data) {
        return $this->zip($data);
    }

    public function post_code($data) {
        return $this->zip($data);
    }

    public function code_postal($data) {
        return $this->zip($data);
    }

    // ZIP END


    public function randomField($field, $data) {
        if (isset($data[$field]))
            return $this->generateRandom($field, $data[$field]);
        return '.';
    }

    public function generateRandom($field, $data) {
        shuffle($data);
        return $data[0];
    }
}
