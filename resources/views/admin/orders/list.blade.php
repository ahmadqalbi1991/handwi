@extends("admin.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
    <div class="card">
        <div class="card-body">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
                <form action="" method="get">
                    <div class="row">
                        <div class="col-md-3 mb-3 ">
                            <label for="container_in" class="form-label">Order Status</label>
                            <select type="text" class="form-control select-nosearch" id="status" name="status">
                            <option value="">All</option>                            
                            <option value="0" {{$status =='0'  ? 'selected' : ''}}>{{order_status(0)}}</option>
                            <option value="1" {{$status =='1'  ? 'selected' : ''}}>{{order_status(1)}}</option>
                            <option value="2" {{$status =='2'  ? 'selected' : ''}}>{{order_status(2)}}</option>
                            <option value="3" {{$status =='3'  ? 'selected' : ''}}>{{order_status(3)}}</option>
                            <option value="4" {{$status =='4'  ? 'selected' : ''}}>{{order_status(4)}}</option>
                            <option value="10" {{$status =='10' ? 'selected' : '' }}>{{order_status(10)}}</option>
                            <option value="11" {{$status =='11' ? 'selected' : '' }}>{{order_status(11)}}</option>
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Date From</label>
                            <input type="text" name="from" class="form-control flatpickr-input" autocomplete="off" value="{{ $from?date('Y-m-d',strtotime($from)):'' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Date To</label>
                            <input type="text" name="to" class="form-control flatpickr-input" autocomplete="off" value="{{ $to?date('Y-m-d',strtotime($to)):'' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Search Order No</label>
                            <input type="text" name="order_id" class="form-control" maxlength="18" autocomplete="off" value="{{ $order_id }}">
                        </div>
                        @if(isset($_GET['customer']))
                        <input type="hidden" name="customer" id="customer" value="{{$_GET['customer']??''}}">
                        @else
                        <div class="col-md-3 form-group">
                            <label>Customer Name</label>
                            <input type="text" name="name" class="form-control" autocomplete="off" value="{{$name}}">
                        </div>
                        @endif
                        <div class="col-md-3 form-group mt-4">
                            <button type="submit" class="btn btn-warning mb-4 ml-2 btn-rounded">Search</button>
                            @if(isset($_GET['customer']))
                            <a href="{{url('admin/orders')}}?customer={{$_GET['customer']}}" class="btn btn-primary mb-4 ml-2 btn-rounded">Clear</a>
                            @else
                            <a href="{{url('admin/orders')}}" class="btn btn-primary mb-4 ml-2 btn-rounded">Clear</a>
                            @endif
                        </div>

                    </div>
                </form>
                

                    

                    <div class="row mt-3 d-none">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="column-filter_length">
                            </div>
                        </div>

                        
                    </div>
                    <div class="table-responsive">
                    <table class="table table-condensed table-striped" id="orders_listing">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order No</th>
                                <!-- <th>Order ID</th> -->
                                <th>Customer </th>
                                {{--<th>Discount</th>
                                <th>VAT</th>--}}
                                <!-- <th>Admin Commission</th> -->
                                <!-- <th>Vendor Earning</th> -->
                                <!-- <th>Shipping Charge</th> -->
                                <th>Total</th>
                                <th>Payment Mode</th> 
                                <th>Order Status</th> 
                                {{-- <th>Status</th>  --}}
                                <!-- <th>Created Date</th> -->
                                <th>Booking Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                           
                         <tbody>
                        @if ($list->total() > 0)
                       

                            <?php $i = $list->perPage() * ($list->currentPage() - 1); ?>
                            @foreach ($list as $item)
                                <?php   $i++; ?>
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td> <?php echo config('global.sale_order_prefix').date(date('Ymd', strtotime($item->created_at))).$item->order_id; ?></td>
                                    <!-- <td>{{ $item->order_id }}</td> -->
                                    <td>{{ $item->customer_name }}</td>
                                   {{-- <td>{{ $item->discount }}</td>
                                    <td>{{ $item->vat }}</td>--}}
                                    <!-- <td>{{ $item->admin_commission }}</td> -->
                                    <!-- <td>{{ $item->vendor_commission }}</td> -->
                                    <!-- <td>0</td> -->
                                    <td>{{ $item->grand_total }}</td>
                                    <td>{{payment_mode($item->payment_mode)}}</td>
                                    <td>{{ order_status($item->status) }}</td>
                                    <!-- <td>{{ get_date_in_timezone($item->created_at, 'd-M-y H:i A') }}</td> -->
                                    <td>{{ get_date_in_timezone($item->booking_date, 'd-M-y h:i A') }}</td>
                                    <td class="text-center">
                                        <a href="{{ url('admin/order_details/' . $item->order_id) }}" class="btn btn-info btn-sm"></i> Details</a>
                                    </td>
                                </tr>
                            @endforeach
                             @else
                             <tr><td colspan="12" align="center" class="pt-2 p-0">
                        
                        <div class="alert alert-warning">
                            <p>No Orders found</p>
                        </div>
                    </td>
                </tr>
                    @endif
                        </tbody>
                       
                    </table>
                </div>


                    <div class="col-sm-12 col-md-12 pull-right">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                            {!! $list->links('admin.template.pagination') !!}
                        </div>
                    </div>

                
            </div>
        </div>
    </div>

   
@stop

@section('script')
    <script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
<script>
$('#orders_listing').DataTable({
        "paging": false, 
        "lengthChange": false, 
        "searching": false, 
        "ordering": true, 
        "info": false, 
        "autoWidth": false 
    });    
</script>
@stop