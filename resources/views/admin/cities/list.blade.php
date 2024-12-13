@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <a href="{{ url('admin/cities/create') }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create City</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-striped ma7zouz-tables" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Country</th>
                            <th>Is Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($cities as $ct)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $ct->city_name }}</td>
                                <td>{{ !empty($ct->country) ? $ct->country->country_name : '' }}</td>
                                <td>
                                    <label class="switch s-icons s-outline  s-outline-warning mt-2 mb-2 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $ct->city_id }}"
                                            data-url="{{ url('admin/cities/change_status') }}"
                                            @if ($ct->city_status) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown custom-dropdown">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="flaticon-dot-three"></i>
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                            <a class="dropdown-item"
                                                href="{{ url('admin/cities/edit/' . $ct->city_id) }}"><i
                                                    class="flaticon-pencil-1"></i> Edit</a>
                                            <a class="dropdown-item" data-role="unlink"
                                                data-message="Do you want to remove this st?"
                                                href="{{ url('admin/cities/delete/' . $ct->city_id) }}"><i
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
            "columnDefs": [
                { "orderable": false, "targets": [0,4,6] },
            ]
        });
    </script>
@stop
