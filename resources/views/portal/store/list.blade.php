@extends('vendor.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
    <?php $role = Auth::user()->role;
if($role == 4) //store manager
{
    $privileges = \App\Models\UserPrivileges::join('users', 'users.id', 'user_privileges.user_id')
    ->join('designations', 'designations.id', '=', 'users.designation_id')->where(['users.id' => Auth::user()->id, 'user_privileges.designation_id' => \App\Models\User::where('id', Auth::user()->id)->pluck('designation_id')->first()])->pluck('privileges')->first();
    $privileges = json_decode($privileges, true);
} ?>
    <div class="card mb-5">
        <div class="card-header">
            <?php $privilege = 0; if($role == 4) { if(!empty($privileges['Store']['Create'])) { if($privileges['Store']['Create'] == 1) { $privilege = 1; } } } else {  $privilege = 1; } ?>
            @if($privilege == 1)
            <a href="{{ url('vendor/store/create') }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Store</a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-striped" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Vendor</th>
                            <th>Is Verified</th>
                            <th>Is Active</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($stores as $str)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    {{ $str->store_name }}
                                </td>
                                <td>+{{ $str->dial_code.' '.$str->mobile }}</td>
                                <td>{{ $str->vendor->name }}</td>
                                
                                <td>
                                    
                                    <label class="switch s-icons s-outline  s-outline-warning  mt-2 mb-2 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $str->id }}"
                                            data-url="{{ url('vendor/store/verify') }}"
                                            @if ($str->verified) checked @endif disabled>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="switch s-icons s-outline  s-outline-warning mt-2 mb-2 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $str->id }}"
                                            data-url="{{ url('vendor/store/change_status') }}"
                                            @if ($str->active) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>{{ get_date_in_timezone($str->created_at, 'd-M-y H:i A') }}</td>
                                <td class="text-center">
                                    <div class="dropdown custom-dropdown">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="flaticon-dot-three"></i>
                                        </a>
                                        
                                       
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                            <?php $privilege = 0; if($role == 4) { if(!empty($privileges['Store']['Edit'])) { if($privileges['Store']['Edit'] == 1) { $privilege = 1; } } } else {  $privilege = 1; } ?>
                                             @if($privilege == 1)
                                            <a class="dropdown-item"
                                                href="{{ url('vendor/store/edit/' . $str->id) }}"><i
                                                    class="flaticon-pencil-1"></i> Edit</a>
                                             @endif
                                            <?php $privilege = 0; if($role == 4) { if(!empty($privileges['Store']['Delete'])) { if($privileges['Store']['Delete'] == 1) { $privilege = 1; } } } else {  $privilege = 1; } ?>
                                            @if($privilege == 1)
                                            <a class="dropdown-item" data-role="unlink"
                                                data-message="Do you want to remove this store?"
                                                href="{{ url('vendor/store/delete/' . $str->id) }}"><i
                                                    class="flaticon-delete-1"></i> Delete</a>
                                            @endif
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
