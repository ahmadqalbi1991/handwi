@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")
<div class="card mb-5">
    <!--<div class="card-header"><a href="{{url('admin/page/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Page</a></div>-->
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="cms_pages">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cms_pages as $key => $policy)
                <tr>
                    <td>{{ $key +1 }}</td>
                    <td>{{ $policy->title_en }}</td>
                    <td>{{ $policy->status == 1 ? "Active" : "In Active" }}</td>
                    <td>{{ date('d-m-Y ',strtotime($policy->created_at)) }}</td>
                    <td class="align-center">
                        <div class="dropdown custom-dropdown">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <i class="flaticon-dot-three"></i>
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                <a class="dropdown-item" href="{{ route('admin.cms_pages.edit', ['id' => $policy->id])}}"><i class="flaticon-pencil-1"></i>Edit</a>
                                <a class="dropdown-item" data-role="unlink"
                                data-message="Do you want to remove this page?"
                                href="{{ url('admin/page/delete/' . $policy->id) }}"><i
                                    class="flaticon-delete-1"></i> Delete</a>
                              </div>
                        </div>
                    </td>
                </tr>                                    
            @endforeach    
            </tbody>
        </table>
        </div>
    </div>
</div>

@stop

@section("script")
<script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
<script> 
$('#cms_pages').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "responsive": true,
    });
    </script>

@stop