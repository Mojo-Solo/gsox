<?php

namespace App\Http\Controllers\Backend\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\User\UpdatePasswordRequest;
use App\Repositories\Frontend\Auth\UserRepository;
use App\Models\Auth\User;
use App\Models\Vendor;
use Hash;
use Request;


/**
 * Class UpdatePasswordController.
 */
class UpdatePasswordController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * ChangePasswordController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param UpdatePasswordRequest $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function update(UpdatePasswordRequest $request)
    {
        if(auth()->guard('vendor')->check()) {
            $vendor=Vendor::findOrFail(auth()->user()->id);
            if (Hash::check($request->old_password, $vendor->password)) {
                $vendor->update(['password' => Hash::make($request->password)]);
            } else {
                return redirect()->back()->withFlashDanger(__('Incorrect Old Password'));
            }
        }
        else {
            $user=User::findOrFail(auth()->user()->id);
            if (Hash::check($request->old_password, $user->password)) {
                $user->update(['password' => Hash::make($request->password)]);
            } else {
                return redirect()->back()->withFlashDanger(__('Incorrect Old Password'));
            }
        }

        return redirect()->back()->withFlashSuccess(__('strings.frontend.user.password_updated'));
    }
}
