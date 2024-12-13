@extends('admin.template.layout')

@section('header')
    <link href="{{ asset('') }}admin-assets/assets/css/support-chat.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/maps/vector/jvector/jquery-jvectormap-2.0.3.css" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/charts/chartist/chartist.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}admin-assets/assets/css/default-dashboard/style.css" rel="stylesheet" type="text/css"/>
@stop

@section('buttons')
    <a href="{{ route('admin.faq.create') }}" class="btn btn-sm btn-primary mt-4">Add FAQ</a>
@endsection

@section('content')

    <style>

        .home-section footer {
            bottom: auto !important;
        }

        .custom-container {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        /*body.dark{*/
        /*    background: url('



        {{ asset('') }}    admin-assets/assets/img/laconcierge-bg.jpg');*/
        /*    background-size: 100% 100%;*/
        /*    background-position: center;*/
        /*    background-repeat: no-repeat;*/
        /*}*/
        .custom-wl {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;

        }

        .custom-wl li {
            width: 250px;
            list-style-type: none;
        }

        .custom-wl li .icon-card {
            /*max-width: 320px;*/
            margin: auto;
            min-height: 250px;
        }
    </style>

    <div class="custom">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered ma7zouz-tables">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($faqs as $content)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $content->faq_title }}</td>
                                    <td>
                                        <label class="switch">
                                            <input {{ $content->status ? 'checked' : '' }} type="checkbox" class="cms_status" data-id="{{ $content->faq_id }}">
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <a class="text-success" href="{{ route('admin.faq.edit', ['id' => $content->faq_id]) }}"><i class="bx bx-pen"></i></a>
                                        <a class="text-danger" href="javascript:void(0);" onclick="deleteFaq('{{ $content->faq_id }}')"><i class="bx bx-trash"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="product-details">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        $('.cms_status').on('change', function () {
            let status = 0;
            if ($(this).is(':checked')) {
                status = 1;
            }

            $.ajax({
                url: "{{ route('admin.faq.change-status') }}",
                type: "POST",
                data: {
                    id: $(this).data('id'),
                    status: status
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status) {
                        toastr["success"](response.message);
                    } else {
                        toastr["error"](response.message);
                    }
                }, error: function (response) {
                    toastr["error"]('Something went wrong');
                }
            })
        })

        function deleteFaq(id) {
            let url = '{{ route('admin.faq.delete', ['id' => ':id']) }}';
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    if (response.status) {
                        toastr["success"](response.message);
                        setTimeout(function () {
                            window.location.reload()
                        }, 2000);
                    } else {
                        toastr["error"](response.message);
                    }
                }
            })
        }
    </script>
@endsection