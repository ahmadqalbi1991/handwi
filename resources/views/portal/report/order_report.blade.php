@extends("portal.template.layout")
@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
<div class="card mb-5">
        <div class="card-body">
            <div class="row">
           
                <form action="" method="get" class="col-sm-12 col-md-12">
                    <div id="column-filter_filter" class="dataTables_filter">
                        <div class="row">
                            <div class="col-lg-3">
                                <label class="w-100">From Date:
                                    <input type="date" name="from_date" class="form-control form-control-sm" id="min_date" placeholder="" aria-controls="column-filter" value="{{$from}}">
                                </label>
                            </div>
                            <div class="col-lg-3">
                                <label class="w-100">To Date:
                                    <input type="date" name="to_date" class="form-control form-control-sm" id="max_date" placeholder="" aria-controls="column-filter" value="{{$to}}">
                                </label>
                            </div>
                            <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Order Number</label>
                                        <input type="text" class="form-control" id="order_number" name="order_number"
                                               placeholder="Enter Order Number" value="{{$_GET['order_number']??''}}">
                                    </div>
        
                            <div class="col-lg-6">
                                <div class=" mt-3">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    @if(\Auth::user()->role==3)
                                        <a href="{{url('portal/order_report')}}"  class="btn btn-primary">Clear</a>
                                    @else
                                        <a href="{{url('admin/report/orders')."?activity=".request()->activity}}"  class="btn btn-primary">Clear</a>
                                    @endif
                                    <input type="submit" name="excel" value="Export" class="btn btn-success" >
                                </div>
                            </div>
                            
                        </div>
                    {{-- <label>From Date:
                    <input type="date" name="from_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$from}}">
                </label>
                <label>To Date:
                    <input type="date" name="to_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$to}}">
                </label>
                <button type="submit" class="btn btn-primary">Submit</button>
                <input type="submit" name="excel" value="Export" class="btn btn-primary" >
                <a href="{{url('admin/report/orders')}}"  class="btn btn-primary">Clear</a> --}}
                </div>
                </form>
            </div>    
                   
                    <div class="table-responsive">
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Order No</th>
                                <!-- <th>Order ID</th> -->
                                <th>Customer</th>
                                <!-- <th>Customer Mobile</th> -->
                                <!-- <th>VAT</th> -->
                                <th>Admin Share</th>
                                <th>Vendor Share</th>
                                <th>Subtotal</th>
                                <th>Discount</th>
                                <th>VAT</th>
                                <th>Total</th>
                                <th>Payment Mode</th> 
                                <th>Delivery Mode</th> 
                                <th>Order Status</th> 
                                <!-- <th>Created Date</th> -->
                                <th>Booking Date</th>
                            </tr>
                        </thead>
                         <tbody>
                        @if ($list->total() > 0)
                       

                            <?php $i = $list->perPage() * ($list->currentPage() - 1); ?>
                            @foreach ($list as $item)
                                <?php   $i++; ?>
                                <tr>
                                    <td>{{ $i }}</td>
                                    <!-- <td>{{ $item->order_id }}</td> -->
                                    <td> <?php echo $item->order_no; ?></td>
                                    <!-- <td>{{ $item->order_id }}</td> -->
                                    <td>{{ $item->customer_name }} <br> {{ $item->customer_mobile }}</td>
                                    <!-- <td></td> -->
                                    <td>{{ $item->admin_commission_per ?? 0 }}</td>
                                    <td>{{ $item->vendor_commission_per ?? 0 }}</td>
                                   <td>{{ $item->total }}</td>
                                   <td>{{ $item->discount }}</td>
                                    <td>{{ $item->vat }}</td>
                                    <td>{{ $item->grand_total }}</td>
                                    <td>{{payment_mode($item->payment_mode)}}</td>
                                    <td>{{order_type($item->order_type)}}</td>
                                    <td>{{ order_status($item->status) }}</td> 
                                    <!-- <td>{{ get_date_in_timezone($item->created_at, 'd-M-y h:i A') }}</td> -->
                                    <td>{{ get_date_in_timezone($item->created_at, 'd-M-y h:i A') }}</td>
                                    <!-- <td class="text-center">
                                        <a href="{{ url('admin/order_details/' . $item->order_id) }}" class="btn btn-info btn-sm"></i> Details</a>
                                    </td> -->
                                </tr>
                            @endforeach
                             @else
                             <tr><td colspan="12" align="center" class="pt-2 p-0">
                        
                        <div class="alert alert-warning">
                            <p>No Record Found</p>
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
@endsection

@section('script')
    <script src="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.js"></script>
<script type="text/javascript">
    document.getElementById("min_date").onchange = function () {
        $('#max_date').val(' ');
        var input = document.getElementById("max_date");
        input.setAttribute("min", this.value);
    }
</script>
    
@endsection
