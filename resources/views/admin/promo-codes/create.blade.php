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







        {{ asset('') }}        admin-assets/assets/img/laconcierge-bg.jpg');*/
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
                <form method="post" action="{{ route('admin.promo-codes.save') }}" id="promo-code-form"
                      enctype="multipart/form-data">
                    @if(!empty($promo_code))
                        <input type="hidden" name="id" value="{{$promo_code->id}}">
                    @endif
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="promo_code">Promo code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="promo_code"
                                               value="{{ !empty($promo_code) ? $promo_code->promo_code : '' }}"
                                               @if(!empty($promo_code)) disabled @endif required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="title">Promo title <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ !empty($promo_code) ? $promo_code->title : '' }}"
                                               class="form-control" name="title" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="form-group">
                                                <label for="value">Value <span class="text-danger">*</span></label>
                                                <input type="number"
                                                       value="{{ !empty($promo_code) ? $promo_code->value : '' }}"
                                                       min="1" name="value" id="value" class="form-control"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label for="value">Type <span class="text-danger">*</span></label>
                                                <select name="type" id="type" class="form-control">
                                                    <option @if(!empty($promo_code) && $promo_code->type === 'percentage') selected
                                                            @endif value="percentage">Percentage
                                                    </option>
                                                    <option @if(!empty($promo_code) && $promo_code->type === 'fixed') selected
                                                            @endif value="fixed">Fixed
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="form-group">
                                                @php
                                                $campaign_ids = [];
                                                if (!empty($promo_code)) {
                                                    $campaign_ids = $promo_code->campaigns->pluck('campaign_id')->toArray();
                                                }
                                                @endphp
                                                <label for="value">Campaigns <span class="text-danger">*</span></label>
                                                <select id="campaigns_select" name="campaigns_id[]" multiple
                                                        class="form-control select2" required>
                                                    <option value="">Select Campaign</option>
                                                    @foreach($campaigns as $campaign)
                                                        <option @if(in_array($campaign->campaigns_id, $campaign_ids)) selected @endif value="{{ $campaign->campaigns_id }}">{{ $campaign->campaigns_title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-4 mt-4">
                                            <div class="form-group">
                                                <label for="all_campaigns">
                                                    <input type="checkbox" name="all_campaigns"
                                                           @if(!empty($promo_code) && $promo_code->all_campaigns) checked
                                                           @endif value="1" id="all_campaigns">
                                                    All
                                                    campaigns
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                        <input type="text"  value="{{ !empty($promo_code) ? $promo_code->start_date : '' }}" class="form-control datepicker" name="start_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="end_date">End Date <span class="text-danger">*</span></label>
                                        <input type="text"  value="{{ !empty($promo_code) ? $promo_code->end_date : '' }}" class="form-control datepicker" name="end_date" required>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="description">Description <span class="text-danger">*</span></label>
                                        <textarea name="description" id="description" class="form-control" required
                                                  rows="5">{{ !empty($promo_code->description) ? $promo_code->description : '' }}</textarea>
                                    </div>
                                </div>
                                <div class="col-12 text-right">
                                    <button type="button" class="btn btn-success" id="save-promo">Save</button>
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
        $('#all_campaigns').change(function () {
            if ($(this).is(':checked')) {
                // Disable the campaigns dropdown if 'All campaigns' is checked
                $('#campaigns_select').prop('disabled', true).val(null).trigger('change');
            } else {
                // Enable the campaigns dropdown if 'All campaigns' is unchecked
                $('#campaigns_select').prop('disabled', false);
            }
        });

        $('#promo-code-form').validate();

        $('#save-promo').on('click', function () {
            if ($('#promo-code-form').valid()) {
                var formData = new FormData($('#promo-code-form')[0]);

                $.ajax({
                    url: '{{ route("admin.promo-codes.save") }}', // Form action URL
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
                            window.location.href = '{{ route("admin.promo-codes.index") }}';
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
    </script>
@endsection