@php $s = $station ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Station Name</label>
        <input type="text" name="station_name" class="form-control" value="{{ old('station_name', $s->station_name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $s->address ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Barangay</label>
        <input type="text" name="barangay" class="form-control" value="{{ old('barangay', $s->barangay ?? 'Tankulan') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Municipality</label>
        <input type="text" name="municipality" class="form-control" value="{{ old('municipality', $s->municipality ?? 'Manolo Fortich') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Province</label>
        <input type="text" name="province" class="form-control" value="{{ old('province', $s->province ?? 'Bukidnon') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Latitude</label>
        <input type="number" step="any" name="latitude" class="form-control" value="{{ old('latitude', $s->latitude ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Longitude</label>
        <input type="number" step="any" name="longitude" class="form-control" value="{{ old('longitude', $s->longitude ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $s->contact_number ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" @selected(old('status', $s->status ?? 'active')=='active')>Active</option>
            <option value="inactive" @selected(old('status', $s->status ?? '')=='inactive')>Inactive</option>
        </select>
    </div>
</div>
