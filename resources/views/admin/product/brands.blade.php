<option value="">Select Brand</option>
@foreach ($brand as $cnt)
    <option <?php if(!empty($product->product_brand_id)) { ?> {{$product->product_brand_id == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
        {{ $cnt->name }}</option>
@endforeach;