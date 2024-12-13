@extends("vendor.template.layout")

@section('header')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
     <style>
        .progress {
            position: relative;
            width: 100%;
        }

        .bar {
            background-color: #00ff00;
            width: 0%;
            height: 20px;
        }

        .percent {
            position: absolute;
            display: inline-block;
            left: 50%;
            color: #040608;
        }

    </style>
@stop


@section('content')
    <div class="card">
         @if($message = Session::get('success'))
   <div class="alert alert-success alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
           <strong>{{ $message }}</strong>
   </div>
   @endif
   @if($message = Session::get('error'))
   <div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
           <strong>{{ $message }}</strong>
   </div>
   @endif
        <div class="card-header">
                   <form action="{{route('vendor.product.export')}}" method="get" class="justify-content-end">
            <div class="status d-flex justify-content-end ">
                <a href="{{ url('vendor/product/create') }}"
                class="btn btn-warning mr-2 btn-rounded">Create Product</a>
                 <a style="display: none;" href="javascript:void(0);" class="btn btn-info mr-2 btn-rounded"  data-toggle="modal" data-target="#exampleModal" > Import</a>
                <input type="hidden" name="from" value="{{$from}}"> 
                 <button type="submit" class="btn btn-danger mr-2 btn-rounded">Export</button> 
            </div>
        </form>
                </div>
                 <div class="modal" tabindex="-1"  id="exampleModal" role="dialog">
            <div class="modal-dialog" role="document">
               
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Import Products & Upload Images</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                
                <div class="modal-body">
                    <form id="fileUploadForm1" method="POST" class="form-inline" enctype="multipart/form-data" action="{{ url('vendor/product/import') }}">
                        {{ csrf_field() }}
                    <label>Import File (.xls, .xslx) </label>
                    <input type="file" name="select_file" class="form-control" />
                    <button type="submit" class="btn btn-primary">Import</button>
                </form>
                <form method="POST" id="fileUploadForm2" class="mt-2 form-inline" enctype="multipart/form-data" action="{{ url('vendor/product/image_upload') }}">
                    {{ csrf_field() }}
                <label>Upload Image (.zip) </label>
                <input type="file" name="zip_file" class="form-control" />
                <button type="submit" class="btn btn-primary">Upload</button>
                
            </form><br>
                                <div class="progress">
                                    <div class="bar"></div>
                                    <div class="percent"></div>
                                </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('vendor.product.download_format')}}"  class="btn btn-success" style="float: left;">Download Format</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  
                </div>
              </div>
           
            </div>
          </div>
        
        <style>
            .form-control {
                height: 38px;
            }
            .btn-secondary:hover, .btn-secondary:focus {
                color: #fff !important;
                background-color: #714cbd;
                box-shadow: none;
                border-color: #714cbd;
            }
        </style>
      
        <div class="card-body">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
                @include('vendor/product/search_form')
                

                    

                    <div class="row mt-3">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="column-filter_length">
                            </div>
                        </div>

                        
                    </div>
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Seller</th>

                                <th>Type</th>
                                <th>Is Active</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($list->total() > 0)
                            <?php $i = 0; ?>
                            @foreach ($list as $item)
                                <?php $i++; ?>
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{$item->product_type ==1 ? 'Simple' :'Variable'}}</td> 
                                    <td>
                                        <label class="switch s-icons s-outline  s-outline-warning  mb-4 mr-2">
                                            <input type="checkbox" class="change_status" data-id="{{$item->id}}"
                                                data-url="{{ url('vendor/products/change_status') }}"
                                                @if ($item->product_status) checked @endif>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>{{ get_date_in_timezone($item->created_on, 'd-M-y H:i A') }}</td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="flaticon-dot-three"></i>
                                            </a>

                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                                <a class="dropdown-item"
                                                    href="{{ url('vendor/products/edit/' . $item->id) }}"><i
                                                        class="flaticon-pencil-1"></i> Edit</a>
                                                <a class="dropdown-item" data-role="unlink"
                                                    data-message="Do you want to remove this product?"
                                                    href="{{ url('vendor/products/delete/' . $item->id) }}"><i
                                                        class="flaticon-delete-1"></i> Delete</a>

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                       
                        @else
                        <tr><td colspan="7" align="center">No products found</td></tr>
                        @endif
                         </tbody>
                    </table>


                    <div class="col-sm-12 col-md-12 pull-right">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                            {!! $list->links('vendor.template.pagination') !!}
                        </div>
                    </div>

            </div>
        </div>
    </div>
@stop
