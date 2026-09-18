@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.info.edit'])
@section('title', 'Station Info')

@section('dashboard-content')
<h4 class="mb-1">Station Information</h4>
<p class="text-muted-gg small mb-3">{{ $station->station_name }} — update your contact details, description, logo, and photo. To change your station's name, address, or map location, contact the LGU.</p>

<div class="card" style="max-width:620px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.info.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Station Logo</label>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <x-station-logo :station="$station" :size="64" />
                        @if($station->logo_path)
                            <div class="form-check small">
                                <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="removeLogo">
                                <label class="form-check-label" for="removeLogo">Remove current logo</label>
                            </div>
                        @endif
                    </div>
                    <input type="file" name="logo" id="logoInput" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">JPG, PNG, or WEBP. Max 2MB. Used in station cards, lists, and map popups.</div>
                    <img id="logoPreview" class="d-none rounded mt-2" style="max-height:80px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Station Photo</label>
                    @if($station->photo_path)
                        <div class="mb-2">
                            <img src="{{ $station->photoUrl() }}" alt="{{ $station->station_name }}" class="rounded" style="max-height:100px;">
                            <div class="form-check small mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhoto">
                                <label class="form-check-label" for="removePhoto">Remove current photo</label>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="photo" id="photoInput" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">JPG, PNG, or WEBP. Max 4MB. Shown on your station's detail page.</div>
                    <img id="photoPreview" class="d-none rounded mt-2" style="max-height:100px;">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" maxlength="2000">{{ old('description', $station->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $station->contact_number) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Station Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $station->email) }}">
            </div>
            <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function wireImagePreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        input.addEventListener('change', function () {
            if (!this.files || !this.files[0]) { preview.classList.add('d-none'); return; }
            preview.src = URL.createObjectURL(this.files[0]);
            preview.classList.remove('d-none');
        });
    }
    wireImagePreview('logoInput', 'logoPreview');
    wireImagePreview('photoInput', 'photoPreview');
</script>
@endpush
@endsection
