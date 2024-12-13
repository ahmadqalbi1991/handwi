<?php
$attribute_list = $attribute_list ?? []; 
$selected_attributes = $selected_attributes ?? []; 
?>
@if ( !empty($attribute_list) )
    @if ( $action == 'add' )
        <div class="form-row">
            <div class="col-lg-4">
                <select class="form-control" name="select_attribute">
                    <option value="">Select Attribute</option>
                    @foreach ($attribute_list as $t_id => $t_row)
                        <option value="{{ $t_id}}">{{$t_row['name']}}</option>    
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <button type="button" class="btn btn-warning" data-role="add-attribute"> Add Attribute</button>
            </div>
        </div>
    @endif
    <div class="form-row">
        <?php $t_col_width = ( count($attribute_list) % 3 == 0 ) ? 4 : 6; ?>
        @foreach ($attribute_list as $t_id => $t_row)
            
            @if ( ($action == 'edit') && (array_key_exists($t_id, $selected_attributes) === FALSE) )
            <?php     continue; ?>
           @endif
            
            <div class="col-lg-{{$t_col_width}}" data-role="attribute-col" <?php echo ($action == 'add' ? 'style="display:none;"' : '') ?> data-attribute-id="<?php echo $t_id ?>">
                <div class="form-group profile-form">
                    <label>
                        {{ $t_row['name']}}
                        @if ( $action == 'add' )
                            <a href="#" class="badge badge-danger ml-2" data-role="remove-attribute">Remove</a>
                        @endif
                    </label>
                    <select class="form-control select2" name="product_attribute[<?php echo $t_id ?>][]" multiple data-role="attribute-select" data-placeholder="Choose..." data-allow-clear="1">
                    @foreach ($t_row['values'] as $t_val_row)
                        <option value="<?php echo $t_val_row[0] ?>" <?php echo ( (array_key_exists($t_id, $selected_attributes) !== FALSE) && in_array($t_val_row[0], $selected_attributes[$t_id]) ? 'selected': '' ) ?>>
                        {{$t_val_row[1]}}</option>
                    @endforeach
                    </select>
                </div>
            </div>
        @endforeach
    </div>
@endif