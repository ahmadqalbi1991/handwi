@extends('admin.template.layout')

@section('header')
    <link href="{{ asset('') }}admin-assets/assets/css/support-chat.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/maps/vector/jvector/jquery-jvectormap-2.0.3.css" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/charts/chartist/chartist.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}admin-assets/assets/css/default-dashboard/style.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css"
          href="{{ asset('admin-assets/plugins/jqvalidation/custom-jqBootstrapValidation.css') }}">
@stop

@section('buttons')
    <button class="btn btn-success btn-sm mt-4" id="launch_campaign">Launch Campaign</button>
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-primary mt-4">Back</a>
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




        {{ asset('') }}     admin-assets/assets/img/laconcierge-bg.jpg');*/
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
        <div class="row">
            <div class="col-12">
                <form action="{{ route('admin.campaigns.save') }}" id="campaign-form" enctype="multipart/form-data">
                    @if(!empty($product_id))
                        <input type="hidden" name="product_id" value="{{ $product_id }}">
                    @endif
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#campaign_details"
                                       role="tab" aria-controls="campaign_details" aria-selected="true">Campaign
                                        Details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#product_details"
                                       role="tab" aria-controls="product_details" aria-selected="false">Product
                                        Details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#attributes" role="tab"
                                       aria-controls="attributes" aria-selected="false">Attributes</a>
                                </li>
                            </ul>
                            <div class="tab-content mt-4" id="myTabContent">
                                <div class="tab-pane fade show active" id="campaign_details" role="tabpanel"
                                     aria-labelledby="home-tab">
                                    <div class="tab-content">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label for="country_id">Country</label>
                                                    <select name="country_id" id="country_id" class="form-control"
                                                            required>
                                                        <option value="">Select Country</option>
                                                        @foreach($countries as $country)
                                                            <option @if(!empty($product) && $product->country_id == $country->countries_id) selected
                                                                    @endif value="{{ $country->countries_id }}">{{ $country->countries_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label for="campaigns_title">Campaign Title <span
                                                                class="text-danger">*</span></label>
                                                    <input value="{{ !empty($product) ? $product->campaigns_title : '' }}"
                                                           type="text" name="campaigns_title" id="campaigns_title"
                                                           required class="form-control" placeholder="Enter title">
                                                </div>
                                            </div>
                                            {{--                                            <div class="col-md-6 col-sm-12">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label for="campaigns_title_arabic">Campaign Title (AR) <span class="text-danger">*</span></label>--}}
                                            {{--                                                    <input type="text" name="campaigns_title_arabic" id="campaigns_title_arabic" required class="form-control text-right" placeholder="Enter title">--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label for="campaigns_draw_date">Draw Date<span class="text-danger">*</span></label>
                                                    <input type="text"
                                                           value="{{ !empty($product) ? \Carbon\Carbon::parse($product->campaigns_draw_date)->format('Y-m-d') : '' }}"
                                                           name="campaigns_draw_date" id="campaigns_draw_date" required
                                                           class="form-control datepicker">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="row">
                                                    <div class="col-4 mt-4">
                                                        <div class="form-group">
                                                            <label for="schedule_now">
                                                                <input type="checkbox"
                                                                       {{ !empty($product) && $product->schedule_now ? 'checked' : '' }} value="1"
                                                                       name="schedule_now" id="schedule_now"
                                                                       class="custom-checkbox">
                                                                &nbsp;Schedule Now
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-8" id="scheduleDateDiv" @if(!empty($product) && $product->schedule_now) style="display: none" @endif>
                                                        <div class="form-group">
                                                            <label for="campaigns_schedule_date">Schedule Date & Time <span
                                                                        class="text-danger">*</span></label>
                                                            <input type="text"
                                                                   value="{{ !empty($product) ? \Carbon\Carbon::parse($product->campaigns_date_start . ' ' . $product->campaigns_time_start)->format('Y-m-d h:i A') : '' }}"
                                                                   name="campaigns_schedule_date" id="campaigns_schedule_date"
                                                                   required class="form-control datetimepicker">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="row mt-5">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="is_featured">
                                                                <input type="checkbox"
                                                                       {{ !empty($product) && $product->is_featured ? 'checked' : '' }} value="1"
                                                                       name="is_featured" id="is_featured">
                                                                &nbsp;Featured Campaign
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="campaigns_desc">Campaign Description <span
                                                                class="text-danger">*</span></label>
                                                    <textarea name="campaigns_desc" id="campaigns_desc" rows="5"
                                                              class="form-control">{{ !empty($product) ? $product->campaigns_desc : '' }}</textarea>
                                                </div>
                                            </div>
                                            {{--                                            <div class="col-md-6 col-sm-12">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label for="campaigns_desc_arabic">Campaign Description (AR) <span class="text-danger">*</span></label>--}}
                                            {{--                                                    <textarea name="campaigns_desc_arabic" id="campaigns_desc_arabic" rows="5"--}}
                                            {{--                                                              class="form-control text-right"></textarea>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}
                                            <div class="col-md-6 col-sm-12" id="campaign_images">
                                                <div class="row image-row">
                                                    <div class="col-8">
                                                        <div class="form-group">
                                                            <label for="campaigns_image">Campaign Image <span
                                                                        class="text-danger">*</span></label>
                                                            <input type="file" name="campaigns_image[]"
                                                                   class="form-control campaigns_image"
                                                                   @if(empty($product)) required @endif>
                                                        </div>
                                                        <a href="javascript:void(0)" class="add_image"><i
                                                                    class="bx bx-plus"></i> Add Image</a>
                                                    </div>
                                                    <div class="col-2">
                                                        <img src="{{ !empty($product) ? $product->campaigns_image : asset('images/dummy.jpg') }}"
                                                             alt="" width="100" height="100"
                                                             class="campaign_image_preview">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="row">
                                                    <div class="col-8">
                                                        <div class="form-group">
                                                            <label for="prize_image">Prize Image <span
                                                                        class="text-danger">*</span></label>
                                                            <input type="file" name="prize_image" id="prize_image"
                                                                   class="form-control"
                                                                   @if(empty($product)) required @endif>
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <img src="{{ !empty($product) ? $product->campaigns_image2 : asset('images/dummy.jpg') }}"
                                                             alt="" width="100" height="100" id="prize_image_preview">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="product_details" role="tabpanel"
                                     aria-labelledby="profile-tab">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <label for="product_name">Product Name <span
                                                        class="text-danger">*</span></label>
                                            <input type="text"
                                                   value="{{ !empty($product) ? $product->product_name : '' }}"
                                                   name="product_name" id="" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="product_unique_iden">Product Unique Identifier <span
                                                        class="text-danger">*</span></label>
                                            <input type="text"
                                                   value="{{ !empty($product) ? $product->product_unique_iden : '' }}"
                                                   name="product_unique_iden" id="product_unique_iden" required
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="product_desc_short">Product description short <span
                                                        class="text-danger">*</span></label>
                                            <textarea name="product_desc_short" id="product_desc_short" rows="5"
                                                      class="form-control"
                                                      required>{{ !empty($product) ? $product->product_desc_short : '' }}</textarea>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="product_desc_full">Product description full <span
                                                        class="text-danger">*</span></label>
                                            <textarea name="product_desc_full" id="product_desc_full" rows="5"
                                                      class="form-control"
                                                      required>{{ !empty($product) ? $product->product_desc_full : '' }}</textarea>
                                        </div>
                                        {{--                                        <div class="col-md-6 col-sm-12">--}}
                                        {{--                                            <label for="product_type">Product type <span class="text-danger">*</span></label>--}}
                                        {{--                                            <select name="product_type" id="product_type" class="form-control">--}}
                                        {{--                                                <option value="">Select Type</option>--}}
                                        {{--                                                <option value="1">Simple</option>--}}
                                        {{--                                                <option value="2">Variable</option>--}}
                                        {{--                                            </select>--}}
                                        {{--                                        </div>--}}
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="attributes" role="tabpanel"
                                     aria-labelledby="attributes-tab">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="stock_quantity">Stock Quantity <span
                                                            class="text-danger">*</span></label>
                                                <input type="number" min="1" name="stock_quantity"
                                                       value="{{ !empty($product) ? $product->stock_quantity : '' }}"
                                                       id="stock_quantity" required class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="price">Price <span class="text-danger">*</span></label>
                                                <input type="number" name="price" id="price" min="0" step="0.01"
                                                       value="{{ !empty($product) ? $product->sale_price : '' }}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="row">
                                                <div class="col-8">
                                                    <div class="form-group">
                                                        <label for="product_image">Product Image <span
                                                                    class="text-danger">*</span></label>
                                                        <input type="file" name="product_image" id="product_image"
                                                               class="form-control"
                                                               @if(empty($product)) required @endif>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <img src="{{ !empty($product) ? $product->product_image : asset('images/dummy.jpg') }}"
                                                         alt="" width="100" height="100" id="product_image_preview">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.js"></script>
    <script>
        $('#campaigns_image').on('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#campaign_image_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $('#prize_image').on('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#prize_image_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        })

        $('#product_image').on('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#product_image_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $('#campaign-form').validate();

        $('#launch_campaign').on('click', function () {
            if ($('#campaign-form').valid()) {
                var formData = new FormData($('#campaign-form')[0]);
                $('#main-error').addClass('d-none').text('');

                $.ajax({
                    url: '{{ route("admin.campaigns.save") }}', // Form action URL
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
                            window.location.href = '{{ route("admin.campaigns.index") }}';
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
                        alert('An error occurred while saving the campaign.');
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

        $(document).ready(function () {
            const checkbox = $('#schedule_now');
            const scheduleDateDiv = $('#scheduleDateDiv');

            function toggleScheduleDateVisibility() {
                if (checkbox.is(':checked')) {
                    scheduleDateDiv.hide();
                } else {
                    scheduleDateDiv.show();
                }
            }

            toggleScheduleDateVisibility();
            checkbox.change(toggleScheduleDateVisibility);
            // Function to append a new image input field
            $('.add_image').click(function () {
                var newImageInput = `
                        <div class="row image-row mt-2">
                            <div class="col-8">
                                <div class="form-group">
                                    <input type="file" name="campaigns_image[]" class="form-control campaigns_image" required>
                                </div>
                            </div>
                            <div class="col-2">
                                <img src="{{ asset('images/dummy.jpg') }}" alt="" width="100" height="100" class="campaign_image_preview">
                            </div>
                            <div class="col-2">
                                <a href="javascript:void(0)" class="remove_image text-danger" style="font-size:20px;">&times;</a>
                            </div>
                        </div>`;

                // Append new input to the form
                $('#campaign_images').append(newImageInput);
            });

            // Function to preview the uploaded image
            $(document).on('change', '.campaigns_image', function (e) {
                var reader = new FileReader();
                var targetImg = $(this).closest('.image-row').find('.campaign_image_preview');

                reader.onload = function (e) {
                    targetImg.attr('src', e.target.result);
                };

                // Read the file and trigger the preview
                reader.readAsDataURL(this.files[0]);
            });

            // Function to remove the image input field
            $(document).on('click', '.remove_image', function () {
                $(this).closest('.image-row').remove();
            });
        });


    </script>
@endsection