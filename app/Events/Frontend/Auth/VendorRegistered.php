<?php

namespace App\Events\Frontend\Auth;

use App\Models\Vendor;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered.
 */
class VendorRegistered
{
    use SerializesModels;

    /**
     * @var
     */
    public $vendor;

    /**
     * @param $vendor
     */
    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
}
