@extends('admin.layouts.app')
@push('meta')
<title>Bulk Import Products | {{ config('app.name') }}</title>
<meta content="Bulk Import Products" name="description" />
@endpush

@push('appendCss')
<style>
    .bi-page { max-width: 860px; margin: 0 auto; }

    .bi-header { margin-bottom: 28px; }
    .bi-header h4 { font-size:1.3rem; font-weight:800; color:#1e293b; }
    .bi-header p  { color:#64748b; font-size:.875rem; margin-top:4px; }

    /* Steps */
    .bi-steps { display:flex; gap:0; margin-bottom:32px; }
    .bi-step {
        flex:1; display:flex; flex-direction:column; align-items:center;
        position:relative; text-align:center;
    }
    .bi-step:not(:last-child)::after {
        content:''; position:absolute; top:18px; left:50%; width:100%;
        height:2px; background:#e2e8f0; z-index:0;
    }
    .bi-step-circle {
        width:36px; height:36px; border-radius:50%; border:2.5px solid #e2e8f0;
        display:flex; align-items:center; justify-content:center;
        font-weight:800; font-size:.85rem; color:#94a3b8; background:#fff;
        position:relative; z-index:1; transition: all .2s;
    }
    .bi-step.done .bi-step-circle { background:#6366f1; color:#fff; border-color:#6366f1; }
    .bi-step-label { font-size:.72rem; font-weight:700; color:#94a3b8; margin-top:8px; text-transform:uppercase; letter-spacing:.5px; }
    .bi-step.done .bi-step-label { color:#6366f1; }

    /* Cards */
    .bi-card {
        background:#fff; border:1.5px solid #f1f5f9; border-radius:14px;
        padding:24px; margin-bottom:20px;
    }
    .bi-card-title {
        font-size:.9rem; font-weight:800; color:#1e293b; margin-bottom:6px;
        display:flex; align-items:center; gap:8px;
    }
    .bi-card-title .step-badge {
        width:26px; height:26px; border-radius:50%; background:#6366f1; color:#fff;
        display:flex; align-items:center; justify-content:center;
        font-size:.72rem; font-weight:800; flex-shrink:0;
    }
    .bi-card p { color:#64748b; font-size:.82rem; margin-bottom:12px; }

    /* Download button */
    .btn-download-template {
        display:inline-flex; align-items:center; gap:8px;
        background:#f0fdf4; color:#16a34a; border:1.5px solid #86efac;
        border-radius:9px; padding:10px 20px; font-size:.875rem;
        font-weight:700; text-decoration:none; transition: background .2s;
    }
    .btn-download-template:hover { background:#dcfce7; color:#15803d; }

    /* Column reference table */
    .col-ref { width:100%; border-collapse:collapse; font-size:.8rem; margin-top:14px; }
    .col-ref th { background:#f8fafc; color:#475569; font-weight:700; padding:8px 12px; text-align:left; border-bottom:2px solid #e2e8f0; }
    .col-ref td { padding:8px 12px; border-bottom:1px solid #f1f5f9; color:#374151; }
    .col-ref td code { background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:.78rem; }
    .col-ref .req-tag { color:#ef4444; font-size:.7rem; font-weight:700; }
    .col-ref .opt-tag { color:#94a3b8; font-size:.7rem; }

    /* Dropzone */
    .csv-dropzone {
        border:2.5px dashed #c7d2fe; border-radius:12px; background:#f5f7ff;
        padding:40px 20px; text-align:center; cursor:pointer;
        transition: border-color .2s, background .2s; position:relative;
    }
    .csv-dropzone:hover, .csv-dropzone.drag-over { border-color:#6366f1; background:#eef2ff; }
    .csv-dropzone input[type=file] {
        position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;
    }
    .csv-dropzone-icon { font-size:3rem; margin-bottom:10px; }
    .csv-dropzone-title { font-weight:800; color:#3730a3; font-size:1rem; }
    .csv-dropzone-sub   { color:#6366f1; font-size:.82rem; margin-top:4px; }
    .csv-file-selected  {
        margin-top:12px; background:#fff; border:1.5px solid #c7d2fe;
        border-radius:8px; padding:10px 14px; font-size:.82rem;
        display:flex; align-items:center; gap:10px; display:none;
    }
    .csv-file-selected.visible { display:flex; }

    /* Import button */
    .btn-import {
        display:inline-flex; align-items:center; gap:8px;
        background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff;
        border:none; border-radius:10px; padding:12px 28px;
        font-size:.9rem; font-weight:700; cursor:pointer;
        box-shadow:0 4px 14px rgba(99,102,241,.35); transition: transform .15s;
        margin-top:16px;
    }
    .btn-import:hover { transform:translateY(-1px); }

    /* Result alerts */
    .bi-result {
        border-radius:10px; padding:16px 20px; margin-top:16px; font-size:.875rem;
    }
    .bi-result.success { background:#f0fdf4; border:1.5px solid #86efac; color:#166534; }
    .bi-result.warning { background:#fffbeb; border:1.5px solid #fde68a; color:#92400e; }
    .bi-result ul { margin:8px 0 0 18px; padding:0; }
    .bi-result li { margin-bottom:4px; }

    /* Category reference */
    .cat-pills { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
    .cat-pill {
        background:#f1f5f9; border-radius:999px; padding:4px 12px;
        font-size:.75rem; font-weight:700; color:#475569;
    }
    .cat-pill code { color:#6366f1; margin-left:4px; }
</style>
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-20">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active">Bulk Import</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="bi-page">
        <div class="bi-header">
            <h4><i class="fa-regular fa-file-arrow-up me-2" style="color:#16a34a"></i> Bulk Product Import</h4>
            <p>Import multiple products at once by uploading a CSV file. Download the template, fill it in, and upload to get started.</p>
        </div>

        {{-- Progress Steps --}}
        <div class="bi-steps">
            <div class="bi-step done">
                <div class="bi-step-circle">1</div>
                <div class="bi-step-label">Download Template</div>
            </div>
            <div class="bi-step">
                <div class="bi-step-circle">2</div>
                <div class="bi-step-label">Fill Products</div>
            </div>
            <div class="bi-step">
                <div class="bi-step-circle">3</div>
                <div class="bi-step-label">Upload CSV</div>
            </div>
            <div class="bi-step">
                <div class="bi-step-circle">4</div>
                <div class="bi-step-label">Done!</div>
            </div>
        </div>

        {{-- STEP 1: Download Template --}}
        <div class="bi-card">
            <div class="bi-card-title">
                <span class="step-badge">1</span> Download the CSV Template
            </div>
            <p>Start with our pre-formatted template that includes all required column headers and sample data.</p>
            <a href="{{ route('products.bulk-import.template') }}" class="btn-download-template">
                <i class="fa-regular fa-download"></i> Download Template (CSV)
            </a>

            <table class="col-ref">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Description</th>
                        <th>Example</th>
                        <th>Required?</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>name</code></td>
                        <td>Product name</td>
                        <td>Silver Bracelet</td>
                        <td><span class="req-tag">Required</span></td>
                    </tr>
                    <tr>
                        <td><code>slug</code></td>
                        <td>URL slug (auto-generated if empty)</td>
                        <td>silver-bracelet</td>
                        <td><span class="opt-tag">Optional</span></td>
                    </tr>
                    <tr>
                        <td><code>description</code></td>
                        <td>Product description</td>
                        <td>Elegant bracelet...</td>
                        <td><span class="opt-tag">Optional</span></td>
                    </tr>
                    <tr>
                        <td><code>category_id</code></td>
                        <td>ID of the category (see below)</td>
                        <td>2</td>
                        <td><span class="req-tag">Required</span></td>
                    </tr>
                    <tr>
                        <td><code>gender</code></td>
                        <td>male / female / unisex</td>
                        <td>female</td>
                        <td><span class="req-tag">Required</span></td>
                    </tr>
                    <tr>
                        <td><code>best_seller</code></td>
                        <td>1 = yes, 0 = no</td>
                        <td>1</td>
                        <td><span class="opt-tag">Optional</span></td>
                    </tr>
                    <tr>
                        <td><code>is_featured</code></td>
                        <td>1 = yes, 0 = no</td>
                        <td>0</td>
                        <td><span class="opt-tag">Optional</span></td>
                    </tr>
                    <tr>
                        <td><code>customizable</code></td>
                        <td>1 = yes, 0 = no</td>
                        <td>0</td>
                        <td><span class="opt-tag">Optional</span></td>
                    </tr>
                </tbody>
            </table>

            {{-- Category ID reference --}}
            <div style="margin-top:16px;">
                <div style="font-size:.8rem;font-weight:700;color:#374151;margin-bottom:6px;">
                    Available Category IDs:
                </div>
                <div class="cat-pills">
                    @foreach($categories as $cat)
                        <span class="cat-pill">{{ $cat->name }} <code>ID: {{ $cat->id }}</code></span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- STEP 2: Upload --}}
        <div class="bi-card">
            <div class="bi-card-title">
                <span class="step-badge">2</span> Upload Your CSV File
            </div>
            <p>Upload your filled CSV. We'll validate each row and report any errors. Successfully validated products will be imported immediately.</p>

            {{-- Import result messages --}}
            @if(session('import_success'))
            <div class="bi-result success">
                <strong><i class="fa-regular fa-circle-check me-2"></i>{{ session('import_success') }}</strong>
            </div>
            @endif

            @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="bi-result warning">
                <strong><i class="fa-regular fa-triangle-exclamation me-2"></i>Some rows had errors:</strong>
                <ul>
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('products.bulk-import.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                @if(isset($errors) && $errors->any())
                <div class="bi-result warning" style="margin-bottom:12px;">
                    <strong><i class="fa-regular fa-triangle-exclamation me-2"></i>Upload Error:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="csv-dropzone" id="csvDropzone">
                    <input type="file" name="csv_file" id="csvFileInput" accept=".csv,.txt">
                    <div class="csv-dropzone-icon">📂</div>
                    <div class="csv-dropzone-title">Drag & Drop CSV File Here</div>
                    <div class="csv-dropzone-sub">or click to browse</div>
                </div>

                <div class="csv-file-selected" id="csvFileSelected">
                    <i class="fa-regular fa-file-csv" style="color:#16a34a;font-size:1.3rem;"></i>
                    <div>
                        <div style="font-weight:700;color:#1e293b;" id="csvFileName">—</div>
                        <div style="color:#64748b;font-size:.75rem;" id="csvFileSize">—</div>
                    </div>
                    <button type="button" onclick="clearCsvFile()" style="margin-left:auto;background:none;border:none;color:#ef4444;cursor:pointer;font-size:1.1rem;">✕</button>
                </div>

                <button type="submit" class="btn-import" id="importBtn">
                    <i class="fa-regular fa-file-import"></i> Import Products
                </button>
            </form>
        </div>

        {{-- Tips --}}
        <div class="bi-card" style="background:#fafafa;">
            <div class="bi-card-title" style="color:#64748b;">
                <span style="font-size:1.1rem;">💡</span> Tips for Successful Import
            </div>
            <ul style="color:#64748b;font-size:.82rem;padding-left:18px;line-height:1.9;">
                <li>Save your spreadsheet as <strong>CSV (Comma Separated Values)</strong> — not .xlsx.</li>
                <li>Do not remove or rename the header row from the template.</li>
                <li><code>gender</code> must be exactly <code>male</code>, <code>female</code>, or <code>unisex</code> (lowercase).</li>
                <li>If <code>slug</code> is left empty, one will be auto-generated from the name.</li>
                <li>Duplicate slugs are automatically suffixed (e.g., <code>ring-1</code>, <code>ring-2</code>).</li>
                <li>Images must be uploaded separately via the product edit page after import.</li>
                <li>Maximum CSV file size: <strong>5MB</strong>.</li>
            </ul>
        </div>

        <div style="text-align:center;margin-top:10px;">
            <a href="{{ route('products.index') }}" style="color:#6366f1;font-weight:700;font-size:.875rem;">
                <i class="fa-regular fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>
</div>
@stop

@push('appendJs')
<script>
    // Dropzone drag/drop
    const zone = document.getElementById('csvDropzone');
    const fileInput = document.getElementById('csvFileInput');
    const selectedBox = document.getElementById('csvFileSelected');

    ['dragenter','dragover'].forEach(e => {
        zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.add('drag-over'); });
    });
    ['dragleave','drop'].forEach(e => {
        zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.remove('drag-over'); });
    });
    zone.addEventListener('drop', ev => { handleCsvFile(ev.dataTransfer.files[0]); });
    fileInput.addEventListener('change', () => { handleCsvFile(fileInput.files[0]); });

    function handleCsvFile(file) {
        if (!file) return;
        document.getElementById('csvFileName').textContent = file.name;
        document.getElementById('csvFileSize').textContent = (file.size/1024).toFixed(1) + ' KB';
        selectedBox.classList.add('visible');
        // Mark step 3 done
        document.querySelectorAll('.bi-step')[2].classList.add('done');
    }

    function clearCsvFile() {
        const dt = new DataTransfer();
        fileInput.files = dt.files;
        selectedBox.classList.remove('visible');
        document.querySelectorAll('.bi-step')[2].classList.remove('done');
    }

    // Show loading on submit
    document.getElementById('importForm').addEventListener('submit', function() {
        const btn = document.getElementById('importBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Importing…';
        // Mark step 4 progress
        document.querySelectorAll('.bi-step')[3].classList.add('done');
    });

    @if(session('import_success'))
        // Mark all steps done
        document.querySelectorAll('.bi-step').forEach(s => s.classList.add('done'));
        toastr.options = { closeButton:true, progressBar:true, positionClass:"toast-top-right", timeOut:"5000" };
        toastr.success("{{ session('import_success') }}");
    @endif
</script>
@endpush
