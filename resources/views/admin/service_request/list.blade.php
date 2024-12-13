@extends("admin.template.layout")

@section('header')
<?php header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.
?>
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
        @php if (empty($_GET)) { @endphp
     <script>
       setTimeout(function(){
           location.reload();
       },15000); // 5000 milliseconds means 5 seconds.
    </script>
@php } @endphp
        
@stop


@section('content')
    <div class="card">
        <div class="card-body">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
                <form action="" method="get">
                    <div class="row align-items-end">
                        <div class="col-lg-3 col-md-6 form-group">
                            <label>Date From</label>
                            <input type="text" name="from" class="form-control flatpickr-input" autocomplete="off" value="{{ $from?date('Y-m-d',strtotime($from)):'' }}">
                        </div>
                        <div class="col-lg-3 col-md-6 form-group">
                            <label>Date To</label>
                            <input type="text" name="to" class="form-control flatpickr-input" autocomplete="off" value="{{ $to?date('Y-m-d',strtotime($to)):'' }}">
                        </div>
                        <div class="col-lg-3 col-md-6 form-group">
                            <label>Search Order ID</label>
                            <input type="text" name="order_id" class="form-control" autocomplete="off" value="{{ $order_id }}">
                        </div>
                        @if(isset($_GET['customer']))
                        <input type="hidden" name="customer" id="customer" value="{{$_GET['customer']??''}}">
                        @else
                        <div class="col-lg-3 col-md-6 form-group">
                            <label>Customer Name</label>
                            <input type="text" name="name" class="form-control" autocomplete="off" value="{{$name}}">
                        </div>
                        @endif
                        <div class="col-lg-3 col-md-6 form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" >

                                <option value="" >All</option>
                                <option value="0" {{is_numeric($status) && $status == 0 ? 'selected' : null;}}>Pending</option>
                                <option value="1" {{!empty($status) && $status == 1 ? 'selected' : null;}}>Accepted</option>
                                <option value="3" {{!empty($status) && $status == 3 ? 'selected' : null;}}>Ongoing</option>
                                <option value="4" {{!empty($status) && $status == 4 ? 'selected' : null;}}>Completed</option>
                                <option value="10" {{!empty($status) && $status == 10 ? 'selected' : null;}}>Canceled/Rejected</option>
                                <select>
                        </div>
                        <div class="col-lg-6 form-group">
                            <button type="submit" class="btn btn-primary  btn-rounded mt-2">Search</button>
                            @if(isset($_GET['customer']))
                            <a href="{{url('admin/service_request')}}?customer={{$_GET['customer']}}" class="btn btn-primary ml-2 btn-rounded mt-2">Clear</a>
                            @else
                            <a href="{{url('admin/service_request')}}" class="btn btn-primary ml-2 btn-rounded mt-2">Clear</a>
                            @endif
                        </div>
                       
                        <!-- <a href="{{asset('admin/service_request')}}"><button type="button" class="btn btn-warning mb-4 ml-2 btn-rounded">Clear</button>
                            <button type="button" class="btn btn-warning mb-4 ml-2 btn-rounded mutebutton"><i style='font-size:15px' class='fas'>&#xf6a9;</i> Mute</button> -->
                    </div>
                </form>
                

                    

                    <div class="row mt-3 d-none">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="column-filter_length">
                            </div>
                        </div>

                        
                    </div>
                    <div class="table-responsive">
                    <table class="table table-condensed table-striped" id="service_request_listing">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order ID</th>
                                <th>Service Request No</th>
                                <th>Activity</th>
                                <th>Customer </th>
                                <th>Total Amount</th>
                                <th>VAT</th>
                                <th>Discount</th> 
                                <th>Service Charge</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th>Payment Mode</th>
                                <th>Admin Commission</th>
                                <th>Created Date</th>
                                <th>Scheduled date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                         <tbody>
                            
                        @if ($list->isNotEmpty())
                      

                            <?php $i = $list->perPage() * ($list->currentPage() - 1); ?>
                            @foreach ($list as $item)
                                <?php   $i++; ?>
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td >
                                        <div style="display: flex; align-items: center; gap: 10px; font-size: 12px;">
                                            {{ $item->order_id }} @if($item->read_admin == 0) <span style="background: #c31718; padding: 2px 6px; border-radius: 5px; color: #fff; font-size: 10px;">New</span> @endif
                                        </div>
                                        
                                     </td>
                                    <td>{{ $item->order_no }}</td>
                                    <td>{{ $item->activity_type }}</td>
                                    <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                                    <td>{{ number_format($item->total, 2, '.', '') }}</td>
                                    <td>{{ number_format($item->vat, 2, '.', '') }}</td>
                                    <td>{{ number_format($item->discount, 2, '.', '') }}</td>
                                    <td>{{ number_format($item->service_charge, 2, '.', '') }}</td>
                                    <td>{{ number_format($item->grand_total, 2, '.', '') }}</td>
                                    <td>{{ service_order_status($item->status) }}</td>
                                    <td>{{ payment_mode($item->payment_mode) }}</td>
                                    <td>{{ number_format($item->admin_commission, 2, '.', '') }}</td>
                                    <td>{{ fetch_booking_created_at_date($item->order_id) }}</td>
                                    <td>{{ fetch_booking_date($item->order_id)}}</td>
                                    <td class="text-center">
                                        <a href="{{ url('admin/service_details/' . $item->order_id) }}" class="btn btn-info btn-sm"></i> Details</a>
                                    </td>
                                </tr>
                            @endforeach
                             @else
                             <tr><td colspan="12" align="center" class="pt-2 p-0">
                        
                        <!-- <div class="alert alert-warning">
                            <p>No Orders found</p>
                        </div> -->
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
$('#service_request_listing').DataTable({
        "paging": false, 
        "lengthChange": false, 
        "searching": false, 
        "ordering": true, 
        "info": false, 
        "autoWidth": false 
    });    
</script>
@stop