@extends("admin.template.layout")

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
    <div class="card mb-5">
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
                    <form id="fileUploadForm1" method="POST" class="form-inline" enctype="multipart/form-data" action="{{ url('admin/product/import') }}">
                        {{ csrf_field() }}
                    <label>Import File (.xls, .xslx) </label>
                    <input type="file" name="select_file" class="form-control" />
                    <button type="submit" class="btn btn-primary">Import</button>
                </form>
                <form method="POST" id="fileUploadForm2" class="mt-2 form-inline" enctype="multipart/form-data" action="{{ url('admin/product/image_upload') }}">
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
                    <a href="{{ route('admin.product.download_format')}}"  class="btn btn-success" style="float: left;">Download Format</a>
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
            <div class="">
                <div class="dataTables_wrapper container-fluid dt-bootstrap4 table-responsive">
                    @include('admin/product/search_form')
                    

                        

                        <div class="row mt-1">
                            <div class="col-sm-12 col-md-6">
                                <div class="dataTables_length" id="column-filter_length">
                                </div>
                            </div>

                            
                        </div>
                        <table class="table table-condensed table-striped ">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Product Name</th>
                                    <th width="15%">Stock QTY</th>
                                    <th width="15%">Seller</th>
                                    <th width="15%">Type</th>
                                    <th width="15%">Is Active</th>
                                    <th width="15%">Created Date</th>
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
                                        <td>{{ $item->stock_quantity }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{$item->product_type ==1 ? 'Simple' :'Variable'}}</td> 
                                        <td>@if ($item->product_status) Active @else Inactive @endif</td>
                                        <td>{{ get_date_in_timezone($item->created_at, 'd-M-y H:i A') }}</td>
                                    </tr>
                                @endforeach
                        
                            @else
                            <tr><td colspan="7" align="center">No products found</td></tr>
                            @endif
                            </tbody>
                        </table>


                        <div class="col-sm-12 col-md-12 pull-right">
                            <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                                {!! $list->links('admin.template.pagination') !!}
                            </div>
                        </div>

                    
                        
                    
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>
    <script>
           var bar = $('.bar');
        var percent = $('.percent');
        $('#fileUploadForm1').ajaxForm({
            beforeSend: function() {
                var percentVal = '0%';
                bar.width(percentVal)
                percent.html(percentVal);
            },
            uploadProgress: function(event, position, total, percentComplete) {
                var percentValue = percentComplete + '%';
                $(".bar").animate({
                        width: '' + percentValue + ''
                    }, {
                        duration: 5000,
                        easing: "linear",
                        step: function (x) {
                        percentText = Math.round(x * 100 / percentComplete);
                            $(".percent").text(percentText + "%");
                        
                        }
                    });
                var percentVal = percentComplete + '%';
                bar.width(percentVal)
                percent.html(percentVal);
            },
            success: function(response) {
                bar.css('background-color','#00ff00');
                if (response.success == 0) {
                     App.alert([response.message, 'error']);
                    bar.css('background-color','red');
                    bar.width('100%');
                    percent.html('');
                  //  window.location.reload();
                } else {
                     App.alert([response.message, 'success']);
                }
                bar.width('0%');
                percent.html('');
            },
            complete: function(xhr) {
                $('#fileUploadForm1').trigger('reset');
                
            },error: function(xhr, status, error) {
                bar.width('0%')
                percent.html('error occured, try again');
}
        });
        $('#fileUploadForm2').ajaxForm({
            beforeSend: function() {
                $('.percent').html('0%');
                var percentVal = '0%';
                bar.width(percentVal)
                percent.html(percentVal);
            },
            uploadProgress: function(event, position, total, percentComplete) {
                var percentValue = percentComplete + '%';
                $(".bar").animate({
                        width: '' + percentValue + ''
                    }, {
                        duration: 5000,
                        easing: "linear",
                        step: function (x) {
                        percentText = Math.round(x * 100 / percentComplete);
                            $(".percent").text(percentText + "%");
                        
                        }
                    });
                var percentVal = percentComplete + '%';
                bar.width(percentVal)
                percent.html(percentVal);
            },
            success: function(response) {
                bar.css('background-color','#00ff00');
                if (response.success == 0) {
                    App.alert([response.message, 'error']);
                    bar.css('background-color','red');
                    bar.width('100%');
                    percent.html('');
                    
                } else {
                     App.alert([response.message, 'success']);
                   
                    
                }
                bar.width('0%');
                percent.html('');
                  $('#fileUploadForm2').trigger('reset');
                  setTimeout("window.location=''",3000);
            },
            error: function(xhr, status, error) {
                bar.width('0%')
                percent.html('error occured, try again');
}
        });
    </script>
@stop