<?php

namespace App\Http\Controllers\Backend\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\User\UpdateProfileRequest;
use App\Http\Requests\Frontend\User\UpdateVendorProfileRequest;
use App\Models\Auth\User;
use App\Models\Vendor;
use App\Repositories\Frontend\Auth\UserRepository;
use File;
use Illuminate\Http\Request as CustomRequest;
use Request;
use Illuminate\Support\MessageBag;

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
	 * @param UpdateProfileRequest request()
	 *
	 * @return mixed
	 * @throws \App\Exceptions\GeneralException
	 */
	public function update(UpdateProfileRequest $request)
	{
		$fieldsList = [];
		if (config('registration_fields') != NULL)
		{
			$fields = json_decode(config('registration_fields'));

			foreach ($fields as $field)
			{
				$fieldsList[] = '' . $field->name;
			}
		}
		$output = $this->userRepository->update(
			$request->user()->id,
			$request->only('first_name', 'last_name', 'dob', 'phone', 'gender', 'address', 'city', 'pincode', 'state', 'country', 'avatar_type', 'avatar_location'),
			$request->has('avatar_location') ? $request->file('avatar_location') : false
		);

		// E-mail address was updated, user has to reconfirm
		if (is_array($output) && $output['email_changed'])
		{
			auth()->logout();

			return redirect()->route('frontend.auth.login')->withFlashInfo(__('strings.frontend.user.email_changed_notice'));
		}

		return redirect()->route('admin.account')->withFlashSuccess(__('strings.frontend.user.profile_updated'));
	}

	public function updateVendor(UpdateVendorProfileRequest $request)
	{
		$vendor = Vendor::findOrFail(auth()->user()->id);
		$vendor->contact_name = $request->name;
		if ($request->avatar_type == "gravatar")
		{
			$vendor->avatar_type = "gravatar";
			$vendor->avatar_location = "";
		}
		elseif ($request->avatar_type == "storage")
		{
			if ($request->hasFile('avatar_location'))
			{
				$imgfile = $request->file('avatar_location');
				$imgpath = 'public/storage/avatars';
				File::makeDirectory($imgpath, $mode = 0777, true, true);
				$name = time() . "_" . $imgfile->getClientOriginalName();
				$success = $imgfile->move(public_path('storage/avatars'), $name);
				if ($vendor->avatar_location != '')
				{
					$old_image = public_path('storage\avatars') . "\\" . $vendor->avatar_location;
					if (File::exists($old_image))
					{
						File::delete($old_image);
					}
				}
				$vendor->avatar_type = "storage";
				$vendor->avatar_location = "avatars/" . $name;
			}
		}
		$vendor->save();
		return redirect()->route('admin.account')->withFlashSuccess(__('strings.frontend.user.profile_updated'));
	}

	public function uploadFiles(CustomRequest $request, MessageBag $message_bag)
	{
		$request->validate([
			'file' => 'required|max:10000',
		]);

        if ($request->file->getClientOriginalExtension() == 'zip' || 
            $request->file->getClientOriginalExtension() == 'exe' ||
            $request->file->getClientOriginalExtension() == '') 
        {    
            $message_bag->add('file', 'This File type not supported!');
        }

        if ($message_bag) 
        {
            return redirect()->route('admin.account')->withErrors($message_bag);
        }

	   $oldImages = auth()->user()->file_uploads != null ? json_decode(auth()->user()->file_uploads) : [];

		if ($file = request()->file('file'))
		{
				$name = $file->getClientOriginalExtension();
				$realName = rand(1, 100) . uniqid() . 'file' . '.' . $name;
				$file->move(public_path('user_uploads'), $realName);
				$images[] = $realName;
		}

		$updatedImages = is_array($oldImages) ? array_merge($images, $oldImages) : $images;

		$user = User::findOrFail(auth()->user()->id);
		$user->file_uploads = json_encode($updatedImages);
		$user->save();

		return redirect()->route('admin.account')->withFlashSuccess('File Uploaded successfully');
	}

	public function deleteFiles($image)
	{
		$user = auth()->user();

		$arr = json_decode($user->file_uploads);

		if (in_array($image, $arr))
		{
			if (file_exists(public_path('user_uploads/' . $image)))
			{
				unlink(public_path('user_uploads/' . $image));
			}

			$index = array_search($image, $arr);
			unset($arr[$index]);
		}
		else
		{
			return back()->withFlashSuccess('Something wrong!');
		}

		$json = json_encode(array_values($arr));

		$user->file_uploads = $json;
		$user->update();

		return back()->withFlashSuccess('File Deleted successfully!');
	}
}
