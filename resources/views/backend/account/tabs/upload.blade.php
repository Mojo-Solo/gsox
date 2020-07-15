<form action="{{ route('admin.profile.file.upload') }}" method="POST" role="form" enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    <div class="row">
        <div class="col">
            <div class="form-group">
                <div class="form-group">
                    <label for="">{{ __('validation.attributes.frontend.password') }}</label>
                    <input type="file" class="form-control" name="file[]" multiple="multiple">
                </div>
            </div><!--form-group-->
        </div><!--col-->
    </div><!--row-->


    <div class="row">
        <div class="col">
            <div class="form-group mb-0 clearfix">
                <button type="submit" class="btn btn-primary">{{ __('labels.general.buttons.update') . ' ' . __('validation.attributes.frontend.password') }}</button>
            </div><!--form-group-->
        </div><!--col-->
    </div><!--row-->
    
</form>


<div class="row" style="padding-top: 20px;">
    <div class="col">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Remove</th>
                </tr>
            </thead>

            <tbody>
             @if (isset($logged_in_user->file_uploads) && $logged_in_user->file_uploads != null)
                @forelse(json_decode($logged_in_user->file_uploads) as $file)
                <tr>
                    <td><a href="">{{ $file }}</a></td>
                    <td><a href="{{ route('admin.profile.file.delete',$file) }}">Delete</a></td>
                </tr>
                @empty
                @endforelse
            @endif
            </tbody>
        </table>
    </div>
</div>
