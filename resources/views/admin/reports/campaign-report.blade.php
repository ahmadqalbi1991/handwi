@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="row">
        <div class="col-12 text-right mb-3">
            <a href="{{ route('admin.campaigns.export') }}" class="btn btn-success">Export Excel</a>
        </div>
    </div>
    <div class="card mb-5">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-striped ma7zouz-tables" id="example2">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Campaign Image</th>
                        <th>Campaign Name</th>
                        <th>Campaign Date</th>
                        <th>Campaign Time</th>
                        <th>Product Name</th>
                        <th>Winner</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img src="{{ $report->campaigns_image }}" width="75" alt="">
                            </td>
                            <td>{{ $report->campaigns_title }}</td>
                            <td>{{ $report->campaigns_date_start }}</td>
                            <td>{{ $report->campaigns_time_start }}</td>
                            <td>{{ $report->product_name }}</td>
                            <td>{{ $report->draw_slip_number }}</td>
                            <td>{{ $report->campaigns_status != 2 ? 'Pending' : 'Closed' }}</td>
                            <td>
                                @if($report->campaigns_status != 2)
                                    <a href="{{ route('admin.reports.download-campaign-tickets', ['campaign_id' => $report->campaigns_id]) }}"
                                       class="btn btn-sm btn-default mt-1">Print Tickets</a>
                                    <a href="javascript:void(0)" onclick="generateWinner('{{ $report->campaigns_id }}')"
                                       class="btn btn-sm btn-primary mt-1">Generate</a>
                                    <a href="{{ route('admin.reports.generate-winner', ['campaign_id' => $report->campaigns_id]) }}" class="btn btn-sm btn-warning mt-1">Generate Manually</a>
                                @else
                                    <button class="btn btn-success btn-rounded"
                                       onclick="showWinner('{{ $report->campaigns_id }}')">View
                                        Winner</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="generateWinnerPopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Select Date and Time</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.campaigns.generate-winner') }}" method="post"
                      id="generate-winner-auto-form">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="campaign_id" value="" id="campaign_id">
                        <input type="text" name="campaign_date" id="campaign_date" class="datetimepicker form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="submit-form-confirm" onclick="confirmPopup()" class="btn btn-success">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirm-popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center">
                        <span style="font-size: 36px;" class="text-warning"><i class="fa fa-warning"></i></span>
                        <h4>Are you sure?</h4>
                        <div class="mt-4">
                            <button class="btn btn-danger" id="cancel-popup" onclick="cancelAllActions()">Cancel
                            </button>
                            <button class="btn btn-success" id="submit-popup" onclick="submitPopup()">Yes</button>
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

    <div class="modal fade" id="winner-popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Winner Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <table class="table table-bordered" id="winner-body">

                        </table>
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
