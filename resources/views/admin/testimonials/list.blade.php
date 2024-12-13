@extends('admin.template.layout')

@section('header')
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Total: <b class="primary-color">{{$testimonial->count()}}</b> Testimonials</h3>
        @if(GetUserPermissions('testimonials_view'))
            <a href="{{ url('admin/testimonials/create') }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Testimonial</a>
        
        @endif
        </div>
        <div class="card-body">
            <!-- <p>Total: {{$testimonial->count()}} Testimonials</p> -->
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-bordered" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Designation</th>

                            <th>Created Date</th>
                            <th data-orderable="false">Status</th>
                            <th data-orderable="false">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($testimonial as $category)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span>

                                            <img id="image-preview" style="width:100px; height:100px;"
                                                class="img-responsive mb-2 rounded-circle" data-image="{{ $category->user_image }}"
                                                src="{{ $category->user_image }}">

                                        </span>

                                    </div>
                                </td>
                                <td>{{ $category->name }}</td>
                                  <td>{{ $category->designation }}</td>
                                <td>{{web_date_in_timezone($category->created_at,config('global.datetime_format'))}}</td>
                                <td>
                                    <label class="switch s-icons s-outline  s-outline-warning mt-2 mb-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $category->id }}"
                                            data-url="{{ url('admin/testimonials/change_status') }}"
                                            @if ($category->active) checked @endif>
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
                                            @if(GetUserPermissions('testimonials_view'))
                                            <a class="dropdown-item"
                                                href="{{ url('admin/testimonials/edit/' . $category->id) }}"><i
                                                    class="fal fa-edit"></i> Edit</a>
                                            @endif
                                            @if(GetUserPermissions('testimonials_view'))
                                            <a class="dropdown-item" data-role="unlink"
                                                data-message="Do you want to remove this Testimonial?"
                                                href="{{ url('admin/testimonials/delete/' . $category->id) }}"><i
                                                    class="fal fa-trash"></i> Delete</a>
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
            "iDisplayLength": {{config('global.admin_per_pageitems')}},
            "ordering": true,
            "info": true,
            "autoWidth": true,
            "responsive": true,
        });
    </script>
@stop
