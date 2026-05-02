<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="product_name">Name</label>
            <input type="text" name="product_name" id="product_name" class="form-control @error('product_name') is-invalid @enderror"
                   value="{{ old('product_name', $item->product_name ?? '') }}" maxlength="100" required>
            @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-4"></div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="photo1">Photo1</label>
            <div class="border rounded p-2 bg-light">
                <input type="file" name="photo1" id="photo1" accept="image/*" class="form-control-file @error('photo1') is-invalid @enderror" {{ isset($item) ? '' : 'required' }}>
                <small class="text-muted d-block mt-1">Upload JPG, PNG or WEBP (max 5MB)</small>
            </div>
            @error('photo1') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @if(!empty($item->photo1))
                <div class="mt-2 d-flex align-items-center">
                    <img src="{{ asset($item->photo1) }}" alt="Current image" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">
                    <span class="ml-2 text-muted small">Current image</span>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-4"></div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="product_type">Type</label>
            <select name="product_type" id="product_type" class="form-control @error('product_type') is-invalid @enderror" required>
                <option value="">Select Type</option>
                @foreach(($itemTypes ?? []) as $type)
                    <option value="{{ $type->id }}" @selected((string) old('product_type', $item->product_type ?? '') === (string) $type->id)>
                        {{ $type->item_type }}
                    </option>
                @endforeach
            </select>
            @error('product_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="weight">Weight</label>
            <input type="number" step="0.01" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror"
                   value="{{ old('weight', $item->weight ?? 0) }}">
            @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('masterapp.ruhi-items.index') }}" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</div>

