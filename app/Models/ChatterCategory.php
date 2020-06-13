<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatterClient extends Model
{
    protected $guarded = [];

    public function parent(){

        return ChatterClient::where('id','=',$this->parent_id)->first();
    }
}
