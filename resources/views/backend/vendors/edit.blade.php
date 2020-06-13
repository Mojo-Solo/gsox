@extends('backend.layouts.app')
@section('title', __('labels.backend.access.vendors.management').' | '.app_name())

@section('content')
    {{ html()->modelForm($vendor, 'PATCH', route('admin.vendors.update', $vendor->id))->class('form-horizontal')->acceptsFiles()->open() }}
<link rel="stylesheet" href="{{url('public/js/jquery.multiselect.css')}}">
<script src="{{url('public/js/jquery.min.js')}}"></script>
<script src="{{url('public/js/jquery.multiselect.js')}}"></script>
    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">@lang('labels.backend.access.vendors.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.vendors.index') }}"
                   class="btn btn-success">@lang('labels.backend.access.vendors.view')</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group row">
                        {{ html()->label(__('Your Name'))->class('col-md-2 form-control-label')->for('name') }}

                        <div class="col-md-10">
                            {{ html()->text('name')
                                ->class('form-control')
                                ->placeholder(__('Name'))
                                ->attribute('maxlength', 255)
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('Your Email Address'))->class('col-md-2 form-control-label')->for('email') }}

                        <div class="col-md-10">
                            <input type="email" value="{{$vendor->email}}" disabled readonly class="form-control">
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.Supervisors.fields.password'))->class('col-md-2 form-control-label')->for('password') }}

                        <div class="col-md-10">
                            {{ html()->password('password')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.Supervisors.fields.password'))
                            }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.company_name'))->class('col-md-2 form-control-label')->for('company_name') }}

                        <div class="col-md-10">
                            {{ html()->text('company_name')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.company_name'))
                                ->attribute('maxlength', 255)
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('Billing contact name'))->class('col-md-2 form-control-label')->for('contact_name') }}

                        <div class="col-md-10">
                            {{ html()->text('contact_name')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.contact_name'))
                                ->attribute('maxlength', 255)
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('Billing contact email'))->class('col-md-2 form-control-label')->for('contact_email') }}

                        <div class="col-md-10">
                            {{ html()->email('contact_email')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.contact_email'))
                                ->attributes(['maxlength'=> 255])
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.phone_number'))->class('col-md-2 form-control-label')->for('phone_number') }}

                        <div class="col-md-10">
                            {{ html()->text('phone_number')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.phone_number'))
                                ->attribute('maxlength', 255)
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row hide">
                        <!-- {{ html()->label(__('labels.backend.access.vendors.table.country_id'))->class('col-md-2 form-control-label')->for('country_id') }} -->

                        <div class="col-md-10">
                            <input type="hidden" name="country_id" value="United States">
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.address1'))->class('col-md-2 form-control-label')->for('address1') }}

                        <div class="col-md-10">
                            {{ html()->text('address1')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.address1'))
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.address2'))->class('col-md-2 form-control-label')->for('address2') }}

                        <div class="col-md-10">
                            {{ html()->text('address2')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.address2'))
                             }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.city'))->class('col-md-2 form-control-label')->for('city') }}

                        <div class="col-md-10">
                            {{ html()->text('city')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.city'))
                                ->attribute('maxlength', 255)
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.state'))->class('col-md-2 form-control-label')->for('state') }}

                        <div class="col-md-10">
                            {{ html()->text('state')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.state'))
                                ->attribute('maxlength', 255)
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.zip'))->class('col-md-2 form-control-label')->for('zip') }}

                        <div class="col-md-10">
                            {{ html()->text('zip')
                                ->class('form-control')
                                ->placeholder(__('labels.backend.access.vendors.table.zip'))
                                ->attribute('maxlength', 255)
                                ->required() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <div class="form-group row">
                        {{ html()->label(__('Confirmed'))->class('col-md-2 form-control-label')->for('invoicing') }}
                        <div class="col-md-10">
                            @if($vendor->confirmed)
                            {{ Form::checkbox('confirmed',$vendor->confirmed,true) }}
                            @else
                            {{ Form::checkbox('confirmed') }}
                            @endif
                        </div><!--col-->
                    </div>

                    <div class="form-group row">
                        {{ html()->label(__('labels.backend.access.vendors.table.invoicing'))->class('col-md-2 form-control-label')->for('invoicing') }}
                        <div class="col-md-10">
                            @if($vendor->invoicing)
                            {{ Form::checkbox('invoicing',$vendor->invoicing,true) }}
                            @else
                            {{ Form::checkbox('invoicing') }}
                            @endif
                        </div><!--col-->
                    </div>
                    @if(!auth()->user()->hasRole('supervisor'))
                    <div class="form-group row clients_div" id="clients_div">
                        {{ html()->label(__('Select Client(s)'))->class('col-md-2 form-control-label')->for('clients') }}
                        <div class="col-md-10">
                            <select name="clients[]" multiple id="clients">
                                @foreach(\App\Models\Client::all() as $key => $client)
                                    <option value="{{$client->id}}" {{(in_array($client->id, explode(",",$vendor->clients)))?'selected':''}}>{{$client->name}}</option>
                                @endforeach
                            </select>
                        </div><!--col-->
                    </div>
                    <script>
                        $('#clients').multiselect({
                            columns: 1,
                            placeholder: 'Select Client(s)',
                            search: true,
                            selectAll: true
                        });
                    </script>
                    @endif
                    <div class="form-group row justify-content-center">
                        <div class="col-4">
                            {{ form_cancel(route('admin.vendors.index'), __('buttons.general.cancel')) }}
                            {{ form_submit(__('buttons.general.crud.update')) }}
                        </div>
                    </div><!--col-->
                </div>
            </div>
        </div>

    </div>
    {{ html()->closeModelForm() }}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
@if(!$vendor->invoicing)
<!-- <script>
    $('#clients_div').hide();
    $('#clients_div').hide();
</script> -->
@endif
<script>
$(document).ready(function(){
    // $('input[name="invoicing"]').click(function(){
    //     if($(this).prop('checked') == false){
    //         $(this).removeAttr("checked");
    //         $(this).val(0);
    //         $('#clients_div').hide();
    //     }
    //     else
    //     { 
    //         $(this).attr("checked","checked");
    //         $(this).val(1);
    //         $('#clients_div').show();
    //     }
    // });
});
</script>
@endsection