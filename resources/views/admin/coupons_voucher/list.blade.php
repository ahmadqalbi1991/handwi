@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")

<style>
    .dataTables_filter, .dataTables_length{
        margin-bottom: 5px !important;
    }
</style>
<div class="card mb-5">
    @if(GetUserPermissions('coupon','Create'))
    <div class="card-header"><a href="{{url('admin/coupons_voucher/create?brand=') . request()->brand}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Coupon</a>
    <!-- <a href="{{ url('admin/coupons/sort') }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-arrow-up-wide-short"></i> Sort</a> -->
    </div>
    @endif
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Code</th>
                <th>Title</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Status</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($datamain as $data) 
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>{{$data->coupon_code}}</td>
                        <td>{{$data->title}}</td>
                        <td>{{$data->brand->name??''}}</td>
                        <td>{{$data->category->name??''}}</td>
                        <td>{{$data->active == 1 ? 'Active' : 'Inactive'}}</td>
                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    @if(GetUserPermissions('coupon','Edit'))
                                    <a class="dropdown-item" href="{{url('admin/coupons_voucher/edit/' .$data->id.'?brand=' .request()->brand)}}"><i class="flaticon-pencil-1"></i> Edit</a>
                                    @endif

                                    @if(GetUserPermissions('coupon','Delete'))
                                    <a class="dropdown-item" data-role="unlink"
                                    data-message="Do you want to remove this coupon?"
                                    href="{{ url('admin/coupons_voucher/delete/' . $data->id) }}"><i
                                        class="flaticon-delete-1"></i> Delete</a>
                                    @endif
                                    
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