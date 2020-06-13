@extends('backend.layouts.app')

@section('title', __('labels.backend.contacts.title').' | '.app_name())




@section('content')

    <div class="card">
        <div class="card-header">

                <h3 class="page-title d-inline">@lang('labels.backend.contacts.title')</h3>


        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">


                        <table id="myTable"
                               class="table table-bordered table-striped ">
                            <thead>
                            <tr>

                                <th>@lang('labels.general.sr_no')</th>
                                <th>@lang('labels.backend.contacts.fields.name')</th>
                                <th>@lang('labels.backend.contacts.fields.email')</th>
                                <th>@lang('labels.backend.contacts.fields.phone')</th>
                                <th>@lang('labels.backend.contacts.fields.message')</th>
                                <th>@lang('labels.backend.contacts.fields.time')</th>
                                <th>@lang('labels.backend.certificates.fields.action')</th>
                            </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Delete Modal -->
        <div class="modal-danger mr-1 mb-1 d-inline-block">
          <div class="modal fade text-left" id="confirm-delete" tabindex="-1" role="dialog"
            aria-labelledby="myModalLabel160" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
              <div class="modal-content">
                <div class="modal-header bg-danger white">
                  <h5 class="modal-title" id="myModalLabel160">Delete Contact</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p>Would you like to delete Contact Request?</p>
                </div>
                <div class="modal-footer">
                  <form id="deleteContact" action="" method="post">
                    <input name="_method" type="hidden" value="DELETE">
                    @csrf
                    <input type="hidden" name="id">
                    <button class="btn btn-danger mr-1 mb-1 waves-effect waves-light" type="submit">Delete</button>
                    <button type="button" class="btn btn-light mr-1 mb-1 waves-effect waves-light" data-dismiss="modal">Close</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
@stop

@push('after-scripts')
    <script>

        $(document).ready(function () {
            var route = '{{route('admin.contact_requests.get_data')}}';

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
                ajax: route,
                columns: [

                    {data: "DT_RowIndex", name: 'DT_RowIndex'},
                    {data: "name", name: 'name'},
                    {data: "email", name: 'email'},
                    {data: "number", name: 'number'},
                    {data: "message", name: "message"},
                    {data: "created_at", name: "time"},
                    {data: 'name', name: 'name', orderable: false,render : function(data, type, row) {
                        return '<a class="btn btn-danger btn-sm" href="javascript:void(0)" data-toggle="modal" data-target="#confirm-delete" onclick=\'deleteModal('+row["id"]+')\' data-toggle="tooltip" data-original-title="Delete Contact" class="text-danger"><i class="fa fa-trash"></i></a>';
                        }
                     
                    },
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"width": "15%", "targets": 5},
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

        });
        function deleteModal(id) {
            $('#confirm-delete #deleteContact').attr('action','{{url("user/contact-request")}}/'+id+"/delete");
        }
    </script>

@endpush