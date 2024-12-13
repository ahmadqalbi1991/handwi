@extends("admin.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
                <form action="" method="get">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Date From</label>
                            <input type="text" name="from" class="form-control flatpickr-input" autocomplete="off" value="{{ $from?date('Y-m-d',strtotime($from)):'' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Date To</label>
                            <input type="text" name="to" class="form-control flatpickr-input" autocomplete="off" value="{{ $to?date('Y-m-d',strtotime($to)):'' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Search Order ID</label>
                            <input type="text" name="order_id" class="form-control" autocomplete="off" value="{{ $order_id }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Customer Name</label>
                            <input type="text" name="name" class="form-control" autocomplete="off" value="{{$name}}">
                        </div>
                        <button type="submit" class="btn btn-warning mb-4 ml-2 btn-rounded">Search</button>
                        <a href="{{asset('admin/report/refund_request_services')}}"><button type="button" class="btn btn-warning mb-4 ml-2 btn-rounded">Clear</button></a>
                    </div>
                </form>
                

                    

                    <div class="row mt-3 d-none">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="column-filter_length">
                            </div>
                        </div>

                        
                    </div>
                    <div class="table-responsive">
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Order No</th>
                                <th>Customer </th>
                                <th>Phone </th>
                                <th>Email </th>
                                {{--<th>Discount</th>
                                <th>VAT</th>--}}
                                <th>Total</th>
                                <th>Payment Refund Mode</th> 
                                {{-- <th>Status</th>  --}}
                                <th>Request Date</th>
                                <th>Order Date</th>
                                <th>Status</th>
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
                                    <td> <a href="{{asset('admin/service_details/'.$item->order_id)}}" class="yellow-color"><?php echo config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($item->created_at))).$item->order_id; ?></a></td>
                                    <td>{{ $item->customer_name }}</td>
                                    <td>{{ $item->dial_code }} {{ $item->phone }}</td>
                                    <td>{{ $item->email }}</td>
                                   {{-- <td>{{ $item->discount }}</td>
                                    <td>{{ $item->vat }}</td>--}}
                                    <td>{{ $item->grand_total }}</td>
                                    <td>@if($item->refund_method==1)
        Wallet
    @else
        Bank
    @endif
                                        
                                        </td>
                                    {{-- <td>{{ order_status$item->status }}</td> --}}
                                    <td>{{ get_date_in_timezone($item->refund_requested_date, 'd-M-y h:i A') }}</td>
                                    <td>{{ get_date_in_timezone($item->created_at, 'd-M-y h:i A') }}</td>
                                    <td>@if($item->refund_accepted != 1)<span class="btn btn-warning" data-role="status-change" href="{{url('admin/report/change_status_accepted_service')}}"  value="{{$item->order_id}}" >Accept</span>
                                    @else <span class="btn btn-default" >Accepted</span> @endif </td>
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
    <script src="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.js"></script>
     <script>
        $('body').off('click', '[data-role="status-change"]');
        $('body').on('click', '[data-role="status-change"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to change status?';
            var href = $(this).attr('href');
            var order_id = $(this).attr('value');
            var title = $(this).data('title') || 'Confirm Status Change';

            App.confirm(title, msg, function() {
                var ajxReq = $.ajax({
                    url: href,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "order_id": order_id,
                    },
                    success: function(res) {
                        if (res['status'] == 1) {
                            App.alert(res['message'] || 'Status changed successfully', 'Success!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);

                        } else {
                            App.alert(res['message'] || 'Unable to change the record.',
                                'Failed!');
                        }
                    },
                    error: function(jqXhr, textStatus, errorMessage) {

                    }
                });
            });

        });
        </script>
@stop