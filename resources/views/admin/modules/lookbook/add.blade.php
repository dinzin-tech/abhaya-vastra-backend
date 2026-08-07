@extends('admin.layouts.app')

@section('title', $item ? 'Edit Lookbook' : 'Add Lookbook')

@section('content')
<div class="page-header">
    <div class="page-header-content header-elements-md-inline">
        <div class="page-title d-flex">
            <h4>
                <i class="icon-arrow-left52 mr-2"></i>
                <span class="font-weight-semibold">Lookbook</span> - {{ $item ? 'Edit Banner' : 'Add New Banner' }}
            </h4>
        </div>
        <div class="header-elements d-none py-0 mb-3 mb-md-0">
            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">
                    <i class="icon-home2 mr-2"></i> Home
                </a>
                <a href="{{ route('lookbook.index') }}" class="breadcrumb-item">Lookbook</a>
                <span class="breadcrumb-item active">{{ $item ? 'Edit' : 'Add' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="content-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ $item ? 'Edit Lookbook Banner' : 'Add New Lookbook Banner' }}</h5>
                </div>
                <div class="card-body">
                    <form id="lookbook-form" enctype="multipart/form-data">
                        @csrf
                        @if($item)
                            <input type="hidden" name="id" value="{{ $item->id }}" />
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control"
                                        value="{{ $item->title ?? '' }}"
                                        placeholder="e.g. FINE ACCESSORIES" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subtitle</label>
                                    <input type="text" name="subtitle" class="form-control"
                                        value="{{ $item->subtitle ?? '' }}"
                                        placeholder="e.g. GALLERIA LEATHERWEAR" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Link <span class="text-danger">*</span></label>
                                    <input type="text" name="link" class="form-control"
                                        value="{{ $item->link ?? '/all-products' }}"
                                        placeholder="e.g. /all-products or /category/women" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Size <span class="text-danger">*</span></label>
                                    <select name="size" class="form-control" required>
                                        <option value="medium" {{ ($item->size ?? '') === 'medium' ? 'selected' : '' }}>Medium (Half Width)</option>
                                        <option value="large"  {{ ($item->size ?? '') === 'large'  ? 'selected' : '' }}>Large (2/3 Width)</option>
                                        <option value="full"   {{ ($item->size ?? '') === 'full'   ? 'selected' : '' }}>Full Width</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="{{ $item->sort_order ?? 0 }}" min="0" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banner Image {{ $item ? '(Leave blank to keep current)' : '' }} <span class="text-danger">{{ $item ? '' : '*' }}</span></label>
                                    <input type="file" name="image" class="form-control-file"
                                        accept="image/*" {{ $item ? '' : 'required' }} />
                                    <small class="text-muted">Recommended: 1200×800px or larger. Max 4MB.</small>
                                    @if($item && $item->image)
                                        @php
                                            $imgUrl = \Illuminate\Support\Str::startsWith($item->image, ['http://', 'https://']) ? $item->image : asset('storage/' . $item->image);
                                        @endphp
                                        <div class="mt-2">
                                            <img src="{{ $imgUrl }}" alt="Current"
                                                 style="width:200px;height:120px;object-fit:cover;border:1px solid #ddd;border-radius:4px;" />
                                            <p class="text-muted small mt-1">Current image</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_active"
                                            name="is_active" value="1" {{ ($item->is_active ?? true) ? 'checked' : '' }} />
                                        <label class="custom-control-label" for="is_active">Active (visible on website)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-2">
                            <strong>Grid Size Guide:</strong><br>
                            • <b>Large</b> – Takes up 2/3 of the grid row. Best for dramatic runway shots.<br>
                            • <b>Medium</b> – Takes up 1/2 of the grid row. Best for product category shots.<br>
                            • <b>Full</b> – Full width banner. Best for seasonal campaign shots.
                        </div>

                        <div id="form-alerts" class="mt-2"></div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="icon-checkmark3 mr-2"></i>
                                {{ $item ? 'Update Lookbook' : 'Save Lookbook' }}
                            </button>
                            <a href="{{ route('lookbook.index') }}" class="btn btn-light ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('lookbook-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const formData = new FormData(this);

    fetch('{{ route("lookbook.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        const alertDiv = document.getElementById('form-alerts');
        if (data.success) {
            alertDiv.innerHTML = '<div class="alert alert-success">✓ ' + data.message + '. Redirecting...</div>';
            setTimeout(() => window.location.href = data.redirect, 1000);
        } else {
            alertDiv.innerHTML = '<div class="alert alert-danger">Error: ' + (data.message || 'Unknown error') + '</div>';
            btn.disabled = false;
            btn.textContent = '{{ $item ? "Update Lookbook" : "Save Lookbook" }}';
        }
    })
    .catch(err => {
        document.getElementById('form-alerts').innerHTML = '<div class="alert alert-danger">Server error. Please try again.</div>';
        btn.disabled = false;
        btn.textContent = '{{ $item ? "Update Lookbook" : "Save Lookbook" }}';
    });
});
</script>
@endsection
