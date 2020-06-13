@extends('backend.layouts.app')

@section('title', __('Group Invoice').' | '.app_name())

@push('after-styles')
    <style>
        .dataTables_wrapper .dataTables_filter {
            float: right !important;
            text-align: left;
            margin-left: 25%;
        }form{
            display: initial;
        }
        .table-bordered th, .table-bordered td {
            font-size: 12px !important;
            padding: 0px !important;
            text-align: center;
        }
        .table-responsive{overflow-x: hidden;}
    </style>
@endpush
@section('content')

    <div class="card" style="background-color:#ffffff;">
        <div class="card-header">
            <h3 class="page-title d-inline">Group Invoice</h3>
            <button type="button" style="float: right;" class="btn btn-success" id="pdfbtn"><i class="fa fa-download"></i> Download as PDF</button>
        </div>
        <div class="card-body html2canvas-container" style="background-color:#ffffff;" id="printable">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <div class="row">
                            <div class="col-6">
                                <img class="navbar-brand-full" src="{{asset('storage/logos/'.config('logo_b_image'))}}"  height="100" alt="Square Logo">
                            </div>
                            <div class="col-6">
                                <p>Date: <strong>{{date('l d F Y')}}</strong></p>
                                <p>Invoice #: <strong><input id="custom_invoice_number" style="border: 0px;font-weight: bold;" type="text" placeholder="Enter invoice number"></strong></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="details" class="control-label">Business Details:</label>
                                    <textarea oninput="auto_grow(this)" style="resize: none;overflow: hidden;min-height: 50px;max-height: 200px;border-color: #c8ced3;" class="form-control">{{app_name()}}</textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="details" class="control-label"><strong>Customer Details:</strong></label>
                                    <table id="myTableq" class="table table-bordered table-striped ">
                                        <thead>
                                        <tr>
                                            <th width="5%">Name</th>
                                            <th width="20%">Email</th>
                                            <th width="5%">Contact Name</th>
                                            <th width="20%">Contact Email</th>
                                            <th width="10%">Phone Number</th>
                                            <th width="10%">Company Name</th>
                                            <th width="5%">City</th>
                                            <th width="5%">State</th>
                                            <th width="5%">Zip</th>
                                            <th width="7%">Address 1</th>
                                            <th width="7%">Address 2</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vendors as $vendor)
                                            <tr>
                                                <td>{{$vendor->name}}</td>
                                                <td>{{$vendor->email}}</td>
                                                <td>{{$vendor->contact_name}}</td>
                                                <td>{{$vendor->contact_email}}</td>
                                                <td>{{$vendor->phone_number}}</td>
                                                <td>{{$vendor->company_name}}</td>
                                                <td>{{$vendor->city}}</td>
                                                <td>{{$vendor->state}}</td>
                                                <td>{{$vendor->zip}}</td>
                                                <td>{{$vendor->address1}}</td>
                                                <td>{{$vendor->address2}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <label><strong>Items:</strong></label>
                        <table id="myTable" class="table table-bordered table-striped ">
                            <thead>
                            <tr>
                                <th width="20%">Student Name</th>
                                <th width="35%">Course</th>
                                <th width="15%">Course Progress</th>
                                <th width="20%">Training Date</th>
                                <th width="10%">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php $total=0; ?>
                                @foreach($orders as $order)
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>{{(isset($order->user))?$order->user->first_name.' '.$order->user->last_name:''}}</td>
                                         <td>{{(isset($item->item))?$item->item->title:''}}</td>
                                        @php
                                            $progress='';
                                            $course='';
                                            $student='';
                                            $price="";
                                            if($order->user) {
                                                $student=$order->user;
                                            }
                                            if($item->item) {
                                                $course=$item->item;
                                            }
                                            if($student && $course) {
                                                $completed_lessons =  $student->chapters()->where('course_id',$course->id)->get()->pluck('model_id')->toArray();
                                                if (!empty($completed_lessons) && count($completed_lessons) > 0) {
                                                    if(count($completed_lessons) >= App\Models\CourseTimeline::where('course_id',$course->id)->count()) {
                                                        $progress="Pass";
                                                    } else {
                                                        $progress="InProgress";
                                                    }
                                                    
                                                } else {
                                                    $progress='InProgress';
                                                }
                                            }
                                            if(isset($item->item)){
                                                $total+=$item->item->price;
                                                $price=$item->item->price;
                                            }
                                        @endphp
                                        <td>{{$progress}}</td>
                                        <td>{{date("d, F Y H:s:i", strtotime($item->created_at))}}</td>
                                        <td>{{'$'.$price}}</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        <div class="form-group col-2 offset-10">
                            <label>Total:</label>
                            <div class="row">
                                <div class="col-6" style="border:1px solid;">
                                    <strong>Subtotal</strong>
                                </div>
                                <div class="col-6" style="border-top:1px solid;border-right:1px solid;border-bottom:1px solid;">
                                    {{'$'.number_format($total,2)}}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6" style="border-left:1px solid;border-bottom:1px solid;border-right:1px solid;">
                                    <strong>Total</strong>
                                </div>
                                <div class="col-6" style="border-right: 1px solid;border-bottom:1px solid;">
                                    {{'$'.number_format($total,2)}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('after-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script>
<script src="{{asset('js/jspdf.debug.js') }}"></script>
<script src="{{ asset('js/html2canvas.js') }}"></script>
    <script>
        function auto_grow(element) {
            element.style.height = "5px";
            element.style.height = (element.scrollHeight)+"px";
        }
        $(document).ready(function () {
           $('#pdfbtn').on('click',function(){
                var btnhtml=$(this).html();
                $(this).attr('disabled',true);
                $(this).html('<i class="fa fa-spin fa-spinner"></i> Processing...');
                $.ajax({
                    url: '{{route("admin.invoiceid.update")}}',
                    type: 'POST',
                    data: {_token: '{{csrf_token()}}',order_ids:'{{$order_ids}}', invoice_number:$("#custom_invoice_number").val()},
                    dataType: 'JSON',
                    success: function (data) { 
                        console.log(data);
                        var pdf = new jsPDF('p','pt','letter');
                        var specialElementHandlers = {
                        '#rentalListCan': function (element, renderer) {
                            return true;
                            }
                        };
                        pdf.canvas.width = $('#printable').width();
                        pdf.addHTML($('#printable')[0], function() {
                            pdf.save("Group invoice.pdf");
                        });
                        $('#pdfbtn').attr('disabled',false);
                        $('#pdfbtn').html(btnhtml);
                    }, error:function(err) {
                        console.log(err);
                        alert('System encountered an problem');
                        $('#pdfbtn').attr('disabled',false);
                        $('#pdfbtn').html(btnhtml);
                    }
                }); 
            });
        });
    </script>

@endpush