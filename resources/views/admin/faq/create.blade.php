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
    <button class="btn btn-success btn-sm mt-4" id="cms_save">Save FAQ</button>
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
                <form action="{{ route('admin.faq.save') }}" id="cms-form" >
                    @if(!empty($content))
                        <input type="hidden" name="id" value="{{ $content->id }}">
                    @endif
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="">Title <span class="text-danger">*</span></label>\
                                        <input type="text" value="{{ !empty($content) ? $content->faq_title : '' }}" name="faq_title" id="title" required class="form-control">
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="">
                                        <label for="">Description <span class="text-danger">*</span></label>
                                        <div id="editor">
                                            @if(!empty($content))
                                                @php echo $content->faq_description @endphp
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="">
                                        <label for="status">
                                            <input @if(!empty($content) && $content->status) checked @endif value="1" type="checkbox" name="status" id="status"> Active
                                        </label>
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
        const quill = new Quill('#editor', {
            theme: 'snow'
        });
        $('#cms-form').validate();

        $('#cms_save').on('click', function () {
            if ($('#cms-form').valid()) {
                $('#main-error').addClass('d-none').text('');

                $.ajax({
                    url: '{{ route("admin.faq.save") }}', // Form action URL
                    type: 'POST', // HTTP method
                    data: {
                        faq_title: $('#title').val(),
                        faq_description: quill.root.innerHTML,
                        status: $('#status').val(),
                        faq_id: "{{ !empty($content) ? $content->faq_id : '' }}"
                    }, // The form data to send
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
                            window.location.href = '{{ route("admin.faq.index") }}';
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