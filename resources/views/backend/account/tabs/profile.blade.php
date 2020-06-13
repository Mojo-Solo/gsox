<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>@lang('labels.frontend.user.profile.avatar')</th>
            @if(auth()->guard('vendor')->check())
            @php
            if($logged_in_user->avatar_type=="gravatar") {
                $size = config('gravatar.default.size');
                $image=gravatar()->get($logged_in_user->contact_email, ['size' => $size]);
            } else {
                $image=url('public/storage/'.$logged_in_user->avatar_location);
            }
            @endphp
            <td><img src="{{ $image }}" height="100px" class="user-profile-image" /></td>
            @else
            <td><img src="{{ $logged_in_user->picture }}" height="100px" class="user-profile-image" /></td>
            @endif
        </tr>
        <tr>
            <th>@lang('labels.frontend.user.profile.name')</th>
            <td>{{ (auth()->guard('vendor')->check())?$logged_in_user->contact_name:$logged_in_user->name }}</td>
        </tr>
        <tr>
            <th>@lang('labels.frontend.user.profile.email')</th>
            <td>{{ (auth()->guard('vendor')->check())?$logged_in_user->contact_email:$logged_in_user->email }}</td>
        </tr>
        <tr>
            <th>@lang('labels.frontend.user.profile.created_at')</th>
            <td>{{ timezone()->convertToLocal($logged_in_user->created_at) }} ({{ $logged_in_user->created_at->diffForHumans() }})</td>
        </tr>
        <tr>
            <th>@lang('labels.frontend.user.profile.last_updated')</th>
            <td>{{ timezone()->convertToLocal($logged_in_user->updated_at) }} ({{ $logged_in_user->updated_at->diffForHumans() }})</td>
        </tr>
        @if(config('registration_fields') != NULL)
            @php
                $fields = json_decode(config('registration_fields'));
            @endphp
            @foreach($fields as $item)
            @if(isset($logged_in_user[$item->name]->company_name) && !empty($logged_in_user[$item->name]->company_name))
                <tr>
                    <th>{{__('labels.backend.general_settings.user_registration_settings.fields.'.$item->name)}}</th>
                    @if($item->name=="vendor" && !$logged_in_user->isAdmin())
                    <td>{{(isset($logged_in_user[$item->name]->company_name))?$logged_in_user[$item->name]->company_name:''}}</td>
                    @else
                    <td>{{$logged_in_user[$item->name]}}</td>
                    @endif
                </tr>
            @endif
            @endforeach
        @endif
    </table>
</div>
