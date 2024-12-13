@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-striped" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <!-- <th>Name</th>
                            <th>Mobile</th> -->
                            <th>Store Info</th>
                            <th>Vendor</th>
                            <th>Email</th>
                            <th>Is Active</th>
                            <th>Location</th>
                            <th>Created Date</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($stores as $str)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    <div class="mb-1">
                                        <a href="#" class="yellow-color">
                                        {{ $str->store_name }}
                                        </a>
                                    </div>
                                    <div class="">
                                    +{{ $str->dial_code.' '.$str->mobile }}
                                    </div>
                                </td>
                                <!-- <td>
                                    {{ $str->store_name }}
                                </td>
                                <td>+{{ $str->dial_code.' '.$str->mobile }}</td> -->
                                <td>{{ $str->vendor->name }}</td>
                                
                                <td>
                                    {{$str->business_email}}
                                </td>
                                
                                <td> @if($str->active) Active @else Inactive @endif
                                       
                                </td>
                                <td>
                                    {{$str->location}}
                                </td>
                                <td>{{ get_date_in_timezone($str->created_at, 'd-M-y H:i A') }}</td>
                                

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.js"></script>
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
