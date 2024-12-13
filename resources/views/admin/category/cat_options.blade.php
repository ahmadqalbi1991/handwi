<option value="">None</option>
@foreach ($categories as $cat)
    <option {{ $cat->id == $parent_id ? 'selected' : '' }} value="{{ $cat->id }}">
        {{ $cat->name }}</option>
@endforeach;