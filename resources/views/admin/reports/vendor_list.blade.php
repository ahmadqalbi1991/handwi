@extends("admin.template.layout")

@section("header")

@stop


@section("content")
<div class="card mb-5">

    <div class="card-body">
      <div class="row">
        <form method="get" action='' class="col-sm-12 col-md-12">
            <div id="column-filter_filter" class="dataTables_filter" style="margin-bottom: 0 !important">
                <div class="row align-items-end">
                    <div class="col-xl-3 col-sm-6 mb-3">
                        <label class="w-100">From Date:
                            <input type="date" name="from_date" class="form-control form-control-sm " id="min_date"  placeholder="" aria-controls="column-filter" value="{{$from_date}}">
                        </label>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-3">
                        <label class="w-100">To Date:
                            <input type="date" name="to_date" class="form-control form-control-sm " id="max_date"  placeholder="" aria-controls="column-filter" value="{{$to_date}}">
                        </label>
                    </div>
                    <div class="col-xl-6 col-sm-12 mb-3">
                        <div class=" mb-2">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a href="{{url('admin/report/customers')}}"  class="btn btn-primary">Clear</a>
                            <input type="submit" name="excel" value="Export" class="btn btn-success" >
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="vendors_report_listing">
            <thead>
                <tr>
                <th>#</th>
                <th>Vendor Details</th>
                <th>Address</th>
                <th>Country/City</th>
                <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($list as $datarow)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>

                        <td>
                            <div>
                                <a href="#" class="yellow-color">
                                    {{$datarow->name}}
                                </a>
                            </div>
                            <div class="">
                                {{$datarow->vendordata->company_name??''}}
                            </div>
                            <span class="">
                                {{$datarow->email}}
                            </span> <br>
                            <span class="">
                                +{{$datarow->dial_code}} {{$datarow->phone}}
                            </span>
                            </div>
                        </td>

                        <!-- <td>{{$datarow->name}}  </td>
                        <td>{{$datarow->vendordata->company_name??''}}</td>
                        <td>{{$datarow->email}}</td>
                        <td>+{{$datarow->dial_code}} {{$datarow->phone}}</td> -->
                        <td>
                          @if(!empty($datarow->vendordata))
                            {{$datarow->vendordata->address1}}<br>
                            {{$datarow->vendordata->address2}}<br>
                            {{$datarow->vendordata->street}}
                          @endif
                        </td>
                        <td>
                          @if(!empty($datarow->country))
                            {{$datarow->country->name??''}},{{$datarow->city->name??''}}
                          @endif
                        </td>
                        <td>{{$datarow->created_at}}</td>


                    </tr>
                @endforeach
            </tbody>
        </table>
        <span>Total {{ $list->total() }} entries</span>

            <div class="col-sm-12 col-md-12 pull-right">
                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                {{-- {!! $list->links('admin.template.pagination') !!} --}}
                {!! $list->appends(request()->input())->links('admin.template.pagination') !!}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section("script")
 <script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
<script>
    document.getElementById("min_date").onchange = function () {
        $('#max_date').val(' ');
        var input = document.getElementById("max_date");
        input.setAttribute("min", this.value);
    }
$('#vendors_report_listing').DataTable({
        "paging": false, 
        "lengthChange": false, 
        "searching": false, 
        "ordering": true, 
        "info": false, 
        "autoWidth": false 
    });    
</script>
@stop
