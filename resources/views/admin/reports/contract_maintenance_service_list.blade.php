@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <!-- <a href="{{ url('admin/contract_maintenance/create') }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Service Category</a> -->
          
        </div>
        <div class="card-body">
            <div class="row">
                <form method="get" action='' class="col-sm-12 col-md-12">
                    <div id="column-filter_filter" class="dataTables_filter">
                        <div class="row">
                            <div class="col-lg-3">
                                <label class="w-100">From Date:
                                    <input type="date" name="from_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$from_date}}">
                                </label>
                            </div>
                            <div class="col-lg-3">
                                <label class="w-100">To Date:
                                    <input type="date" name="to_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$to_date}}">
                                </label>
                            </div>
        
                            <div class="col-lg-6">
                                <div class=" mt-3">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                               
                                    <a href="{{url('admin/report/maintenance_service')}}"  class="btn btn-primary">Clear</a>
                                    <input type="submit" name="excel" value="Export" class="btn btn-success" >
                                </div>
                            </div>
                            
                        </div>
                        {{-- <label>From Date:
                            <input type="date" name="from_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$from_date}}">
                        </label>
                        <label>To Date:
                            <input type="date" name="to_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$to_date}}">
                        </label>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <input type="submit" name="excel" value="Export" class="btn btn-primary" >
                        <a href="{{url('admin/report/maintenance_service')}}"  class="btn btn-primary">Clear</a> --}}
                    </div>
                </form>
             </div>
            <div class="table-responsive">
                <table class="table table-condensed table-striped" id="example2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Building Type</th>
                            <th>Contract Type</th>
                            <th>Client Name</th>
                            <th>File</th>
                            <th>Created Date</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($contract_maintainance_job as $job)
                            <?php $i++; ?>
                            <tr>   
                                <td>{{ $i }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="ml-2">
                                            <a href="#" class="yellow-text">{{ $job->description }}</a>
                                        </span>
                                    </div>
                                </td>
                               <td>
                                <p> {{ $job->building_list->name ?? '' }} </p>
                               </td>
                                <td>
                                    <p>{{ $job->contract_text ?? '-' }} </p>
                                </td>
                                 <td>
                                    <p> {{ $job->user->name ?? '' }} </p>
                                </td>
                                 <td>
                                    <p>  <a href="{{ $job->file ?? '' }}" download > Download File </a> </p>
                                </td>
                                <td>{{ get_date_in_timezone($job->created_at, config('global.datetime_format')) }}</td>
                                

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.js"></script>
    <script>
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            "responsive": true,
        });
    </script>
@stop
