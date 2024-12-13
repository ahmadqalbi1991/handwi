@extends("admin.template.layout")

@section("header")

@stop


@section("content")
<div class="card">

    <div class="card-body">
      <div class="row">
        <form method="get" action='' class="col-sm-12 col-md-12">
            <div id="column-filter_filter" class="dataTables_filter">
                <div class="row">
                    <div class="col-lg-3">
                        <label class="w-100">From Date:
                            <input type="date" name="from_date" class="form-control form-control-sm" id="min_date" placeholder="" aria-controls="column-filter" value="{{$from_date}}">
                        </label>
                    </div>
                    <div class="col-lg-3">
                        <label class="w-100">To Date:
                            <input type="date" name="to_date" class="form-control form-control-sm" id="max_date" placeholder="" aria-controls="column-filter" value="{{$to_date}}">
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
                          
                            <a href="{{url('admin/report/service_request')}}"  class="btn btn-primary">Clear</a>
                            <input type="submit" name="excel" value="Export" class="btn btn-success" >
                        </div>
                    </div>
                    
                </div>
                {{-- <label>From Date:
                    <input type="date" name="from_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$from_date}}">
                </label>
                <label>To Date:
                    <input type="date" name="to_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$to_date}}">
                </label>
                <button type="submit" class="btn btn-primary">Submit</button>
                <input type="submit" name="excel" value="Export" class="btn btn-primary" >
                <a href="{{url('admin/report/service_request')}}"  class="btn btn-primary">Clear</a> --}}
            </div>
        </form>
      </div>
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="service_request_report_listing">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order No</th>
                    <th>Customer</th>
                    <!-- <th>Discount</th> -->
                    <!-- <t>Grhand Total</th> -->
                    <!-- <th>Payment Mode</th> -->
                    <!-- <th>Booking Date</th> -->

                    <th>Admin Share</th>
                    <th>Vendor Share</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>VAT</th>
                    <th>Service Charge</th>
                    <th>Total</th>
                    <th>Payment Mode</th>
                    <th>Status</th> 
                    <th>Booking Date</th>
                </tr>
            </thead>
            <tbody>
               <?php $i = $list->perPage() * ($list->currentPage() - 1); ?>
                @foreach($list as $datarow)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>

                        <td>{{ $datarow->order_no ?? '' }} </td>
                        <td>{{ $datarow->users->first_name .' '.$datarow->users->last_name ?? '' }} </td>
                        <!-- <td>{{ number_format($datarow->discount, 2, '.', '') }}</td> -->
                        @php
                        $admin_commission = 0;
                        $vendor_commission = 0;
                        foreach ($datarow->services as $i_key => $p_val) {
                            $admin_commission = $admin_commission + $p_val->admin_commission;
                            $vendor_commission = $vendor_commission + $p_val->vendor_commission;
                        }
                        @endphp

                        <td>{{number_format($admin_commission, 2, '.', '')}}</td>
                        <td>{{number_format($vendor_commission, 2, '.', '')}}</td>
                        <td>{{number_format($datarow->total, 2, '.', '')}}</td>
                        <td>{{number_format($datarow->discount, 2, '.', '')}}</td>
                        <td>{{number_format($datarow->vat, 2, '.', '')}}</td>

                        <td>{{number_format($datarow->service_charge, 2, '.', '')}}</td>
                        <td>{{number_format($datarow->grand_total, 2, '.', '')}}</td>
                        <td>
                          {{payment_mode($datarow->payment_mode)}}
                        </td>
                        <td>{{ service_order_status($datarow->status) }}</td> 
                        <td>{{ get_date_in_timezone($datarow->created_at, config('global.datetime_format')) }}</td>
                        <!--<td>{{ get_date_in_timezone($datarow->booking_date, config('global.datetime_format')) }}</td>-->


                    </tr>
                @endforeach
            </tbody>
        </table>
        <span>Total {{ $list->total() }} entries</span>

            <div class="col-sm-12 col-md-12 pull-right">
                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                {{-- {!! $list->links('admin.template.pagination') !!} --}}
                {!! $list->appends(request()->input())->links('admin.template.pagination') !!}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section("script")
 <script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
<script>
    document.getElementById("min_date").onchange = function () {
        $('#max_date').val(' ');
        var input = document.getElementById("max_date");
        input.setAttribute("min", this.value);
    }
$('#service_request_report_listing').DataTable({
        "paging": false, 
        "lengthChange": false, 
        "searching": false, 
        "ordering": true, 
        "info": false, 
        "autoWidth": false 
    });    
</script>
@stop