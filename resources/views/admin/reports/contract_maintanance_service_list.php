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
                <form method="get" action='' class="col-sm-12 col-md-6">
                    <div id="column-filter_filter" class="dataTables_filter">
                        <label>From Date:
                            <input type="date" name="from_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$from_date}}">
                        </label>
                        <label>To Date:
                            <input type="date" name="to_date" class="form-control form-control-sm" placeholder="" aria-controls="column-filter" value="{{$to_date}}">
                        </label>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <input type="submit" name="excel" value="Export" class="btn btn-primary" >
                        <a href="{{url('admin/report/customers')}}"  class="btn btn-primary">Clear</a>
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
                            <th>Action</th>
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
                                    <p>{{ $job->contract_text ?? '' }} </p>
                                </td>
                                 <td>
                                    <p> {{ $job->user->name ?? '' }} </p>
                                </td>
                                 <td>
                                    <p>  <a href="{{ $job->file ?? '' }}" download > Download File </a> </p>
                                </td>
                                <td>{{ get_date_in_timezone($job->created_at, config('global.datetime_format')) }}</td>
                                <td class="text-center">
                                    <div class="dropdown custom-dropdown">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="flaticon-dot-three"></i>
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                            <a class="dropdown-item"
                                                href="{{ url('admin/contract_maintenance/edit/' . $job->id.'/'.$job->name) }}"><i
                                                    class="flaticon-pencil-1"></i> Edit</a>
                                            <!-- <a class="dropdown-item" data-role="unlink"
                                                data-message="Do you want to remove this category?"
                                                href="{{ url('admin/contract_maintenance/delete/' . $job->id) }}"><i
                                                    class="flaticon-delete-1"></i> Delete</a> -->
                                        </div>
                                    </div>
                                </td>

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
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
        });
    </script>
@stop
