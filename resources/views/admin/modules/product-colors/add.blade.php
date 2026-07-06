@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Product Color | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Product Color" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@push('appendCss')
<style>
    .pcf-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .pcf-header h4 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .pcf-header .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
        padding: 8px 18px;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        color: #475569;
        text-decoration: none;
        transition: all .2s;
    }
    .pcf-header .btn-back:hover { background:#f1f5f9; color:#0f172a; }

    .pcf-row { display:grid; gap:20px; margin-bottom:20px; }
    .pcf-row-1 { grid-template-columns: 1fr; }
    .pcf-row-2 { grid-template-columns: 1fr 1fr; }

    .pcf-field label {
        display:block; font-size:0.8rem; font-weight:600;
        color:#374151; margin-bottom:6px; letter-spacing:.3px;
    }
    .pcf-field label .req { color:#ef4444; margin-left:2px; }
    .pcf-field .form-control, .pcf-field select {
        width:100%; padding:10px 14px; font-size:0.9rem;
        border:1.5px solid #e2e8f0; border-radius:8px;
        background:#f8fafc; color:#1e293b; transition: border-color .2s, box-shadow .2s;
        outline:none;
    }
    .pcf-field .form-control:focus, .pcf-field select:focus {
        border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1);
        background:#fff;
    }

    /* Live Color Swatch */
    .color-swatch-display {
        width: 38px; height: 38px; border-radius: 8px;
        border: 2px solid #cbd5e1; display: inline-block;
        background: #e2e8f0; vertical-align: middle;
        margin-left: 10px; transition: background .2s;
    }

    /* Dropzone */
    .dropzone-area {
        border:2.5px dashed #c7d2fe; border-radius:12px;
        background:#f5f7ff; padding:28px 20px; text-align:center;
        cursor:pointer; transition: border-color .2s, background .2s;
        position:relative;
    }
    .dropzone-area:hover, .dropzone-area.drag-over {
        border-color:#6366f1; background:#eef2ff;
    }
    .dropzone-area input[type=file] {
        position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;
    }
    .dropzone-icon { font-size:2.5rem; color:#a5b4fc; margin-bottom:10px; }
    .dropzone-title { font-weight:700; font-size:0.95rem; color:#3730a3; }
    .dropzone-sub { font-size:0.78rem; color:#6366f1; margin-top:4px; }
    .dropzone-note { font-size:0.72rem; color:#94a3b8; margin-top:8px; }

    /* Image preview card */
    .img-preview-grid { display:flex; flex-wrap:wrap; gap:12px; margin-top:14px; }
    .img-preview-card {
        position:relative; width:120px; height:120px;
        border-radius:10px; overflow:hidden;
        border:1.5px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,.07);
    }
    .img-preview-card img { width:100%; height:100%; object-fit:cover; display:block; }
    .img-preview-card .img-size-badge {
        position:absolute; bottom:0; left:0; right:0;
        background:rgba(0,0,0,.55); color:#fff; font-size:0.65rem;
        text-align:center; padding:3px 0; font-weight:600;
    }
    .img-preview-card .img-remove-btn {
        position:absolute; top:4px; right:4px; width:22px; height:22px;
        background:#ef4444; color:#fff; border:none; border-radius:50%;
        font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center;
        transition: background .2s;
    }
    .img-preview-card .img-remove-btn:hover { background:#dc2626; }
    .compression-bar {
        height:4px; border-radius:999px; background:#e2e8f0; margin-top:8px; overflow:hidden;
    }
    .compression-bar-fill { height:100%; background:#6366f1; width:0%; transition:width .4s; border-radius:999px; }

    .btn-pf-save {
        display:inline-flex; align-items:center; gap:8px;
        background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff;
        border:none; border-radius:10px; padding:12px 28px;
        font-size:0.9rem; font-weight:700; cursor:pointer;
        box-shadow:0 4px 14px rgba(99,102,241,.35); transition: transform .15s, box-shadow .15s;
    }
    .btn-pf-save:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(99,102,241,.4); }
    .btn-pf-edit {
        display:inline-flex; align-items:center; gap:8px;
        background:#fff; color:#6366f1; border:2px solid #6366f1;
        border-radius:10px; padding:10px 24px; font-size:0.9rem;
        font-weight:700; cursor:pointer; transition: background .2s;
    }
    .btn-pf-edit:hover { background:#eef2ff; }
</style>
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="pcf-header">
        <div>
            <h4>
                <i class="fa-regular fa-palette me-2" style="color:#6366f1"></i>
                {{ $item ? 'Update Product Color' : 'Add New Product Color' }}
            </h4>
        </div>
        <a href="{{ route('product-colors.index') }}" class="btn-back">
            <i class="fa-regular fa-arrow-left"></i> Back to Colors
        </a>
    </div>

    <div class="card__wrapper" style="border-radius:16px;padding:28px;">
        @if($item)
        <div class="alert d-flex align-items-center gap-2 mb-4" style="background:#fef3c7;border:1.5px solid #fcd34d;border-radius:10px;font-size:.85rem;color:#92400e;padding:12px 18px;">
            <i class="fa-solid fa-lock"></i>
            <span>Form is in <strong>view mode</strong>. Click <strong>Enable Edit</strong> to make changes.</span>
            <button type="button" id="editBtn" class="btn-pf-edit ms-auto" onclick="enableEdit()">
                <i class="fa-regular fa-pen"></i> Enable Edit
            </button>
        </div>
        @endif

        <form id="colorForm" action="{{ route('product-colors.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $item ? $item->id : '' }}">
            <input type="hidden" name="removed_images" id="removed_images" value="[]">

            <div class="pcf-row pcf-row-2">
                <div class="pcf-field">
                    <label for="product_id">Product <span class="req">*</span></label>
                    <select name="product_id" id="product_id" {{ $item ? 'disabled' : '' }} required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                {{ ($item && $item->product_id == $product->id) ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pcf-field">
                    <label for="color">Color (Name or Hex) <span class="req">*</span></label>
                    <div style="display:flex;align-items:center;">
                        <input type="text" name="color" id="color" class="form-control" 
                               value="{{ $item->color ?? '' }}" {{ $item ? 'readonly' : '' }} required
                               placeholder="e.g. Red, #FF0000" style="flex:1;">
                        <span class="color-swatch-display" id="colorSwatch"></span>
                    </div>
                </div>
            </div>

            <div class="pcf-row pcf-row-1">
                <div class="pcf-field">
                    <label>Color Images (multiple allowed)</label>
                    <div class="dropzone-area" id="colorImagesZone" style="{{ $item ? 'pointer-events:none;opacity:0.6' : '' }}">
                        <input type="file" id="images_input" accept="image/*" multiple>
                        <div class="dropzone-icon">🖼️</div>
                        <div class="dropzone-title">Drag & Drop or Click</div>
                        <div class="dropzone-sub">Upload color-specific pictures</div>
                        <div class="dropzone-note">JPG, PNG, WEBP — auto-compressed to ≤500KB each</div>
                    </div>
                    <div class="compression-bar" id="progressBar" style="display:none">
                        <div class="compression-bar-fill" id="progressBarFill"></div>
                    </div>
                    <div class="compress-status" id="compressStatus" style="font-size:0.75rem;margin-top:6px;font-weight:600;color:#6366f1;"></div>
                    <div class="img-preview-grid" id="imagesPreviewGrid">
                        @if($item && $item->images)
                            @foreach(is_array($item->images) ? $item->images : json_decode($item->images, true) as $img)
                                <div class="img-preview-card" data-path="{{ $img }}">
                                    <img src="{{ asset('storage/'.$img) }}">
                                    <span class="img-size-badge">Current</span>
                                    <button type="button" class="img-remove-btn d-none" onclick="removeOldImage(this,'{{ $img }}')">✕</button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    {{-- Hidden file input for actual submission --}}
                    <input type="file" name="images[]" id="images_hidden" style="display:none" accept="image/*" multiple>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;">
                <a href="{{ route('product-colors.index') }}" class="btn-back" style="padding:11px 24px;border-radius:10px;">Cancel</a>
                <button type="button" class="btn-pf-save saveBtn" id="saveBtn" onclick="submitForm()"
                    style="{{ $item ? 'display:none' : '' }}">
                    <i class="fa-regular fa-floppy-disk"></i>
                    {{ $item ? 'Save Changes' : 'Add Color' }}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('appendJs')
<script>
    // Swatch preview update
    const colorInput = document.getElementById('color');
    const colorSwatch = document.getElementById('colorSwatch');

    function updateSwatch() {
        const val = colorInput.value.trim();
        if (val.startsWith('#') && val.length >= 4) {
            colorSwatch.style.backgroundColor = val;
        } else {
            colorSwatch.style.backgroundColor = '#e2e8f0';
        }
    }
    colorInput.addEventListener('input', updateSwatch);
    updateSwatch();

    // Enable edit mode
    function enableEdit() {
        document.querySelectorAll('input:not([type="hidden"]), select').forEach(el => {
            el.removeAttribute('readonly');
            el.removeAttribute('disabled');
        });
        document.getElementById('colorImagesZone').style.pointerEvents = 'auto';
        document.getElementById('colorImagesZone').style.opacity = '1';
        document.querySelectorAll('.img-remove-btn').forEach(btn => btn.classList.remove('d-none'));
        document.getElementById('saveBtn').style.display = 'inline-flex';
        document.getElementById('editBtn') && (document.getElementById('editBtn').style.display = 'none');
    }

    // Compression Utility
    function compressImage(file, maxSizeKB = 500, quality = 0.85) {
        return new Promise((resolve) => {
            const maxBytes = maxSizeKB * 1024;
            if (file.size <= maxBytes) { resolve(file); return; }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let { width, height } = img;
                    const MAX_DIM = 2000;
                    if (width > MAX_DIM || height > MAX_DIM) {
                        const ratio = Math.min(MAX_DIM / width, MAX_DIM / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    let q = quality;
                    let attempt = 0;
                    function tryCompress() {
                        canvas.toBlob(blob => {
                            if ((blob.size <= maxBytes) || q <= 0.2 || attempt > 6) {
                                const compressed = new File([blob], file.name, { type: 'image/jpeg' });
                                resolve(compressed);
                            } else {
                                q -= 0.1;
                                attempt++;
                                tryCompress();
                            }
                        }, 'image/jpeg', q);
                    }
                    tryCompress();
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function formatBytes(b) {
        if (b < 1024) return b + ' B';
        if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
        return (b/1024/1024).toFixed(2) + ' MB';
    }

    // Setup Drag and Drop
    let stagedFiles = [];
    const zoneEl = document.getElementById('colorImagesZone');
    const inputEl = document.getElementById('images_input');
    const hiddenEl = document.getElementById('images_hidden');
    const gridEl = document.getElementById('imagesPreviewGrid');
    const barEl = document.getElementById('progressBar');
    const fillEl = document.getElementById('progressBarFill');
    const statEl = document.getElementById('compressStatus');

    ['dragenter','dragover'].forEach(ev => {
        zoneEl.addEventListener(ev, e => { e.preventDefault(); zoneEl.classList.add('drag-over'); });
    });
    ['dragleave','drop'].forEach(ev => {
        zoneEl.addEventListener(ev, e => { e.preventDefault(); zoneEl.classList.remove('drag-over'); });
    });
    zoneEl.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
    inputEl.addEventListener('change', e => handleFiles(e.target.files));

    async function handleFiles(files) {
        if (!files.length) return;

        barEl.style.display = 'block';
        fillEl.style.width = '30%';
        statEl.textContent = `Compressing ${files.length} image(s)…`;

        for(let i=0; i<files.length; i++) {
            const file = files[i];
            const compressed = await compressImage(file, 500);
            stagedFiles.push(compressed);

            const sizeLabel = formatBytes(compressed.size);
            const isOk = compressed.size <= 500 * 1024;

            const card = document.createElement('div');
            card.className = 'img-preview-card';
            
            const reader = new FileReader();
            reader.onload = function(ev) {
                card.innerHTML = `
                    <img src="${ev.target.result}">
                    <span class="img-size-badge ${isOk ? '' : 'warning'}">${sizeLabel}</span>
                    <button type="button" class="img-remove-btn" onclick="removeStagedImage(this, ${stagedFiles.length - 1})">✕</button>
                `;
            };
            reader.readAsDataURL(compressed);
            gridEl.appendChild(card);
        }

        fillEl.style.width = '100%';
        statEl.textContent = `✅ ${files.length} image(s) compressed successfully!`;
        setTimeout(() => { barEl.style.display = 'none'; fillEl.style.width = '0%'; }, 1500);

        updateHiddenInput();
    }

    function removeStagedImage(btn, index) {
        stagedFiles.splice(index, 1);
        btn.closest('.img-preview-card').remove();
        updateHiddenInput();
    }

    let removedOldImages = [];
    function removeOldImage(btn, imgPath) {
        removedOldImages.push(imgPath);
        btn.closest('.img-preview-card').remove();
        document.getElementById('removed_images').value = JSON.stringify(removedOldImages);
    }

    function updateHiddenInput() {
        const dt = new DataTransfer();
        stagedFiles.forEach(file => dt.items.add(file));
        hiddenEl.files = dt.files;
    }

    // Submit form handler
    function submitForm() {
        const form = document.getElementById('colorForm');
        const formData = new FormData(form);

        // Append staged compressed files
        stagedFiles.forEach(f => formData.append('images[]', f));

        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Saving…';

        axios.post(form.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        }).then(res => {
            if (res.data.success) {
                toastr.success(res.data.message || 'Saved successfully!');
                setTimeout(() => { window.location.href = res.data.redirect || '/admin/product-colors'; }, 1000);
            } else {
                toastr.error(res.data.message || 'Something went wrong.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> Save';
            }
        }).catch(err => {
            const errors = err.response?.data?.errors;
            if (errors) {
                const msgs = Object.values(errors).flat().join('<br>');
                toastr.error(msgs, 'Validation Error', { timeOut: 6000 });
            } else {
                toastr.error('Failed to save color.');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> Save';
        });
    }
</script>
@endpush
