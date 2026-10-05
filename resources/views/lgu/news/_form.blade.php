@php $n = $news ?? null; @endphp
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $n->title ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" class="form-select" required>
            @foreach(\App\Models\News::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(old('category', $n->category ?? '')==$key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="5" required>{{ old('content', $n->content ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Featured Image <span class="text-muted-gg fw-normal">(optional)</span></label><br>
        @if($n && $n->image_path)
            <img src="{{ $n->imageUrl() }}" class="rounded mb-2" style="max-height:100px;"><br>
            <div class="form-check small mb-1">
                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage">
                <label class="form-check-label" for="removeImage">Remove current image</label>
            </div>
        @endif
        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="draft" @selected(old('status', $n->status ?? 'draft')=='draft')>Draft</option>
            <option value="published" @selected(old('status', $n->status ?? '')=='published')>Published</option>
            <option value="archived" @selected(old('status', $n->status ?? '')=='archived')>Archived</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Publish Date</label>
        <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($n?->published_at)->format('Y-m-d\TH:i')) }}">
        <div class="form-text">Leave blank to publish immediately when status is Published.</div>
    </div>

    <div class="col-12"><hr></div>

    <div class="col-md-4">
        <label class="form-label">Related Gasoline Station <span class="text-muted-gg fw-normal">(optional)</span></label>
        <select name="related_station_id" class="form-select">
            <option value="">— None —</option>
            @foreach($stations as $s)
                <option value="{{ $s->id }}" @selected(old('related_station_id', $n->related_station_id ?? '')==$s->id)>{{ $s->station_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Related Barangay/Area <span class="text-muted-gg fw-normal">(optional)</span></label>
        <input type="text" name="related_barangay" class="form-control" value="{{ old('related_barangay', $n->related_barangay ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Related Fuel Type <span class="text-muted-gg fw-normal">(optional)</span></label>
        <select name="related_fuel_type_id" class="form-select">
            <option value="">— None —</option>
            @foreach($fuelTypes as $ft)
                <option value="{{ $ft->id }}" @selected(old('related_fuel_type_id', $n->related_fuel_type_id ?? '')==$ft->id)>{{ $ft->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Previous Price <span class="text-muted-gg fw-normal">(optional, for price update news)</span></label>
        <div class="input-group">
            <span class="input-group-text">₱</span>
            <input type="number" step="0.01" min="0" name="previous_price" class="form-control" value="{{ old('previous_price', $n->previous_price ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Current Price <span class="text-muted-gg fw-normal">(optional)</span></label>
        <div class="input-group">
            <span class="input-group-text">₱</span>
            <input type="number" step="0.01" min="0" name="current_price" class="form-control" value="{{ old('current_price', $n->current_price ?? '') }}">
        </div>
    </div>
</div>
