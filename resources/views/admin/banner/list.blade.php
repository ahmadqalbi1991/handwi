@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")
<div class="card mb-5">
    <div class="card-header"><a href="{{url('admin/banner/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Banner</a></div>
    <div class="card-body">
    <div class="dataTables_wrapper container-fluid dt-bootstrap4">
   

    <div class="row">
        <!-- <div class="col-sm-12 col-md-6">
            <div class="dataTables_length" id="column-filter_length">
            </div>
        </div> -->
        
        <form method="get" action='' class="col-lg-12">
            <div class="row row align-items-center">
                <div class="col-lg-6">
                    <div id="column-filter_filter" class="dataTables_filter">
                        <label>Banner Type</label>
                                    <select name="banner_type" class="form-control form-control-sm" required>
                                        <option> All </option>
                                        <option value="1" @if(!empty($bannertype)) {{$bannertype==1 ? "selected" : null}} @endif >{{banner_type(1)}}</option>
                                        <option value="2" @if(!empty($bannertype)) {{$bannertype==2 ? "selected" : null}} @endif >{{banner_type(2)}}</option>
                                        <option value="3" @if(!empty($bannertype)) {{$bannertype==3 ? "selected" : null}} @endif >{{banner_type(3)}}</option>
                                        <option value="4" @if(!empty($bannertype)) {{$bannertype==4 ? "selected" : null}} @endif >{{banner_type(4)}}</option>
                                        <option value="5" @if(!empty($bannertype)) {{$bannertype==5 ? "selected" : null}} @endif >{{banner_type(5)}}</option>
                                        <option value="6" @if(!empty($bannertypee)) {{$bannertype==6 ? "selected" : null}} @endif >{{banner_type(6)}}</option>
                                    </select>
                            
                        </div>
                </div>
                <div class="col-lg-3">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
           
            
      
        </form>
    </div>
    @if($list->total() > 0)
        <table class="table table-condensed table-striped" id="banners">
            <thead>
                <tr>
                <th>#</th>
                <th>Title</th>
                <th style="text-align: center">Banner</th>
                <th>Banner Type</th>
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
                   <td>{{$item->banner_title}}</td>
                   <td style="text-align: center"><?php if(!empty($item->banner_image)) { ?>
                            <img src="<?php echo url(config('global.upload_path').config('global.banner_image_upload_dir').$item->banner_image);?>" style="height: 60px;width: 60px;border-radius: 50%;object-fit:cover;" ><?php } ?></td>
                            <td>{{banner_type($item->banner_type)}}</td>
                            <td>{{$item->active ? 'Active' :'Inactive'}}</td>
                   <td>{{get_date_in_timezone($item->created_on,'d-M-y h:i A')}}</td>
                   <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    <a class="dropdown-item" href="{{url('admin/banner/edit/'.$item->id)}}"><i class="flaticon-pencil-1"></i> Edit</a>
                                    <a class="dropdown-item" data-role="unlink" data-message="Do you want to remove this banner?" href="{{url('admin/banner/delete/'.$item->id)}}"><i class="flaticon-delete-1"></i> Delete</a>

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