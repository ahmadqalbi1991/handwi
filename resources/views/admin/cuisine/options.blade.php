<option value="">Select Activity Type</option>
@foreach ($activity_types as $activity_type)
    <option value="{{ $activity_type->id }}" >{{ $activity_type->activity_name }}
    </option>
@endforeach