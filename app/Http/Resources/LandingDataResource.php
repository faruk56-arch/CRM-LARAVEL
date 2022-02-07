<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LandingDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $arr = array(
            'id' => $this->id,
            'landing_id' => $this->landing_id,
            'landing' => $this->landing->name,
            'visitor' => $this->visitor,
            'created_at' => date('Y-m-d H:i', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d H:i', strtotime($this->updated_at)),
        );
        $json = json_decode($this->data, true);
        $arr = array_merge($arr, $json);
        if ($this->landing->zone_climatique)
            $arr = array_merge($arr, ['zone_climatique' => \App\LandingData::zone_france($json)]);
        $arr['entry_status'] = $this->entry_status;

        return $arr;
    }
}
