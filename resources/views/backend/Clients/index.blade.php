@extends('backend.layouts.app')

@section('title', __('labels.backend.Clients.title').' | '.app_name())

@section('content')
<style>
.right{float:right;}
</style>
    <div class="card">
        <div class="card-header">

                <h3 class="page-title d-inline">@lang('labels.backend.Clients.title')</h3>
                <div class="float-right">
                    <a href="{{ route('admin.Clients.create') }}"
                       class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>
                </div>

        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <div class="d-block">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.Clients.index') }}"
                                       style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.Clients.index') }}?show_deleted=1"
                                       style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                                </li>
                            </ul>
                        </div>


                        <table id="myTable"
                               class="table table-bordered table-striped @can('client_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                            <thead>
                            <tr>

                                @can('client_delete')
                                    @if ( request('show_deleted') != 1 )
                                        <th style="text-align:center;">
                                            <input type="checkbox" class="mass" id="select-all"/>
                                        </th>
                                    @endif
                                @endcan

                                <th>@lang('labels.general.sr_no')</th>
                                <th>@lang('labels.backend.Clients.fields.name')</th>
                                <th>@lang('labels.backend.Clients.fields.slug')</th>
                                <th>@lang('labels.backend.Clients.fields.icon')</th>
                                <th>@lang('labels.backend.Clients.fields.courses')</th>
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

@stop

@push('after-scripts')
    <script>

        $(document).ready(function () {
            var route = '{{route('admin.Clients.get_data')}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.Clients.get_data',['show_deleted' => 1])}}';
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
                            columns: [ 1, 2, 3, 5 ]

                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [ 1, 2, 3, 5 ]
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [
                        @can('client_delete')
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                      @endcan
                    {
                        data: "DT_RowIndex", name: 'DT_RowIndex'
                    },
                    {data: "name", name: 'name', render:function(data,type,row){
                        return '<a href="{{url('courses')}}/'+row['slug']+'">'+data+'</a>';
                    }},
                    {data: "slug", name: 'slug'},
                    {data: "icon", name: 'icon'},
                    {data: "courses", name: "courses",orderable:false,render : function(data, type, row) {
                        return data+' <a href="{{url("/")}}/courses/'+row['slug']+'" target="_blank" class="btn btn-info right">View</a>';
                    }},
                    {data: "actions", name: "actions"}
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"className": "text-center", "targets": [0]}
                ],
                @endif

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
            @can('client_access')
            @if(request('show_deleted') != 1)
            $('.actions').html('<a href="' + '{{ route('admin.Clients.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif
            @endcan

        });

    </script>

@endpush