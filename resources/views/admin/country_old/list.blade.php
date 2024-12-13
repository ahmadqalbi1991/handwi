@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")
<div class="card mb-5">
    <div class="card-header"><a href="{{url('admin/country/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Country</a></div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Dial Code</th>
                <th>Status</th>
                <th>Created Date</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($countries as $country) 
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>{{$country->name}}</td>
                        <td>{{$country->prefix}}</td>
                        <td>{{$country->dial_code}}</td>
                        <td>@if($country->active) Active @else Inactive @endif</td>
                        <td>{{ get_date_in_timezone($country->created_at, config('global.datetime_format')) }}</td>
                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    <a class="dropdown-item" href="{{url('admin/country/'.$country->id.'/edit')}}"><i class="flaticon-pencil-1"></i> Edit</a>

                                    <a class="dropdown-item" data-role="unlink"
                                    data-message="Do you want to remove this country?"
                                    href="{{ url('admin/country/' . $country->id) }}"><i
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
      "columnDefs": [
        { "orderable": false, "targets": [0,6] },
      ]
    });
    </script>
@stop