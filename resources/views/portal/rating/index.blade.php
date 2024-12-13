@extends('admin.layouts.master')
@section('headerFiles')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/custom_dt_zero_config.css') }}">

@endsection

@section('content')

<div class="container mb-5">
    <div class="page-header">
        <div class="page-title">
            <h3>Customers Inquiries List</h3>
        </div>
    </div>

    <div class="row layout-spacing ">

        <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-content widget-content-area">
                    <div class="table-responsive mb-4">
                        <table id="zero-config" class="table table-striped table-hover table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($queries as $key =>  $row)
                                    <tr>
                                        <td>{{ $key +1 }}</td>
                                        <td>{{ $row->full_name }}</td>
                                        <td>{{ $row->email }}</td>
                                        <td>{{ $row->mobile }}</td>
                                        <td>{{ $row->subject  }}</td>
                                        <td>{{ $row->message  }}</td>
                                        <td>{{ date('d-m-Y ',strtotime($row->created_at)) }}</td>
                                    </tr>                                    
                                @endforeach                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" class="form-validate form-delete" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                Are you sure you want to delete this record?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger deleteRecord">Delete</button>
            </div>
            <input type="hidden" name="id" id="id" value="">
        </form>
      </div>
    </div>
  </div>

@endsection

@section('footerJs')
    <script src="{{ asset('admin-assets/plugins/table/datatable/datatables.js') }}"></script>
    <script>
        $('#zero-config').DataTable({
            "language": {
                "paginate": { "previous": "<i class='flaticon-arrow-left-1'></i>", "next": "<i class='flaticon-arrow-right'></i>" },
                "info": "Showing page _PAGE_ of _PAGES_"
            }
        });

        function showDeleteModal(id){
            $("#id").val(id);
            $("#deleteModal").modal("show");
        }

        $(".deleteRecord").click(function(){
            $.ajax({
                type:'POST',
                url: "{{ route("admin.video.delete") }}",
                data: $('.form-delete').serialize(),
                success: function(response){
                    if(response.success){
                        toastr["success"](response.message);
                        setTimeout(function(){ 
                            window.location.href = "{{ route("admin.video.list")}}";
                        }, 1000);                        
                    } else {
                        toastr["error"](response.message);
                    }
                },
                error: function(){
                    toastr["error"]("Something went wrong");
                }
            });
        });
    </script>

@endsection