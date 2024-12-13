@extends("admin.template.layout")

@section("header")

@stop


@section("content")
<div class="card">

    <div class="card-body">
      <div class="row">
        <form method="get" action='' class="col-sm-12 col-md-12">
            <div id="column-filter_filter" class="dataTables_filter">
                <div class="row">
                    <div class="col-lg-3">
                        <label class="w-100">From Date:
                            <input type="date" name="from_date" class="form-control form-control-sm" id="min_date"  placeholder="" aria-controls="column-filter" value="{{$from_date}}">
                        </label>
                    </div>
                    <div class="col-lg-3">
                        <label class="w-100">To Date:
                            <input type="date" name="to_date" class="form-control form-control-sm" id="max_date"  placeholder="" aria-controls="column-filter" value="{{$to_date}}">
                        </label>
                    </div>

                    <div class="col-lg-6">
                        <div class=" mt-3">
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
        <table class="table table-condensed table-striped" id="customers_listing">
            <thead>
                <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile No</th>
                <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $list->perPage() * ($list->currentPage() - 1); ?>
                @foreach($list as $datarow)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>

                        <td>{{$datarow->name}} </td>
                        <td>{{$datarow->email}}</td>
                        <td>+{{$datarow->dial_code}} {{$datarow->phone}}</td>
                        
                       
                        <td>{{date( 'd-m-Y h:i A', strtotime($datarow->created_at))}}</td>


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
$('#customers_listing').DataTable({
        "paging": false, 
        "lengthChange": false, 
        "searching": false, 
        "ordering": true, 
        "info": false, 
        "autoWidth": false 
    });    
</script>
@stop
