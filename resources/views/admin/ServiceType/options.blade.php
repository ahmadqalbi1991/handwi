<option value="">Select Service Type</option>
@foreach ($service_types as $row)
    <option value="{{ $row->id }}" >{{ $row->activity_name }}
    </option>
@endforeach