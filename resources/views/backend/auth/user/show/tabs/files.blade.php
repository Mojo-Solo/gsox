{{-- <div class="col">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Remove</th>
                </tr>
            </thead>

            <tbody>
             @if (isset($$user->file_uploads) && $$user->file_uploads != null)
                @forelse(json_decode($$user->file_uploads) as $file)
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
</div><!--table-responsive--> --}}
