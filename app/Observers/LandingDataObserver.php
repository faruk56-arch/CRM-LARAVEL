<?php

namespace App\Observers;

use App\LandingData;

class LandingDataObserver
{
    /**
     * Handle the landing data "created" event.
     *
     * @param  \App\LandingData  $landingData
     * @return void
     */
    public function creating(LandingData $landingData)
    {
        $data = \GuzzleHttp\json_decode($landingData->data, true);
        $phone = ['phone', 'mobile', 'tel', 'phone-number', 'numero-de-telephone'];
        foreach ($phone as $item_phone) {
            if (isset($data[$item_phone])) {
                $data[$item_phone] = str_replace(' ', '', $data[$item_phone]);
                $data[$item_phone] = str_replace('.', '', $data[$item_phone]);
                $data[$item_phone] = str_replace('-', '', $data[$item_phone]);
            }
        }
        $landingData->data = json_encode($data);
    }

    /**
     * Handle the landing data "updated" event.
     *
     * @param  \App\LandingData  $landingData
     * @return void
     */
    public function updated(LandingData $landingData)
    {
        //
    }

    /**
     * Handle the landing data "deleted" event.
     *
     * @param  \App\LandingData  $landingData
     * @return void
     */
    public function deleted(LandingData $landingData)
    {
        //
    }

    /**
     * Handle the landing data "restored" event.
     *
     * @param  \App\LandingData  $landingData
     * @return void
     */
    public function restored(LandingData $landingData)
    {
        //
    }

    /**
     * Handle the landing data "force deleted" event.
     *
     * @param  \App\LandingData  $landingData
     * @return void
     */
    public function forceDeleted(LandingData $landingData)
    {
        //
    }
}
