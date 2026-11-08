@extends('layouts.app')
@section('title', 'Report an Issue')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h4 class="mb-1">Report an Issue to the LGU</h4>
        <p class="text-muted-gg mb-4">No account needed. Your report goes directly to the Manolo Fortich LGU for review.</p>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data">
                    @csrf
                    {{-- honeypot field: real users never see or fill this --}}
                    <input type="text" name="website" value="" style="position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-muted-gg fw-normal">(optional)</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Information <span class="text-muted-gg fw-normal">(optional)</span></label>
                            <input type="text" name="contact" class="form-control" value="{{ old('contact') }}" placeholder="Phone or email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Complaint Category</label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\Complaint::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category')==$key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Related Gasoline Station <span class="text-muted-gg fw-normal">(optional)</span></label>
                            <select name="station_id" class="form-select">
                                <option value="">— Not specific to a station —</option>
                                @foreach($stations as $s)
                                    <option value="{{ $s->id }}" @selected(old('station_id', request('station_id'))==$s->id)>{{ $s->station_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Complaint Description</label>
                            <textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="photoInput">Upload Image Evidence (Optional)</label>
                            <input type="file" name="photo" id="photoInput" class="form-control @error('photo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            @error('photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            <div class="form-text">JPG, PNG or WEBP, up to 4 MB. Only the LGU can see your image.</div>
                            <div id="photoError" class="text-danger small mt-1 d-none"></div>
                            <div id="photoPreviewWrap" class="mt-2 d-none">
                                <img id="photoPreview" alt="Selected evidence preview" class="img-thumbnail" style="max-height:160px;">
                                <div class="mt-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="photoRemove"><i class="bi bi-trash"></i> Remove</button>
                                    <button type="button" class="btn btn-sm btn-outline-brand" id="photoReplace"><i class="bi bi-arrow-repeat"></i> Replace</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-brand mt-4"><i class="bi bi-send-fill me-1"></i>Submit Complaint</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const input = document.getElementById('photoInput');
    const err = document.getElementById('photoError');
    const wrap = document.getElementById('photoPreviewWrap');
    const img = document.getElementById('photoPreview');
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    function clearPhoto() { input.value = ''; wrap.classList.add('d-none'); img.removeAttribute('src'); }
    input.addEventListener('change', function () {
        err.classList.add('d-none');
        const f = input.files && input.files[0];
        if (!f) { clearPhoto(); return; }
        if (!allowed.includes(f.type)) { err.textContent = 'Please choose a JPG, PNG or WEBP image.'; err.classList.remove('d-none'); clearPhoto(); return; }
        if (f.size > 4 * 1024 * 1024) { err.textContent = 'The image is larger than 4 MB. Please choose a smaller one.'; err.classList.remove('d-none'); clearPhoto(); return; }
        img.src = URL.createObjectURL(f);
        wrap.classList.remove('d-none');
    });
    document.getElementById('photoRemove').addEventListener('click', clearPhoto);
    document.getElementById('photoReplace').addEventListener('click', function () { input.click(); });
})();
</script>
@endpush
@endsection
