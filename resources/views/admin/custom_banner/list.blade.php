@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
          href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('buttons')
    <a class="btn btn-success btn-sm mt-4" href="{{ route('admin.app-banners.create') }}">Add Banner</a>
@endsection

@section("content")
    <div class="card mb-5">
        <div class="card-body">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
                <table class="table table-condensed table-striped ma7zouz-tables">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th style="text-align: center">Banner</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->bi_name }}</td>
                            <td class="text-center">
                                <img src="{{ $item->bi_image }}" width="150" alt="">
                            </td>
                            <td>
                                <label class="switch">
                                    <input {{ $item->bi_status ? 'checked' : '' }} type="checkbox"
                                           class="banner_status" data-id="{{ $item->id }}">
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <a class="text-success"
                                   href="{{ route('admin.app-banners.edit', ['banner_id' => $item->id]) }}"><i
                                            class="bx bx-pen"></i></a>
                                <a class="text-danger" href="javascript:void(0);"
                                   onclick="deleteBanner('{{ $item->id }}')"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script>

    </script>
@endsection