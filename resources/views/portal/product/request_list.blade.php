@extends("vendor.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
    <div class="card">
        <div class="card-body">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
                <form action="" method="get">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Date From</label>
                            <input type="text" name="from" class="form-control datepicker" autocomplete="off" value="{{$from}}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Date To</label>
                            <input type="text" name="to" class="form-control datepicker" autocomplete="off" value="{{$to}}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Category</label>
                    <select class="form-control jqv-input product_cat select2" data-jqv-required="true"
                        name="category" data-role="select2" data-placeholder="Select Categories"
                        data-allow-clear="true">
                        <option value="">All</option>
                        <?php if(isset($category_list) && count($category_list) > 0): ?>

                        <?php 
                        $id = '';
                        foreach($category_list as $parent_cat_id => $parent_cat_name): ?>
                       
                        <?php if ( isset($sub_category_list[$parent_cat_id]) && !empty($sub_category_list[$parent_cat_id]) ) { ?>
                        <optgroup label="<?php echo $parent_cat_name; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                            <?php foreach ($sub_category_list[$parent_cat_id] as $sub_cat_id => $sub_cat_name): ?>
                            <?php if ($id > 0 && $id == $sub_cat_id) {
                                continue;
                            } ?>
                            <?php if ( isset($sub_category_list[$sub_cat_id]) && !empty($sub_category_list[$sub_cat_id]) ){ ?>
                        <optgroup label="<?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                            <?php foreach ($sub_category_list[$sub_cat_id] as $sub_cat_id2 => $sub_cat_name2): ?>
                            <?php if ($id > 0 && $id == $sub_cat_id2) {
                                continue;
                            } ?>
                            <?php if ( isset($sub_category_list[$sub_cat_id2]) && !empty($sub_category_list[$sub_cat_id2]) ){ ?>
                        <optgroup label="<?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                            <?php foreach ($sub_category_list[$sub_cat_id2] as $sub_cat_id3 => $sub_cat_name3): ?>
                            <?php if ($id > 0 && $id == $sub_cat_id3) {
                                continue;
                            } ?>
                            <?php if ( isset($sub_category_list[$sub_cat_id3]) && !empty($sub_category_list[$sub_cat_id3]) ){ ?>
                            <?php foreach ($sub_category_list[$sub_cat_id3] as $sub_cat_id4 => $sub_cat_name4): ?>
                            <?php if ($id > 0 && $id == $sub_cat_id4) {
                                continue;
                            } ?>
                            <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id4; ?>"
                                <?php echo in_array($sub_cat_id4, $category_ids) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('&nbsp;', 10) . $sub_cat_name4; ?>
                            </option>
                            <?php endforeach; ?>
                            <?php }else{ ?>
                            <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id3; ?>"
                                <?php echo in_array($sub_cat_id3, $category_ids) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('&nbsp;', 8) . $sub_cat_name3; ?>
                            </option>
                            <?php } ?>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php }else{ ?>
                        <option value="<?php echo $sub_cat_id2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                            <?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>
                        </option>
                        <?php } ?>
                        <?php endforeach; ?>
                        </optgroup>
                        <?php }else{ ?>
                        <option value="<?php echo $sub_cat_id; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                            <?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>
                        </option>
                        <?php } ?>
                        <?php endforeach; ?>
                        </optgroup>
                        <?php }else{ ?>
                        <option value="<?php echo $parent_cat_id; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                            <?php echo $parent_cat_name; ?>
                        </option>
                        <?php } ?>


                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                        </div>
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary float-right">Filter</button>
                            @if($from || $to || $category)
                            <a type="button" href="{{url('admin/products_requests')}}" class="btn btn-primary float-right mr-2">Reset</a>
                            @else
                            <button type="reset" class="btn btn-primary float-right mr-2">Reset</button>
                            @endif
                        </div>
                    </div>
                </form>
                @if ($list->total() > 0)

                    

                    <div class="row mt-3">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="column-filter_length">
                            </div>
                        </div>

                        <form method="get" action='' class="col-sm-12 col-md-6">
                            <div id="column-filter_filter" class="dataTables_filter">
                                <label>Search:
                                    <input type="search" name="search_key" class="form-control form-control-sm"
                                        placeholder="" aria-controls="column-filter" value="{{ $search_key }}">
                                </label>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Seller</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @foreach ($list as $item)
                                <?php $i++; ?>
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->business_name }}</td>
                                    
                                    <td>{{ get_date_in_timezone($item->created_on, 'd-M-y H:i A') }}</td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="flaticon-dot-three"></i>
                                            </a>

                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                                <a class="dropdown-item"
                                                    href="{{ url('admin/products/add_to_product/' . $item->id) }}"><i
                                                        class="flaticon-plus-1"></i> Add to product</a>
                                                
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>


                    <div class="col-sm-12 col-md-12 pull-right">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                            {!! $list->links('admin.template.pagination') !!}
                        </div>
                    </div>

                @else
                    <br>
                    <div class="alert alert-warning">
                        <p>No request found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.js"></script>
    <script>
        
    </script>
@stop