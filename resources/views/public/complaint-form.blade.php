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
                            <label class="form-label">Photo / Evidence <span class="text-muted-gg fw-normal">(optional)</span></label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <button class="btn btn-brand mt-4"><i class="bi bi-send-fill me-1"></i>Submit Complaint</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
