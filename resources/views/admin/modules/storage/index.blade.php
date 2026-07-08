@extends('admin.layouts.app')

@push('meta')
<title>Storage Configuration | {{ config('app.name') }}</title>
<meta content="Storage Configuration" name="description" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Storage Configuration</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xxl-8 col-xl-8 col-lg-8">
            <form class="card__wrapper p-30" action="{{ route('admin.storage.update') }}" method="POST">
                @csrf

                <h4 class="mb-20">Dynamic Storage Config</h4>

                <div class="from__input-box mb-20">
                    <div class="form__input-title">
                        <label for="storage_driver">Select Storage Provider <span>*</span></label>
                    </div>
                    <div class="form__input">
                        <select name="storage_driver" id="storage_driver" class="form-control" onchange="toggleGcsFields(this.value)">
                            <option value="local" {{ old('storage_driver', $settings->storage_driver ?? 'local') === 'local' ? 'selected' : '' }}>Local Storage (Public Disk)</option>
                            <option value="gcs" {{ old('storage_driver', $settings->storage_driver ?? '') === 'gcs' ? 'selected' : '' }}>Google Cloud Storage (GCS)</option>
                        </select>
                    </div>
                </div>

                <div id="gcs_fields" style="{{ old('storage_driver', $settings->storage_driver ?? '') === 'gcs' ? '' : 'display: none;' }}">
                    <div class="from__input-box mb-20">
                        <div class="form__input-title">
                            <label for="gcs_bucket">GCS Bucket Name <span>*</span></label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="gcs_bucket" id="gcs_bucket" class="form-control" placeholder="e.g. my-app-bucket" value="{{ old('gcs_bucket', $settings->gcs_bucket ?? '') }}" />
                        </div>
                    </div>

                    <div class="from__input-box mb-20">
                        <div class="form__input-title">
                            <label for="gcs_project_id">GCP Project ID <span>*</span></label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="gcs_project_id" id="gcs_project_id" class="form-control" placeholder="e.g. my-gcp-project-123" value="{{ old('gcs_project_id', $settings->gcs_project_id ?? '') }}" />
                        </div>
                    </div>

                    <div class="from__input-box mb-20">
                        <div class="form__input-title">
                            <label for="gcs_key_file">Service Account JSON Key Content <span>*</span></label>
                        </div>
                        <div class="form__input">
                            <textarea name="gcs_key_file" id="gcs_key_file" class="form-control" rows="8" placeholder='{ "type": "service_account", ... }'>{{ old('gcs_key_file', $settings->gcs_key_file ?? '') }}</textarea>
                            <small class="text-muted">Paste the contents of your Google Cloud Service Account JSON key file here. The system will dynamically authenticate via this key.</small>
                        </div>
                    </div>
                </div>

                <div class="mt-30">
                    <button type="submit" class="btn btn-primary">Save Configuration & Test Connection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleGcsFields(driver) {
    const fields = document.getElementById('gcs_fields');
    if (driver === 'gcs') {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}
</script>
@endsection
