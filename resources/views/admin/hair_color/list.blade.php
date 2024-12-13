@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
    <style>
        .color-box {
    width: 60px;
    height: 60px;
    display: inline-block;
    background-color: #ccc;
    position: absolute;
    left: 5px;
    top: 5px;
}
    </style>
@stop


@section("content")

<style>
    #example2_info{
        display: none
    }
</style>
<div class="card mb-5">
    <div class="card-header"><a href="{{url('admin/hair_color/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Color</a></div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Name</th>
                <th>Color</th>
                <th>Active</th>
                <th>Created</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($colors as $datarow) 
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>{{$datarow->name}}</td>
                        <td> 
                            <div class="color-box" style="background-color: {{$datarow->color}};"></div>
                        </td>

                       
                        <td>
                            <label class="switch s-icons s-outline  s-outline-warning  mb-4 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $datarow->id }}"
                                            data-url="{{ url('admin/hair_color/change_status') }}"
                                            @if ($datarow->active) checked @endif>
                                        <span class="slider round"></span>
                            </label>
                        </td>
                        <td>{{$datarow->created_at}}</td>
                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    <a class="dropdown-item" href="{{url('admin/hair_color/'.$datarow->id.'/edit')}}"><i class="flaticon-pencil-1"></i> Edit</a>
                                    <a class="dropdown-item" data-role="unlink"
                                    data-message="Do you want to remove this Color?"
                                    href="{{ url('admin/hair_color/' . $datarow->id) }}"><i
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