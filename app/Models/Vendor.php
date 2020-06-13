<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
//use Spatie\MediaLibrary\HasMedia\HasMedia;
use Illuminate\Support\Facades\File;
use Mtownsend\ReadTime\ReadTime;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Auth\Traits\Method\UserMethod;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
/**
 * Class Vendor
*/
class Vendor  extends Authenticatable
{
    // use SoftDeletes;
    use Notifiable,HasRoles,UserMethod;
    protected $table = 'vendors';
    protected $guarded = [];
	
	public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function supervisors() {
    	return $user_ids = \DB::table('users')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', '=', 'supervisor')
                ->where('users.vendor_id', '=', auth()->user()->id)
                ->pluck('users.id')
                ->toArray();
    }
    public function supervisorsById($id) {
        return $user_ids = \DB::table('users')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', '=', 'supervisor')
                ->where('users.vendor_id', '=', $id)
                ->pluck('users.id')
                ->toArray();
    }
    public function clientsById($id) {
        if(empty($id)) {
            return array();
        }
        $vendor=\DB::table('vendors')->where('id',$id)->first();
        if($vendor)
            return explode(",", $vendor->clients);
        else
            return array();
    }
    public function students() {
    	return $user_ids = \DB::table('users')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', '=', 'student')
                ->where('users.vendor_id', '=', auth()->user()->id)
                ->pluck('users.id')
                ->toArray();
    }
}
