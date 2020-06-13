@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('Students Management'))

@section('breadcrumb-links')
    @include('backend.students.includes.breadcrumb-links')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    {{ __('Students Management') }} <small class="text-muted">{{ __('Active Students') }}</small>
                </h4>
            </div><!--col-->
            <div class="col-sm-7">
                @include('backend.students.includes.header-buttons')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>@lang('labels.backend.access.users.table.last_name')</th>
                            <th>@lang('labels.backend.access.users.table.first_name')</th>
                            <th>@lang('labels.backend.access.users.table.email')</th>
                            <th>@lang('labels.backend.access.users.table.confirmed')</th>
                            <th>@lang('labels.backend.access.users.table.other_permissions')</th>
                            <th>@lang('labels.backend.access.users.vendor')</th>
                            <th>@lang('labels.backend.access.users.table.last_updated')</th>
                            <th>@lang('labels.general.actions')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $student->last_name }}</td>
                                <td>{{ $student->first_name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{!! ($student->confirmed)?'<span class="badge badge-success">Yes</span>':'<span class="badge badge-danger">No</span>' !!}</td>
                                @php
                                $permissions=\DB::table('model_has_permissions')->where('model_id',$student->id)->pluck('permission_id')->toArray();
                                $permissions=\DB::table('permissions')->whereIn('id',$permissions)->pluck('name')->toArray()
                                ;
                                if($permissions)
                                    $permissions=implode(',<br>',$permissions);
                                else
                                    $permissions="N/A";
                                @endphp
                                <td>{!! $permissions !!}</td>
                                @php
                                $vendor=\DB::table('vendors')->where('id',$student->vendor_id)->first();
                                if($vendor)
                                    $vendor=$vendor->contact_name;
                                else
                                    $vendor='';
                                @endphp
                                <td>{!! $vendor !!}</td>
                                <td>{{ $student->updated_at }}</td>
    <td>
        <div class="btn-group" role="group" aria-label="User Actions">
          <a href="{{ url('user/students/'.$student->id) }}" data-toggle="tooltip" data-placement="top" title="View" class="btn btn-info"><i class="fas fa-eye"></i></a>
          <a href="{{ url('user/students/'.$student->id.'/edit') }}" data-toggle="tooltip" data-placement="top" title="Edit" class="btn btn-primary"><i class="fas fa-edit"></i></a>
          <div class="btn-group btn-group-sm" role="group">
            <button id="userActions" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              More
            </button>
            <div class="dropdown-menu" aria-labelledby="userActions">
              <a href="#" data-method="delete" data-trans-button-cancel="Cancel" data-trans-button-confirm="Delete" data-trans-title="Are you sure you want to do this?" class="dropdown-item" style="cursor:pointer;" onclick="$(this).find('form').submit();">Delete
                <form action="{{ url('user/students/'.$student->id) }}" method="POST" name="delete_item" style="display:none">
                <input type="hidden" name="_method" value="delete">
                {{csrf_field()}}
                </form>
                </a>
            </div>
          </div>
        </div>
    </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div><!--col-->
        </div><!--row-->
        <div class="row">
            <div class="col-7">
                <div class="float-left">
                    {!! count($students) !!} {{ trans_choice('labels.backend.access.users.table.total', count($students)) }}
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
