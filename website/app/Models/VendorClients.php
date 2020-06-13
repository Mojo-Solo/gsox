<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorClients extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function vendors(){
        return $this->belongsToMany(User::class, 'vendor_id');
    }


}
