<?php

namespace App\Http\Controllers\Backend\Auth\User;

use App\Http\Controllers\Controller;
use App\Repositories\Frontend\Auth\UserRepository;
use App\Http\Requests\Frontend\User\UpdateProfileRequest;
use App\Http\Requests\Frontend\User\UpdateVendorProfileRequest;
use App\Models\Vendor;
use Request;
use File;
/**
 * Class ProfileController.
 */
class ProfileController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * ProfileController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param UpdateProfileRequest $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function update(UpdateProfileRequest $request)
    {
        $fieldsList = [];
        if(config('registration_fields') != NULL){
            $fields = json_decode(config('registration_fields'));

            foreach ($fields  as $field){
                $fieldsList[] =  ''.$field->name;
            }
        }
        $output = $this->userRepository->update(
            $request->user()->id,
            $request->only('first_name', 'last_name','dob', 'phone', 'gender', 'address', 'city', 'pincode', 'state', 'country', 'avatar_type', 'avatar_location'),
            $request->has('avatar_location') ? $request->file('avatar_location') : false
        );

        // E-mail address was updated, user has to reconfirm
        if (is_array($output) && $output['email_changed']) {
            auth()->logout();

            return redirect()->route('frontend.auth.login')->withFlashInfo(__('strings.frontend.user.email_changed_notice'));
        }

        return redirect()->route('admin.account')->withFlashSuccess(__('strings.frontend.user.profile_updated'));
    }

    public function updateVendor(UpdateVendorProfileRequest $request)
    {
        $vendor=Vendor::findOrFail(auth()->user()->id);
        $vendor->contact_name=$request->name;
        if($request->avatar_type=="gravatar") {
            $vendor->avatar_type="gravatar";
            $vendor->avatar_location="";
        } elseif($request->avatar_type=="storage") {
            if($request->hasFile('avatar_location')) {
                $imgfile = $request->file('avatar_location');
                $imgpath = 'public/storage/avatars';
                File::makeDirectory($imgpath, $mode = 0777, true, true);
                $name = time()."_".$imgfile->getClientOriginalName();
                $success = $imgfile->move(public_path('storage/avatars'), $name);
                if($vendor->avatar_location!='') {
                    $old_image=public_path('storage\avatars')."\\".$vendor->avatar_location;
                    if (File::exists($old_image)) {
                        File::delete($old_image);
                    }
                }
                $vendor->avatar_type="storage";
                $vendor->avatar_location="avatars/".$name;          
            }
        }
        $vendor->save();
        return redirect()->route('admin.account')->withFlashSuccess(__('strings.frontend.user.profile_updated'));
    }
}
