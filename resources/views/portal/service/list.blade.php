@extends('portal.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <a href="{{ url('portal/services/create') }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Service</a>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-striped" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Is Active</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($categories as $category)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span>
                                        @if ($category->image != '')
                                            <img id="image-preview" style="width:100px; height:90px;"
                                                class="img-responsive mb-2" data-image="{{asset($category->image) }}"
                                                src="{{asset($category->image)}}">
                                        @endif
                                        </span>
                                        <span class="ml-2">
                                            <a href="#" class="yellow-text">{{ $category->name }}</a>
                                            <span>{{ $category->parent_name }}</span>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <label class="switch s-icons s-outline  s-outline-warning mt-2 mb-2 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $category->id }}"
                                            data-url="{{ url('portal/services/change_status') }}"
                                            @if ($category->active) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    @if(!empty($category->categories_selected))
                                        @foreach($category->categories_selected as $catint)
                                            {{$catint->name}}<br>
                                        @endforeach
                                    @endif
{{--                                    @if(!empty($category->service_category))--}}
{{--                                        @foreach($category->service_category as $catint)--}}
{{--                                    @foreach($categories as $service_category)--}}
{{--                                            {{$catint->service_category->name}}<br>--}}
{{--                                        @endforeach--}}
{{--                                    @endif--}}
{{--                                    @endforeach--}}
                                </td>
                                <td>{{ number_format($category->service_price, 2, '.', '')}}</td>
                                <td>{{ get_date_in_timezone($category->created_at, config('global.datetime_format')) }}</td>
                                <td class="text-center">
                                    <div class="dropdown custom-dropdown">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="flaticon-dot-three"></i>
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                            <a class="dropdown-item"
                                                href="{{ url('portal/services/edit/' . $category->id) }}"><i
                                                    class="flaticon-pencil-1"></i> Edit</a>
                                            <a class="dropdown-item" data-role="unlink"
                                                data-message="Do you want to remove this service?"
                                                href="{{ url('portal/services/delete/' . $category->id) }}"><i
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
