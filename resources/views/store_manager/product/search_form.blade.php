<form action="" method="get">
    <div class="row">
        <div class="col-md-3 form-group">
            <label>Filter Products</label>
            <?php
            $filters = [
                        '1'=>'Product Title A-Z',
                        '2'=>'Product Title Z-A',
                        '3'=>'Created (Oldest First)',
                        '4'=> 'Created (Newest First)',
                        '5'=>'Updated (Oldest First)',
                        '6'=> 'Updated (Newest First)'
                         ]; 
             ?>
            <select class="form-control jqv-input product_cat" data-jqv-required="true"
            name="sort_type" data-role="select2" data-placeholder="Select Filter"
            data-allow-clear="true">
            <option value="">Filter Products</option>
            @foreach($filters as $key=>$val) 
                @if(isset($_GET['sort_type']) && $_GET['sort_type'] == $key)
                <?php  $selected = "selected"; ?>
                 @else 
                    <?php $selected  = "";  ?>
                @endif
                <option value="{{$key}}" {{$selected }}>{{$val}}</option>
            @endforeach
            
        </select>
        </div>
        <div class="col-md-3 form-group">
            <label>Category</label>
            <select class="form-control jqv-input product_cat select2" data-jqv-required="true"
            name="category" data-role="select2" data-placeholder="Select Categories"
            data-allow-clear="true">
                <option value="">All</option>
                @if(isset($category_list) && count($category_list) > 0)

                <?php 
                $id = ''; ?>
                @foreach($category_list as $parent_cat_id => $parent_cat_name)
               
                @if ( isset($sub_category_list[$parent_cat_id]) && !empty($sub_category_list[$parent_cat_id]) )
                <optgroup label="<?php echo $parent_cat_name; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                    @foreach ($sub_category_list[$parent_cat_id] as $sub_cat_id => $sub_cat_name)
                    @if ($id > 0 && $id == $sub_cat_id)
                        continue;
                    @endif
                    @if ( isset($sub_category_list[$sub_cat_id]) && !empty($sub_category_list[$sub_cat_id]) )
                <optgroup label="<?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                    @foreach ($sub_category_list[$sub_cat_id] as $sub_cat_id2 => $sub_cat_name2)
                    @if ($id > 0 && $id == $sub_cat_id2)
                       <?php  continue; ?>
                    @endif
                    @if ( isset($sub_category_list[$sub_cat_id2]) && !empty($sub_category_list[$sub_cat_id2]) )
                <optgroup label="<?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                    @foreach ($sub_category_list[$sub_cat_id2] as $sub_cat_id3 => $sub_cat_name3)
                    @if ($id > 0 && $id == $sub_cat_id3)
                        continue;
                    @endif
                    @if ( isset($sub_category_list[$sub_cat_id3]) && !empty($sub_category_list[$sub_cat_id3]) )
                    @foreach ($sub_category_list[$sub_cat_id3] as $sub_cat_id4 => $sub_cat_name4)
                    @if ($id > 0 && $id == $sub_cat_id4) {
                       <?php  continue; ?>
                    @endif
                    <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id4; ?>"
                        <?php echo in_array($sub_cat_id4, $category_ids) ? 'selected' : ''; ?>>
                        <?php echo str_repeat('&nbsp;', 10) . $sub_cat_name4; ?>
                    </option>
                    @endforeach
                    @else
                    <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id3; ?>"
                        <?php echo in_array($sub_cat_id3, $category_ids) ? 'selected' : ''; ?>>
                        <?php echo str_repeat('&nbsp;', 8) . $sub_cat_name3; ?>
                    </option>
                    @endif
                    @endforeach
                </optgroup>
                @else
                <option value="<?php echo $sub_cat_id2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                    <?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>
                </option>
                @endif
                @endforeach
                </optgroup>
                @else
                <option value="<?php echo $sub_cat_id; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                    <?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>
                </option>
                @endif
                @endforeach
                </optgroup>
                @else
                <option value="<?php echo $parent_cat_id; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                    {{$parent_cat_name}}
                </option>
                @endif


                @endforeach
                @endif
            </select>
        </div>
       {{-- <div class="col-md-2 form-group">
             <label>Price From</label>
             <input type="text" name="price_from" class="form-control">
        </div>
        <div class="col-md-2 form-group">
             <label>Price To</label>
             <input type="text" name="price_to" class="form-control">
        </div>--}}
        <div class="col-md-2 form-group">
             <label>Name</label>
              <input type="search" name="search_key" class="form-control form-control-sm"
                                        placeholder="" aria-controls="column-filter" value="{{ $search_key }}">
        </div>
        <div class="col-md-4 mt-4">
            
            @if($from || $to || $category || isset($_GET['search_key']))
            <a type="button" href="{{url('admin/products')}}" class="btn btn-primary float-right ml-2">Reset</a>
            @else
            <button type="reset" class="btn btn-primary float-right ml-2">Reset</button>
            @endif
            <button type="submit" class="btn btn-primary float-right">Filter</button>
        </div>
    </div>
</form>