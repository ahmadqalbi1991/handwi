@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")
<div class="card mb-5">
    <div class="card-header"><a href="{{url('admin/promotion/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Promotion</a></div>
    <div class="card-body">
    <div class="dataTables_wrapper container-fluid dt-bootstrap4">
   

    <div class="row">
        <!-- <div class="col-sm-12 col-md-6">
            <div class="dataTables_length" id="column-filter_length">
            </div>
        </div> -->
        
        
    </div>
    @if($list->total() > 0)
        <table class="table table-condensed table-striped" id="banners">
            <thead>
                <tr>
                <th>#</th>
                <th>Title</th>
                <th style="text-align: center">Banner</th>
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
                   <td>{{$item->title}}</td>
                   <td style="text-align: center"><?php if(!empty($item->image_name)) { ?>
                            <img src="<?php echo url(config('global.upload_path').config('global.banner_image_upload_dir').$item->image_name);?>" style="height: 60px;width: 60px;border-radius: 50%;object-fit:cover;" ><?php } ?></td>
                           
                            <td>{{$item->status ? 'Active' :'Inactive'}}</td>
                   <td>{{get_date_in_timezone($item->created_at,'d-M-y h:i A')}}</td>
                   <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    <a class="dropdown-item" href="{{url('admin/promotion/edit/'.$item->id)}}"><i class="flaticon-pencil-1"></i> Edit</a>
                                    <a class="dropdown-item" data-role="unlink" data-message="Do you want to remove this banner?" href="{{url('admin/promotion/delete/'.$item->id)}}"><i class="flaticon-delete-1"></i> Delete</a>

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
<script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
<script> 
$('#banners').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "responsive": true,
    });
    </script>
@stop