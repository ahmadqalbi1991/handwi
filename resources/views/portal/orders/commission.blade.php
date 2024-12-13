@extends("portal.template.layout")

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
                        <div class="col-md-3 form-group">
                            <label>Date From</label>
                            <input type="text" name="from" class="form-control datepicker" autocomplete="off" value="{{ $from?date('m/d/Y',strtotime($from)):'' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Date To</label>
                            <input type="text" name="to" class="form-control datepicker" autocomplete="off" value="{{ $to?date('m/d/Y',strtotime($to)):'' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Search Order ID</label>
                            <input type="text" name="order_id" class="form-control" autocomplete="off" value="{{ $order_id }}">
                        </div>
                        <button type="submit" class="btn btn-warning mb-4 mr-2 btn-rounded" name="submit">Search</button>
                         <button type="submit" class="btn btn-warning mb-4 mr-2 btn-rounded" name="submit" value="export">Export</button>
                    </div>
                </form>
                

                    

                    <div class="row mt-3">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="column-filter_length">
                            </div>
                        </div>

                        
                    </div>
                    <div class="table-responsive">
                    <table class="table table-condensed table-striped display nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order ID</th>
                                <th>Invoice ID</th>
                                <th>Vendor </th>
                                <th>Admin Commission</th>
                                <th>Vendor Earning</th>
                                <th>Total</th>
                                <th>Payment Mode</th> 
                                {{-- <th>Status</th>  --}}
                                <th>Order Date</th>
                            </tr>
                        </thead>
                         <tbody>
                        @if (!empty($list))
                       

                            <?php $i = 0; ?>
                            @foreach ($list as $item)
                                <?php   $i++; ?>
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->order_id }}</td>
                                    <td>{{ $item->invoice_id }}</td>
                                    <td>{{ $item->vendor_name }}</td>
                                    <td>{{ $item->ad_comm }}</td>
                                    <td>{{ $item->vd_comm }}</td>
                                    <td>{{ $item->subtot }}</td>
                                    <td>@if($item->payment_mode==1)
        {{'COD'}}
    @else
        {{'CARD'}}
    @endif</td>
                                    {{-- <td>{{ order_status$item->status }}</td> --}}
                                    <td>{{web_date_in_timezone($item->created_at,'d-M-Y h:i A')}}</td>
                                    
                                </tr>
                            @endforeach
                             @else
                             <tr><td colspan="11" align="center">
                        <br>
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
     $(document).ready(function() { $('#example').DataTable( { dom: 'Bfrtip', buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print' ] } ); } );
 </script>
@stop