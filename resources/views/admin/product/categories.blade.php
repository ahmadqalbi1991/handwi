@if(isset($category_list) && count($category_list) > 0)

    @foreach($category_list as $parent_cat_id => $parent_cat_name)
        <!-- continue; -->
    @if(in_array(strtolower(str_replace(' ', '', $parent_cat_name)) ,['dinein','pickup']))
    @php
    @endphp
    @endif
        {{-- in_array(strtolower(str_replace(' ', '', $parent_cat_name)) ,['delivery']) || --}}
    
    <?php if ( isset($sub_category_list[$parent_cat_id]) && !empty($sub_category_list[$parent_cat_id]) ) { ?>
    <optgroup label="<?php echo $parent_cat_name; ?>" <?php echo  in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
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
        {{--in_array(strtolower(str_replace(' ', '', $parent_cat_name)) ,['delivery']) ||--}}
    <option value="<?php echo $parent_cat_id; ?>" <?php echo  in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
        <?php echo $parent_cat_name; ?>
    </option>
    <?php } ?>

  @endforeach
 @endif