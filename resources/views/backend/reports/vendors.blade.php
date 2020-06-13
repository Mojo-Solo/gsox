@extends('backend.layouts.app')

@section('title', __('Vendors Report').' | '.app_name())

@push('after-styles')
    <style>
        .dataTables_wrapper .dataTables_filter {
            float: right !important;
            text-align: left;
            margin-left: 25%;
        }

        div.dt-buttons {
            display: inline-block;
            width: 100%;
            text-align: center;
        }
    </style>
@endpush
@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">Vendors Report</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="myTable" class="table table-bordered table-striped ">
                            <thead>
                            <tr>
                                <th>@lang('labels.general.sr_no')</th>
                                <th>@lang('labels.backend.reports.fields.course')</th>
                                <th>@lang('labels.backend.access.users.vendor')</th>
                                <th>@lang('labels.backend.reports.fields.completed')</th>
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
{!! Form::open(['route' => 'admin.reports.get_vendors_data_bydate', 'id'=>'date_range_form']) !!}
{!! Form::hidden('dateFrom', '', ['class' => 'form-control', 'id'=>'dateFrom']) !!}
{!! Form::hidden('dateTo', '', ['class' => 'form-control', 'id'=>'dateTo']) !!}
{!! Form::close() !!}
@stop

@push('after-scripts')
    <script>
        var course_route='';
        $(document).ready(function () {
            course_route = '{{route('admin.reports.get_vendors_data')}}';
            var from="{{(isset($_GET['from']))?$_GET['from']:''}}";
            var to="{{(isset($_GET['to']))?$_GET['to']:''}}";
            if(from!='' && to!='') {
                course_route=course_route+'?from='+from+'&to='+to;
            }
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
                            columns: ':visible',
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible',
                        }
                    },
                    'colvis'
                ],
                ajax: course_route,
                columns: [

                    {data: "id", name: 'id', width: '8%'},
                    {data: "title", name: 'title'},
                    {data: "vendor", name: 'vendor'},
                    {data: "completed", name: 'completed'},
                ],
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    }
                },
                order:[[0,"desc"]],
                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
            });
        });
        $(window).bind("load",function() {
            $('#myTable_wrapper').prepend("<center><label for='from'>From:</label><input name='from' type='date' id='from' value='<?php if(isset($_GET['from'])) { echo $_GET['from']; } else { echo '';} ?>'><label for='to'>To:</label><input name='to' type='date' id='to' value='<?php if(isset($_GET['to'])) { echo $_GET['to']; } else { echo '';} ?>'> <button type='btn' class='btn-primary' id='fitersearch'>Search</button></center>");
            $('#fitersearch').on('click',function(){
                var from=$('#from').val();
                var to=$('#to').val();
                if((from!='' && to!='') || (from=='' && to==''))
                {
                    course_route="{{route('admin.reports.vendors')}}";
                    location.replace(course_route+'?from='+from+'&to='+to);
                }
            });
        });
    </script>

@endpush