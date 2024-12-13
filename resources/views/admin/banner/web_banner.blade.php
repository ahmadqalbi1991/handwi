@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")
<div class="card mb-5">
    <div class="card-header"><a href="{{url('admin/web_banner/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Banner</a></div>
    <div class="card-body">
    <div class="dataTables_wrapper container-fluid dt-bootstrap4">
    @if($list->total() > 0)

    <div class="row">
        <div class="col-sm-12 col-md-6">
            <div class="dataTables_length" id="column-filter_length">
            </div>
        </div>
        
        <form method="get" action='' class="col-sm-12 col-md-6">
            <div id="column-filter_filter" class="dataTables_filter">
                <label>Search:
                    <input type="search" name="search_key" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$search_key}}">
                </label>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
        <table class="table table-condensed table-striped">
            <thead>
                <tr>
                <th>#</th>
                <th>Title</th>
                <th>Status</th>
                <th>Created Date</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php $i = $list->perPage() * ($list->currentPage() - 1); ?>
            @foreach($list as $item)
            <?php $i++; ?>
               <tr>
                   <td>{{$i}}</td>
                   <td>{{$item->banner_title_1}}</td>
                   <td>{{$item->active ? 'Active' :'Inactive'}}</td>
                   <td>{{get_date_in_timezone($item->created_on,'d-M-y H:i A')}}</td>
                   <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    <a class="dropdown-item" href="{{url('admin/web_banner/edit/'.$item->id)}}"><i class="flaticon-pencil-1"></i> Edit</a>
                                    <a class="dropdown-item" data-role="unlink" data-message="Do you want to remove this banner?" href="{{url('admin/web_banner/delete/'.$item->id)}}"><i class="flaticon-delete-1"></i> Delete</a>
                                </div>
                            </div> 
                        </td>
               </tr>
            @endforeach
            </tbody>
        </table>
       
            
            <div class="col-sm-12 col-md-12 pull-right">
                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                {!! $list->links('admin.template.pagination') !!}
                </div>
            </div>
        
        @else
        <br>
        <div class="alert alert-warning">
            <p>No banner found</p>
        </div>
        @endif
    </div>
    </div>
</div>
@stop

@section("script")
<script src="{{asset('')}}admin_assets/plugins/table/datatable/datatables.js"></script>
<script>
$('#example2').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "responsive": true,
    });
    </script>
@stop