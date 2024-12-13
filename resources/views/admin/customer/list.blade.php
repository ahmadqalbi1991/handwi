@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")

<style>
    #example2_info{
        display: none
    }
    /* .switch input {
        display: block;
        margin: 0 auto;
    } */
</style>
<div class="row">
    <div class="col-12 text-right mb-3">
        <a href="{{ route('admin.customers.export') }}" class="btn btn-success">Export Excel</a>
    </div>
</div>
<div class="card mb-5">
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped ma7zouz-tables" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Name</th>
                <th style="text-align: center;width:100px;">Image</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($customers as $customer)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>
                            {{ $customer->user_first_name }} {{ $customer->user_last_name }}
                        </td>
                        <td style="width:100px; text-align: center;">
                            <img width="50" src="{{ $customer->image }}" alt="">
                        </td>
                        <td>{{$customer->user_email_id}}</td>
                        <td>+{{str_replace('+', '', $customer->dial_code)}} {{$customer->phone_number}}</td>
                        
                        <td>
                            <label class="switch s-icons s-outline  s-outline-warning mb-2 mt-2 mr-2">
                                        <input type="checkbox" class="change_status_customer" data-id="{{ $customer->user_id }}"
                                            data-url="{{ url('admin/customers/change_status') }}"
                                            @if ($customer->user_status) checked @endif>
                                        <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> Recharge Wallet </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="recharge_wallet" id="recharge_wallet" method="post" action="{{ url('admin/customers/add_wallet_balance') }}">
                <div class="modal-body">

                    <input class="form-control" type="number" required name="wallet_balance" id="wallet_balance" placeholder="0.00">
                    <input type="hidden" id="customer_id" name="customer_id">
                    @csrf
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    {{--<button type="button" class="btn btn-primary" onclick="add_wallet_balance()">Add</button>--}}
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section("script")
    <script>
        $('.change_status_customer').on('change', function () {
            let status = 0;
            if ($(this).is(':checked')) {
                status = 1;
            }

            $.ajax({
                url: "{{ route('admin.customers.change-status') }}",
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
    </script>
@stop