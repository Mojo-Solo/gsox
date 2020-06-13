<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Frontend\Auth\UserRepository;
use App\Notifications\Frontend\Auth\UserNeedsConfirmation;
use App\Models\Auth\User;
use App\Models\Vendor;
/**
 * Class ConfirmAccountController.
 */
class ConfirmAccountController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * ConfirmAccountController constructor.
     *
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param $token
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function confirm($token)
    {
        $this->user->confirm($token);

        return redirect()->route('frontend.auth.login')->withFlashSuccess(__('exceptions.frontend.auth.confirmation.success'));
    }
    public function verifyUser($token)
    {
        $userVerify=User::where('confirmation_code',$token)->first();
        if(!$userVerify) {
            abort(404);
        }
        if($userVerify->confirmed > 0) {
        return redirect()->route('frontend.auth.login')->withFlashDanger(__('Email Already Verified'));

        }
        $userVerify->confirmed=1;
        $userVerify->save();

        return redirect()->route('frontend.auth.login')->withFlashSuccess(__('exceptions.frontend.auth.confirmation.success'));
    }

    public function verifyVendor($token)
    {
        $vendorVerify=Vendor::where('confirmation_code',$token)->first();
        if(!$vendorVerify) {
            abort(404);
        }
        if($vendorVerify->confirmed > 0) {
        return redirect()->route('frontend.auth.login')->withFlashDanger(__('Email Already Verified'));

        }
        $vendorVerify->confirmed=1;
        $vendorVerify->save();
        if($vendorVerify->is_student) {
            $user=User::where('email',$vendorVerify->email)->first();
            if($user) {
                $user->confirmed=1;
                $user->save();
            }
        }

        return redirect()->route('frontend.auth.login')->withFlashSuccess(__('exceptions.frontend.auth.confirmation.success'));
    }

    /**
     * @param $uuid
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function sendConfirmationEmail($uuid)
    {
        $user = $this->user->findByUuid($uuid);

        if ($user->isConfirmed()) {
            return redirect()->route('frontend.auth.login')->withFlashSuccess(__('exceptions.frontend.auth.confirmation.already_confirmed'));
        }

        $user->notify(new UserNeedsConfirmation($user->confirmation_code));

        return redirect()->route('frontend.auth.login')->withFlashSuccess(__('exceptions.frontend.auth.confirmation.resent'));
    }
}
