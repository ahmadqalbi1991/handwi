@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <a href="{{ url('admin/attributes/create') }}" class="btn btn-warning mb-4 mr-2 btn-rounded">Create Attribute</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-striped" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Attribute Name</th>
                            <th>Attribute Type</th>
                            <th>Industry Type</th>
                            <th>Is Active</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($attributes as $attr)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    {{ $attr->attribute_name }}
                                </td>
                                <td>{{ $attr->attribute_types->attribute_type_name }}</td>
                                <td>{{ $attr->industry_types->name }}</td>
                               
                                <td>
                                    <label class="switch s-icons s-outline  s-outline-warning  mb-4 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $attr->id }}"
                                            data-url="{{ url('admin/attributes/change_status') }}"
                                            @if ($attr->active) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>{{ get_date_in_timezone($attr->created_at, 'd-M-y H:i A') }}</td>
                                <td class="text-center">
                                    <div class="dropdown custom-dropdown">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="flaticon-dot-three"></i>
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                            
                                            <a class="dropdown-item"
                                                href="{{ url('admin/attribute_values/' . $attr->id) }}"><i
                                                    class="flaticon-view-1"></i>  Attribute Values</a>
                                            <a class="dropdown-item"
                                                href="{{ url('admin/attributes/edit/' . $attr->id) }}"><i
                                                    class="flaticon-pencil-1"></i> Edit</a>
                                            <a class="dropdown-item" data-role="unlink"
                                                data-message="Do you want to remove this attributes?"
                                                href="{{ url('admin/attributes/delete/' . $attr->id) }}"><i
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
