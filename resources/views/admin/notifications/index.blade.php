@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section("content")
<div class="card mb-5">
    <div class="card-header">
        
        @if(GetUserPermissions('notification_create'))
        <a href="{{ route('admin.notifications.add')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Add New</a>
        @endif
    </div>
    <div class="card-body">

        <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing p-0">
            @if ( session('message'))
            <div class="alert alert-success alert-dismissable custom-success-box" style="margin: 15px;">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong> {{ session('message') }} </strong>
            </div>
            @endif
            <div class="statbox widget box box-shadow">
                 <div class="table-responsive">
                           <table class="table table-condensed table-striped" id="notificaton_list">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>User Type</th>
                                    <th style="text-align: center">Image</th>
                                    <th>Created Date</th>
                                     <th>Actions</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notification_list as $key => $list)
                                <tr>

                                    <td>{{ $key  + 1 }}</td>
                                    <td>{{ $list->title }}</td>
                                    <td>{{ $list->description }}</td>
                                    <td>@if($list->user_type == 0) All @elseif($list->user_type == 2) User @else Vendor @endif</td>
                                    @if($list->image)
                                    <td style="text-align: center"><img src="{{ asset('/uploads/notifications/'.$list->image) }}" style="width: 60px;height: 60px;border-radius: 50%; object-fit: cover;" /></td>                                    
                                    @else
                                    <td></td>
                                    @endif
                                    <td>{{ get_date_in_timezone($list->created_at, config('global.datetime_format')) }}</td>
                                     <td>
                                        @if(GetUserPermissions('notification_delete'))
                                        <ul class="table-controls">
                                            <li>
                                              <a class="dropdown-item" data-role="unlink" data-message="Do you want to remove this notification?" href="{{url('admin/notifications/delete/'.$list->id)}}"><i class="flaticon-delete-1"></i></a>
                                            </li>
                                        </ul>
                                        @endif
                                    </td> 
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


     @endsection

@section('footerJs')
        @endsection

        @section("script")
<script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
<script> 
$('#notificaton_list').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "responsive": true,
    });
    </script>

@stop