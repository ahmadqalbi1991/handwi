@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        @if(get_user_permission('activity_types', 'c'))
        <div class="card-header">
            <a href="{{ url('admin/activity_type/create') }}" class="btn-custom btn mr-2 mt-2 mb-2" style="display:none;"><i class="fa-solid fa-plus"></i> Create Activity Type</a>
            <!-- <a href="{{ url('admin/activity_type/sort') }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-arrow-up-wide-short"></i> Sort</a> -->
        </div>
        @endif
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-striped" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <!-- <th>Account Name</th> -->
                            <!-- <th>Status</th> -->
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($activityTypes as $activityType)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                                
                                <td>{{ $activityType->name }}</td>
                                <!-- <td>{{ $activityType->account->name ?? '' }}</td> -->
                                <!-- <td>
                                    <label class="switch s-icons s-outline  s-outline-warning  mb-4 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $activityType->id }}"
                                               data-url="{{ url('admin/activity_type/change_status') }}"
                                               @if ($activityType->status == '1') checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td> -->
                                <td>{{web_date_in_timezone($activityType->created_at,'d-M-Y h:i A')}}</td>
                                
                                <td class="text-center">
                                    <div class="dropdown custom-dropdown">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="flaticon-dot-three"></i>
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                            @if(get_user_permission('activity_types','u'))
                                            <a class="dropdown-item"
                                                href="{{ url('admin/activity_type/edit/' . $activityType->id) }}"><i
                                                    class="flaticon-pencil-1"></i> Edit</a>
                                            @endif 
                                            @if(get_user_permission('activity_types','d'))
                                            <a class="dropdown-item  d-none" data-role="unlink"
                                                data-message="Do you want to remove this activity?"
                                                href="{{ url('admin/activity_type/delete/' . $activityType->id) }}"><i
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
