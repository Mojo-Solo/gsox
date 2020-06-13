@extends('backend.layouts.app')
@section('title', __('labels.backend.access.vendors.management').' | '.app_name())


@section('content')

    <div class="card">
        <div class="card-header">
                <h3 class="page-title d-inline">@lang('labels.backend.access.vendors.management')</h3>
            @if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_create']))
                <div class="float-right">
                    <a href="{{ route('admin.vendors.create') }}"
                       class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>

                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <div class="d-block">
                            <ul class="list-inline">
                            </ul>
                        </div>


                        <table id="myTable"
                               class="table table-bordered table-striped @if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_delete'])) @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                            <thead>
                            <tr>

                                @if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_delete']))
                                    @if ( request('show_deleted') != 1 )
                                        <th style="text-align:center;"><input type="checkbox" class="mass"
                                                                              id="select-all"/>
                                        </th>@endif
                                @endif

                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Confirmed</th>
                                <th>@lang('labels.backend.access.vendors.table.contact_name')</th>
                                <th>@lang('labels.backend.access.vendors.table.contact_email')</th>
                                <th>@lang('labels.backend.access.vendors.table.phone_number')</th>
                                <th>@lang('labels.backend.access.vendors.table.company_name')</th>
                                <th>@lang('labels.backend.access.vendors.table.country_id')</th>
                                <th>@lang('labels.backend.access.vendors.table.city')</th>
                                <th>Clients</th>
                                @if( request('show_deleted') == 1 )
                                    <th>&nbsp; @lang('strings.backend.general.actions')</th>
                                @else
                                    <th>&nbsp; @lang('strings.backend.general.actions')</th>
                                @endif
                            </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script>

        $(document).ready(function () {
            var route = '{{route('admin.Vendors.get_data')}}';
            @if(request('show_deleted') == 1)
                route = '{{route('admin.Vendors.get_data',['show_deleted' => 1])}}';
            @endif
            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4, 5 ]

                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4, 5 ]
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [
                @if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_delete']))
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                @endif
                    {data: "id", name: 'id'},
                    {data: "name", name: 'name'},
                    {data: "email", name: 'email'},
                    {data: "confirmed",render : function(data, type, row) {
                            if(data)
                                return "<span class='badge badge-success'>Yes</a>";
                            else
                                return "<span class='badge badge-danger'>No</a>";
                        }, name: 'confirmed'
                    },
                    {data: "contact_name", name: 'contact_name'},
                    {data: "contact_email", name: 'contact_email'},
                    {data: "phone_number", name: 'phone_number'},
                    {data: "company_name", name: 'company_name'},
                    {data: "country_id", name: 'country_id'},
                    {data: "city", name: 'city'},
                    {data: "clients", name: 'clients'},
                    {data: "actions", name: 'actions'}
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"className": "text-center", "targets": [0]}
                ],
                @endif
                order:[[0,"desc"]],
                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    }
                }
            });
            @if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_delete']))
            $('.actions').html('<a href="' + '{{ route('admin.Supervisors.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif
        });

    </script>

@endpush