@extends('backend.layouts.app')

@section('title', __('labels.backend.reports.students_report').' | '.app_name())

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

        table.dataTable th:nth-child(1) {
          width: 20px;
          max-width: 20px;
          font-weight: bold;
         /* word-break: break-all;*/
          /*white-space: pre-line;*/
        }

        table.dataTable td:nth-child(1) {
          width: 20px;
          max-width: 20px;
         /* word-break: break-all;*/
          white-space: pre-line;
        }

       

        table.dataTable th:nth-child(9) {
          width: 72px;
          max-width: 20px;
          /*font-size:10px;*/
          font-weight: bold;
          /*word-break: break-all;*/
          white-space: pre-line;
        }

        table.dataTable td:nth-child(9) {
          width: 20px;
          max-width: 20px;
         /* word-break: break-all;*/
          white-space: pre-line;
        }

        table.dataTable th:nth-child(10) {
          width: 82px;
          max-width: 20px;
          /*font-size:10px;*/
          font-weight: bold;
          /*word-break: break-all;*/
          /*white-space: pre-line;*/
        }

        table.dataTable td:nth-child(10) {
          width: 20px;
          max-width: 20px;
         /* word-break: break-all;*/
          /*white-space: pre-line;*/
        }


    </style>
@endpush
@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">@lang('labels.backend.reports.students_report')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="myTable" class="table table-bordered table-striped ">
                            <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Student Name</th>
                                {{-- <th>Email</th> --}}
                                {{-- <th>Cd</th> --}}
                                <th>@lang('labels.backend.reports.fields.course')</th>
                                <th>Vendor Name</th>
                                {{-- <th>Status</th> --}}
                                {{-- <th>Score</th> --}}
                                <th>Amount</th>
                                {{-- <th>AC</th> --}}
                                <th>Last Viewed Timeline</th>
                                <th>Purchase Date</th>
                                <th>Course Completion Date</th>
                                <th>Certificate</th>
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
<script src="https://cdn.datatables.net/plug-ins/1.10.21/sorting/date-euro.js"></script>
    <script>
        $(document).ready(function () {
            var course_route = '{{route('admin.reports.get_students_data')}}';

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 25,
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
                    {data: "first_name", name: 'first_name',orderable: false,render : function(data, type, row) {
                        return data+' '+row['last_name'];
                    }},
                    // {data: "email", name: 'email'},
                    // {data: "confirmed", name: 'confirmed',orderable: false,render : function(data, type, row) {
                    //     if(data==0)
                    //         return '<span class="badge badge-danger">No</span>';
                    //     else
                    //         return '<span class="badge badge-success">Yes</span>';
                    // }},
                    {data: "title", name: 'title'},
                    {data: "company_name", name: 'company_name'},
                    // {data: "status", name: 'status',orderable: false,render : function(data, type, row) {
                    //     if(data==0)
                    //         return '<span class="badge badge-info">InProgress</span>';
                    //     else
                    //         return '<span class="badge badge-success">Completed</span>';
                    // }},
                    // {data: "score", name: 'score'},
                    {data: "price", name: 'price',orderable: false,render : function(data, type, row) {
                        if(data==null || data=='null')
                            return '$0';
                        else
                            return '$'+data;
                    }},
                    // {data: "amount_collected", name: 'amount_collected',orderable: false,render : function(data, type, row) {
                    //     return '$'+data;
                    // }},
                    {data: "last_viewed", name: 'last_viewed',render:function(data,type,row){
                        return '<a href="'+data['resume_link']+'">'+data['name']+'</a>';
                    }},
                    {data:{_: 'created_at.display', sort: 'created_at.timestamp'}, name: 'created_at.timestamp',class:'date'},
                    {data: {_: 'expiry.display', sort: 'expiry.timestamp'}, name: 'expiry.timestamp',class:'date'},
                    // {data: {_: 'certificates', sort: 'certificates'}, name: 'certificates',class:'date'},
                    {data: "certificates", name: 'certificates',render:function(data,type,row){
                        return data != null ? '<a href="{{ asset('storage/certificates/') }}'+'/'+data+'" class="btn btn-success">View</a>' : '';
                    }},
                ],
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    }
                },
                order:[[7,"desc"]],
                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
            });
        });

    </script>

@endpush