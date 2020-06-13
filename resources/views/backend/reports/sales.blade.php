@extends('backend.layouts.app')

@section('title', __('labels.backend.reports.sales_report').' | '.app_name())

@push('after-styles')
<style type="text/css">
    #myCourseTable{
        width: 100% !important;
    }
</style>
@endpush

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">@lang('labels.backend.reports.sales_report')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-5">
                    <div class="card text-white bg-primary text-center">
                        <div class="card-body">
                            <h2 class="">{{$appCurrency['symbol'].' '.number_format($total_earnings)}}</h2>
                            <h5>@lang('labels.backend.reports.total_earnings')</h5>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5 ml-auto">
                    <div class="card text-white bg-success text-center">
                        <div class="card-body">
                            <h2 class="">{{$total_sales}}</h2>
                            <h5>@lang('labels.backend.reports.total_sales')</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h4>@lang('labels.backend.reports.courses')</h4>
                    <div class="table-responsive">
                        <div class="d-block">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.reports.sales') }}"
                                       style="{{ (request('paid') == 1 || request('unpaid') == 1 ) ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.reports.sales') }}?paid=1"
                                       style="{{ (request('paid') ==1 ) ? 'font-weight: 700' : '' }}">Paid</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.reports.sales') }}?unpaid=1"
                                       style="{{ (request('unpaid') ==1 ) ? 'font-weight: 700' : '' }}">UnPaid</a>
                                </li>
                            </ul>
                        </div>
                        <table id="myCourseTable" class="table table-bordered table-striped ">
                            <thead>
                            <tr>
                                <th width="10%">@lang('labels.general.sr_no')</th>
                                <th width="40%">Course Name</th>
                                <th width="20%">Client</th>
                                <th width="10%">@lang('labels.backend.reports.fields.orders')</th>
                                <th width="20%">@lang('labels.backend.reports.fields.earnings') <span style="font-weight: lighter">(in {{$appCurrency['symbol']}})</span></th>
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
            var course_route = '{{route('admin.reports.get_course_data')}}';
            var paid="{{(isset($_GET['paid']))?$_GET['paid']:''}}";
            var unpaid="{{(isset($_GET['unpaid']))?$_GET['unpaid']:''}}";
            if(paid) {
                course_route=course_route+'?paid=1';
            }if(unpaid) {
                course_route=course_route+'?unpaid=1';
            }

            $('#myCourseTable').DataTable({
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
                    {data: "name", name: 'name'},
                    {data: "client", name: 'client'},
                    {data: "orders", name: 'orders'},
                    {data: "earnings", name: 'earnings'},
                ],
                order:[[0,"desc"]],
                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
            });
        });

    </script>

@endpush