@extends('backend.layouts.app')

@section('title', __('Invoices Report').' | '.app_name())

@push('after-styles')
    <style>
        .dataTables_wrapper .dataTables_filter {
            float: right !important;
            text-align: left;
            margin-left: 25%;
        }form{
            display: initial;
        }
        
    </style>
@endpush
@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">Invoices Report</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <div class="d-block">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.reports.invoices') }}"
                                       style="{{ (request('paid') == 1 || request('unpaid') == 1 ) ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.reports.invoices') }}?paid=1"
                                       style="{{ (request('paid') ==1 ) ? 'font-weight: 700' : '' }}">Paid</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.reports.invoices') }}?unpaid=1"
                                       style="{{ (request('unpaid') ==1 ) ? 'font-weight: 700' : '' }}">UnPaid</a>
                                </li>
                            </ul>
                        </div>
                        <div class="d-block" id="actions_div">
                            <form method="post" action="#">
                                {{csrf_field()}}
                                <select class="col-3" name="action" id="action">
                                    <option value="">Select Action</option>
                                    <option value="group">Generate Group Invoice</option>
                                    <option value="paid">Mark as Paid</option>
                                    <option value="unpaid">Mark as Unpaid</option>
                                    <option value="delete">Delete</option>
                                </select>
                                <input type="hidden" name="ids" id="ids" />
                            </form>
                        </div>
                        <table id="myTable" class="table table-bordered table-striped ">
                            <thead>
                            <tr>
                                <th><input type="checkbox" id="allchecks" name="allchecks[]"></th>
                                <th>Training Date</th>
                                <th>Student Name</th>
                                <th>Student Email</th>
                                <th>Course</th>
                                <th>Record ID</th>
                                <th>Course Progress</th>
                                <th>Payment Status</th>
                                <th>Amount</th>
                                <th>Amount Collected</th>
                                <th>Invoice #</th>
                                <th>Vendor Company Name</th>
                                <th>Vendor Contact Name</th>
                                <th>Vendor Email</th>
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
            course_route = '{{route('admin.reports.get_invoices_data')}}';
            var from="{{(isset($_GET['from']))?$_GET['from']:''}}";
            var to="{{(isset($_GET['to']))?$_GET['to']:''}}";
            var paid="{{(isset($_GET['paid']))?$_GET['paid']:''}}";
            var unpaid="{{(isset($_GET['unpaid']))?$_GET['unpaid']:''}}";
            if(from!='' && to!='') {
                course_route=course_route+'?from='+from+'&to='+to;
            }
            if(paid) {
                if(from!='' && to!='')
                    course_route=course_route+'&paid=1';
                else
                    course_route=course_route+'?paid=1';
            }if(unpaid) {
                if(from!='' && to!='')
                    course_route=course_route+'&unpaid=1';
                else
                    course_route=course_route+'?unpaid=1';
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
                    {data: "id", name: 'id',searchable:false,sortable:false,render : function(data, type, row) {
                            return '<input type="checkbox" name="checkbox[]" data-id="'+data+'" id="checkbox'+data+'" />';
                        },
                    },
                    {data: "created_at", name: 'created_at'},
                    {data: "user_id", name: 'user_id'},
                    {data: "user_email", name: 'user_email'},
                    {data: "title", name: 'title'},
                    {data: "reference_no", name: 'reference_no'},
                    {data: "progress", name: 'progress'},
                    {data: "status", name: 'status'},
                    {data: "price", name: 'price'},
                    {data: "amount", name: 'amount'},
                    {data: "invoice_number", name: 'invoice_number'},
                    {data: "vendor_company_name", name: 'vendor_company_name'},
                    {data: "vendor_contact_name", name: 'vendor_contact_name'},
                    {data: "vendor_email", name: 'vendor_email'},
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
            $('#actions_div > form  #action').on('change',function(e){
                action="";
                if($(this).val()=="paid") {
                    action="{{route('admin.reports.invoices.paid')}}";
                }if($(this).val()=="unpaid") {
                    action="{{route('admin.reports.invoices.unpaid')}}";
                }if($(this).val()=="delete") {
                    action="{{route('admin.reports.invoices.delete')}}";
                }if($(this).val()=="group") {
                    action="{{route('admin.reports.invoices.group')}}";
                }
                $('#actions_div > form').attr('action',action);
                var IDs = $('table tbody input:checkbox:checked').map(function(){
                  return $(this).attr('data-id');
                }).get();
                $('#actions_div > form #ids').val(IDs);
                if(action!=='') {
                    $('#actions_div > form').submit();
                }
            });
        });
        $('#allchecks').on('change',function(){
            if($(this).is(':checked')) {
                $('table tbody input[type="checkbox"]').prop('checked', true);
            } else {
                $('table tbody input[type="checkbox"]').prop('checked', false);
            }
        });
        $(document).ready(function() {
            $('#actions_div').append("<span class='col-8'><label for='from'>From:</label><input name='from' type='date' id='from' value='<?php if(isset($_GET['from'])) { echo $_GET['from']; } else { echo '';} ?>'><label for='to'>To:</label><input name='to' type='date' id='to' value='<?php if(isset($_GET['to'])) { echo $_GET['to']; } else { echo '';} ?>'> <button type='btn' class='btn-primary' id='fitersearch'>Search</button></span>");
            $('#fitersearch').on('click',function(){
                var from=$('#from').val();
                var to=$('#to').val();
                if((from!='' && to!='') || (from=='' && to==''))
                {
                    course_route="{{route('admin.reports.invoices')}}";
                    location.replace(course_route+'?from='+from+'&to='+to);
                }
            });
        });
    </script>

@endpush