@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
    <style>
        /* Basic styling for suggestion dropdown */
        #suggestions {
            border: 1px solid #ccc;
            display: none;
            max-height: 200px;
            overflow-y: auto;
            position: absolute;
            background: #fff;
            width: calc(100% - 22px);
            z-index: 1000;
        }

        .suggestion-item {
            padding: 10px;
            cursor: pointer;
            color: #000;
        }

        .suggestion-item:hover {
            background-color: #f0f0f0;
        }
    </style>
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <form action="" id="ticket-form">
                        <div class="form-group">
                            <label for="ticket_number">Enter Ticket Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ticket_number" required>
                            <input type="hidden" name="campaign_id" id="campaign_id" value="{{ $id }}">
                            <div id="suggestions">
                                @foreach($draw_slips as $slip)
                                    <div class="suggestion-item">{{ $slip->draw_slip_number }}</div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 text-right">
                            <button type="button" disabled class="btn btn-success btn-rounded" id="search_user">Submit</button>
                        </div>
                    </form>
                </div>
                <div class="col-12 mt-4" id="user-info" style="display: none">
                    <div class="row">
                        <div class="col-4">
                            <label for="">Name</label>
                            <p id="user-name">User bname</p>
                        </div>
                        <div class="col-4">
                            <label for="">Email</label>
                            <p id="user-email">User bname</p>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="">Date & Time</label>
                                <input type="text" name="campaign_date" id="campaign_date" class="form-control datetimepicker">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-right">
                            <button id="generate-winner" type="button" class="btn btn-success btn-rounded">Generate Report</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="winner-popup-success" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center">
                        <span style="font-size: 36px;" class="text-warning">
                            <img src="{{ asset('images/money.png') }}" alt="">
                        </span>
                        <h4 id="winner"></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script>

    </script>
@endsection
