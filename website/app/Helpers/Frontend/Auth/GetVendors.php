<?php

namespace App\Helpers\Frontend\Auth;
use App\Models\Auth\User;
/**
 * Class Socialite.
 */
class GetVendors
{
    /**
     * Generates social login links based on what is enabled.
     *
     * @return string
     */
    public static function getVendorsList()
    {
        $get_vendors = User::role('vendor')->get();
        $vendors = array();
        if($get_vendors ){
            $vendors[''] = 'Please select a Vendor';
            foreach($get_vendors as $vendor){
                $vendors[$vendor->id] = $vendor->name.' - '.$vendor->city.', '.$vendor->state;
            }
        }
        return $vendors;
    }
}
