@extends("vendor.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")
<div class="card">
    <div class="card-header"><a href="{{url('vendor/store_managers/create')}}" class="btn btn-warning mb-4 mr-2 btn-rounded">Create Store manager</a></div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Image</th>
                <th>Name</th>
                <th>Store</th>
                <th>Active</th>
                <th>Last Updated</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($datamain as $datarow) 
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td><img src="{{$datarow->user_image}}" style="width:60px;height:60px;object-fit:cover;" class="rounded-circle" alt="User"></td>
                        <td>{{$datarow->name}}</td>
                        <td>{{$datarow->store_name}}</td>
                        <td>
                            <label class="switch s-icons s-outline  s-outline-warning  mb-4 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $datarow->id }}"
                                            data-url="{{ url('vendor/store_managers/change_status') }}"
                                            @if ($datarow->active) checked @endif>
                                        <span class="slider round"></span>
                            </label>
                        </td>
                        <td>{{$datarow->updated_at}}</td>
                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    <a class="dropdown-item" href="{{url('vendor/store_managers/'.$datarow->id.'/edit')}}"><i class="flaticon-pencil-1"></i> Edit</a>

                                    <a class="dropdown-item" data-role="unlink"
                                    data-message="Do you want to remove this Store manager?"
                                    href="{{ url('vendor/store_managers/' . $datarow->id) }}"><i
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