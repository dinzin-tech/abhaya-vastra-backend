@extends('admin.layouts.app')

@section('title', 'Lookbook Banners')

@section('content')
<div class="page-header">
    <div class="page-header-content header-elements-md-inline">
        <div class="page-title d-flex">
            <h4>
                <i class="icon-arrow-left52 mr-2"></i>
                <span class="font-weight-semibold">Lookbook</span> - Manage Banners
            </h4>
        </div>
        <div class="header-elements d-none py-0 mb-3 mb-md-0">
            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">
                    <i class="icon-home2 mr-2"></i> Home
                </a>
                <span class="breadcrumb-item active">Lookbook</span>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="content-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h5 class="card-title">Editorial Lookbook Banners</h5>
                    <div class="header-elements">
                        <a href="{{ route('lookbook.create') }}" class="btn btn-primary btn-sm">
                            <i class="icon-plus3 mr-2"></i> Add New Lookbook
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form action="" method="GET" id="search-form">
                                <div class="input-group">
                                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control"
                                        placeholder="Search by title..." />
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="icon-search4"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Subtitle</th>
                                    <th>Size</th>
                                    <th>Link</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="list_rows">
                                @include('admin.modules.lookbook.list_rows', ['items' => \App\Models\Lookbook::orderBy('sort_order','asc')->paginate(10)])
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination_area"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
