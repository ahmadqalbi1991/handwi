@extends("admin.template.layout")

@section('header')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
     <style>
    .select2-container .select2-selection--multiple{
        min-height: 44px;
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
                  
            <div class="status d-flex justify-content-end ">
                <a href="{{ url('admin/product/create') }}"
                class="btn-custom btn mr-2 mt-2 mb-2" data-toggle="modal" data-target="#exampleModal"><i class="fa-solid fa-plus"></i> Add to Featured</a>
               
                
            </div>
       
                </div>
                 <div class="modal" tabindex="-1"  id="exampleModal" role="dialog">
            <div class="modal-dialog" role="document">
               
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Add Products to Featured</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                
                <div class="modal-body">
                    <form id="admin-form" method="POST" class="form-inline" enctype="multipart/form-data" action="{{ url('admin/save_featured_products') }}">
                        {{ csrf_field() }}
                  <div class="col-md-12 form-group applies_to_select" id="browse_product">
                                                <label class="control-label">Browse Product</label>
                                                <select class="form-control product_search select2" id="product_search"  name="txt_products[]" style="width:100%" required>
                                                 @foreach($products as $prod)
                                                 <option value="<?=$prod->id?>"><?=$prod->product_name?></option>
                                                 @endforeach
                                                </select>
                                                <div class="error"></div>
                                            </div>
                
            </form>
                              
                </div>
                <div class="modal-footer">
                    <button type="submit" form="admin-form" class="btn btn-primary">Add</button>
                    
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
                                    <th width="15%">Image</th>
                                    <th width="15%">Action</th>
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
                                        <td></td>
                                        <td class="text-center">
                                            <div class="dropdown custom-dropdown">
                                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                    <i class="flaticon-dot-three"></i>
                                                </a>

                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                                  
                                                    <a class="dropdown-item" data-role="unlink"
                                                        data-message="Do you want to remove this product from featured?"
                                                        href="{{ url('admin/products/deletefeatured/' . $item->id) }}"><i
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

        });
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            $('.invalid-feedback').remove();
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);

            App.loading(true);
            $form.find('button[type="submit"]')
                .text('Saving')
                .attr('disabled', true);

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $form.attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                dataType: 'json',
                timeout: 600000,
                success: function(res) {
                    App.loading(false);

                    if (res['status'] == 0) {
                        if (typeof res['errors'] !== 'undefined') {
                            var error_def = $.Deferred();
                            var error_index = 0;
                            jQuery.each(res['errors'], function(e_field, e_message) {
                                if (e_message != '') {
                                    $('[name="' + e_field + '"]').eq(0).addClass('is-invalid');
                                    $('<div class="invalid-feedback">' + e_message + '</div>')
                                        .insertAfter($('[name="' + e_field + '"]').eq(0));
                                    if (error_index == 0) {
                                        error_def.resolve();
                                    }
                                    error_index++;
                                }
                            });
                            error_def.done(function() {
                                var error = $form.find('.is-invalid').eq(0);
                                $('html, body').animate({
                                    scrollTop: (error.offset().top - 100),
                                }, 500);
                            });
                        } else {
                            var m = res['message'];
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message']);
                        setTimeout(function() {
                            window.location.href = App.siteUrl('/admin/featured_products');
                        }, 1500);
                    }

                    $form.find('button[type="submit"]')
                        .text('Save')
                        .attr('disabled', false);
                },
                error: function(e) {
                    App.loading(false);
                    $form.find('button[type="submit"]')
                        .text('Save')
                        .attr('disabled', false);
                    App.alert(e.responseText, 'Oops!');
                }
            });
        });
        $(document).delegate("#applies_to", "change", function() {
        $(".applies_to_select").css("display","none");
        var show = $('option:selected', this).attr('data-show');
        $(show).css("display","block");     
        });
        $(".basicDate").flatpickr({
     minDate: "today"
});
    </script>
@stop