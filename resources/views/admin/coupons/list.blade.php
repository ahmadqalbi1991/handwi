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
    <div class="card-header"><a href="{{url('admin/coupons/create')}}" class="btn btn-warning mb-4 mr-2 btn-rounded"><i class="fa-solid fa-plus"></i> Create Promocode</a></div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Code</th>
                <th>Title</th>
                <th>Amount</th>
                <th>Expiry Date</th>
                <th>Users per Coupon</th>
                <th>Total Users used</th>
                <th>Applied To</th>
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
                        <td>{{$data->coupon_title}}</td>
                        <td>{{$data->coupon_amount}} @if($data->name == "%"){{$data->name}} @endif</td>
                        <td>{{date('Y-m-d', strtotime($data->coupon_end_date))}}</td>
                        <td>{{$data->coupon_used}}</td>
                        <td>{{$data->coupon_used_users}}</td>
                        <td>@if($data->applied_to == 3) Service @else Product @endif</td>
                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    <a class="dropdown-item" href="{{url('admin/coupons/'.$data->id.'/edit')}}"><i class="flaticon-pencil-1"></i> Edit</a>

                                    <a class="dropdown-item" data-role="unlink"
                                    data-message="Do you want to remove this coupon?"
                                    href="{{ url('admin/coupons/delete/' . $data->id) }}"><i
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