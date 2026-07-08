@extends('admin.layouts.app')

@push('meta')
<title>Design Library | {{ config('app.name') }}</title>
<meta content="Manage Print Designs" name="description" />
<style>
    .design-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
    }
    .design-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
    }
    .design-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .design-img-wrapper {
        position: relative;
        padding-top: 100%;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .design-img {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        object-fit: contain;
        padding: 10px;
    }
    .design-info {
        padding: 12px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .design-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .design-cat {
        font-size: 0.75rem;
        color: #64748b;
    }
    .category-list-group {
        max-height: 350px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Print Designs Library</li>
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

    <div class="row g-4">
        <!-- Left Column: Categories management -->
        <div class="col-lg-4">
            <!-- Add Category -->
            <form class="card__wrapper p-20 mb-20" action="{{ route('admin.print-designs.store-category') }}" method="POST">
                @csrf
                <h5 class="mb-15">Add Design Category</h5>
                <div class="from__input-box mb-15">
                    <div class="form__input">
                        <input type="text" name="name" class="form-control" placeholder="e.g. Anime, Vintage" required />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Create Category</button>
            </form>

            <!-- Categories List -->
            <div class="card__wrapper p-20">
                <h5 class="mb-15">Design Categories</h5>
                @if($categories->isEmpty())
                    <p class="text-muted text-center py-3">No categories created yet.</p>
                @else
                    <ul class="list-group category-list-group">
                        @foreach($categories as $cat)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $cat->name }}</strong>
                                    <span class="badge bg-secondary rounded-pill ms-2">{{ $cat->designs_count ?? $cat->designs()->count() }}</span>
                                </div>
                                <form action="{{ route('admin.print-designs.destroy-category', $cat->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category? Designs inside it will have their category removed.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0 m-0"><i class="fa fa-trash"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- Right Column: Upload designs & catalog display -->
        <div class="col-lg-8">
            <!-- Bulk Upload designs -->
            <form class="card__wrapper p-20 mb-20" action="{{ route('admin.print-designs.store-design') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="mb-15">Upload Print Designs (Support Bulk Upload)</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label>Select Category</label></div>
                            <div class="form__input">
                                <select name="category_id" class="form-control">
                                    <option value="">Uncategorized</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label>Choose Files <span>*</span></label></div>
                            <div class="form__input">
                                <input type="file" name="images[]" class="form-control" accept="image/*" multiple required />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-15">
                    <button type="submit" class="btn btn-primary">Upload Design(s)</button>
                </div>
            </form>

            <!-- Catalog Display -->
            <div class="card__wrapper p-20">
                <!-- Filters Bar -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-3">
                    <h5 class="mb-0">All Designs ({{ $designs->total() }})</h5>
                    
                    <form action="{{ route('admin.print-designs.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
                        <select name="category_id" class="form-control form-control-sm" style="width: 150px;" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" class="form-control form-control-sm" style="width: 200px;" placeholder="Search designs..." value="{{ request('search') }}" />
                        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    </form>
                </div>

                @if($designs->isEmpty())
                    <div class="text-center py-5">
                        <p class="text-muted">No designs found.</p>
                    </div>
                @else
                    <div class="design-grid mb-20">
                        @foreach($designs as $design)
                            <div class="design-card">
                                <div class="design-img-wrapper">
                                    <img class="design-img" src="{{ $design->image_url }}" alt="{{ $design->title }}">
                                </div>
                                <div class="design-info">
                                    <div>
                                        <div class="design-title" title="{{ $design->title }}">{{ $design->title }}</div>
                                        <div class="design-cat">{{ $design->category->name ?? 'Uncategorized' }}</div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-2">
                                        <form action="{{ route('admin.print-designs.destroy-design', $design->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this design?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2"><i class="fa fa-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $designs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
