@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
          href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('buttons')
    <button class="btn btn-success btn-sm mt-4" id="save-banner">Save Banner</button>
@endsection

@section("content")
    <div class="card mb-5">
        <div class="card-body">
            <form action="" enctype="multipart/form-data" id="add-banner-form" method="post">
                @if(!empty($banner))
                    <input type="hidden" name="id" value="{{ $banner->id }}">
                @endif
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="bi_name">Banner Title <span class="text-danger">*</span></label>
                            <input type="text" value="{{ !empty($banner) ? $banner->bi_name : '' }}"
                                   class="form-control" name="bi_name" id="bi_name" required>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="bi_status">Banner Status <span class="text-danger">*</span></label>
                            <select name="bi_status" id="bi_status" class="form-control" required>
                                <option value="">Select Option</option>
                                <option @if(!empty($banner) && $banner->bi_status == 1) selected @endif value="1">
                                    Active
                                </option>
                                <option @if(!empty($banner) && $banner->bi_status == 0) selected @endif value="0">
                                    Inactive
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="bi_product_id">Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="bi_product_id" class="form-control" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option @if(!empty($banner) && $banner->product_id == $product->product_id) selected
                                            @endif value="{{ $product->product_id }}">{{ $product->product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="bi_image">Banner Image <span class="text-danger">*</span></label>
                                    <input type="file" name="bi_image" id="bi_image" class="form-control" @if(empty($banner)) required @endif>
                                </div>
                            </div>
                            <div class="col-2">
                                <img src="{{ !empty($banner) ? $banner->bi_image : asset('images/dummy.jpg') }}"
                                     alt="" width="100" height="100" id="bi_image_preview">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.js"></script>
    <script>
        $('#bi_image').on('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#bi_image_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $('#add-banner-form').validate();

        $('#save-banner').on('click', function () {
            if ($('#add-banner-form').valid()) {
                var formData = new FormData($('#add-banner-form')[0]);
                $('#main-error').addClass('d-none').text('');

                $.ajax({
                    url: '{{ route("admin.app-banners.save") }}', // Form action URL
                    type: 'POST', // HTTP method
                    data: formData, // The form data to send
                    contentType: false, // Needed to handle the file upload
                    processData: false, // Don't process the files
                    cache: false, // Disable cache
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Add CSRF token
                    },
                    beforeSend: function () {
                        // Show loader or disable button before sending request
                        $('#launch_campaign').prop('disabled', true);
                    },
                    success: function (response) {
                        // Handle success
                        $('span.text-danger').remove();
                        $('#launch_campaign').prop('disabled', false);
                        if (response.status === '1') {
                            toastr["success"](response.message);
                            window.location.href = '{{ route("admin.app-banners.index") }}';
                        } else if (response.status === '2') {
                            var errors = response.validationErrors;
                            $('#main-error').removeClass('d-none').text('Please check all mandatory fields.')
                            $.each(errors, function (field, messages) {
                                var fieldElement = $('[name="' + field + '"]');
                                fieldElement.after('<span class="text-danger">' + messages[0] + '</span>');
                            });
                        } else {
                            toastr["error"](response.message);
                        }
                    },
                    error: function (xhr) {
                        // Handle error
                        $('#launch_campaign').prop('disabled', false);
                        alert('An error occurred while saving the banner.');
                    },
                    complete: function () {
                        // Enable button after request is completed
                        $('#submit-button').prop('disabled', false);
                    }
                });
            } else {
                $('#main-error').removeClass('d-none').text('Please check all mandatory fields.');
            }
        });
    </script>
@endsection