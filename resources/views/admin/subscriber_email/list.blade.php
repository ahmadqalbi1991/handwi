@extends('admin.template.layout')

@section('header')
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Total: <b class="primary-color">{{$subscriberemails->count()}}</b> Subscriber Emails</h3>
            <a href="{{ url('admin/subscriberemailsexport') }}" class="btn btn-primary">Export</a> 
        </div>
        
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
                            <a href="{{url('admin/subscriberemails')}}"  class="btn btn-primary">Clear</a>
                            <input type="submit" name="excel" value="Export" class="btn btn-success" >
                           
                        </div>
                    </div>
                    
                </div>
                
               
                
                
                
            </div>
        </form>
      </div>
            <!-- <p>Total: {{$subscriberemails->count()}} Testimonials</p> -->
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-bordered" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            

                            <th>Created Date</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($subscriberemails as $category)
                            <?php $i++; ?>
                            <tr>
                                <td>{{ $i }}</td>
                             
                                <td>{{ $category->email }}</td>
                                  
                                <td>{{web_date_in_timezone($category->created_at,config('global.datetime_format'))}}</td>
                         

                           

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <script>
        $('#example2').DataTable({
            "paging": true,
            "searching": true,
            
            "ordering": true,
            "info": true,
            "autoWidth": true,
            "responsive": true,
        });
    </script>
@stop
