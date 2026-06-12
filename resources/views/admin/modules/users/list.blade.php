@extends('admin.layouts.app')

@push('meta')
<title>Users | {{ config('app.name') }}</title>
<meta content="Users" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Search Filters -->
        <div class="col-xxl-12">
            <div class="row">
                <div class="col-xxl-3">
                    <div class="card__wrapper">
                        <div class="from__input-box">
                            <div class="form__input">
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ $name ?? '' }}" placeholder="Name">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3">
                    <div class="card__wrapper">
                        <div class="from__input-box">
                            <div class="form__input">
                                <input type="text" class="form-control" id="email" name="email"
                                    value="{{ $email ?? '' }}" placeholder="Email">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3">
                    <div class="card__wrapper">
                        <div class="from__input-box">
                            <div class="form__input">
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ $phone ?? '' }}" placeholder="Phone">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3">
                    <div class="card__wrapper">
                        <div class="d-flex align-items-center justify-content-between gap-15">
                            <button type="button" class="btn btn-secondary w-100" onclick="recordList()">Search</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-xxl-12 mt-3">
            <div class="card__wrapper">
                <div class="mb-10">
                    <div class="row align-items-center">
                        <div class="col-xl-8 order-2 order-xl-1">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <select class="form-control" name="offset" id="offset" onchange="recordList()">
                                        <option value="10" {{ $offset == '10' ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ $offset == '25' ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ $offset == '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ $offset == '100' ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table__wrapper table-responsive">
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{ route('users.list') }}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>Sr. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Zip</th>
                            </tr>
                        </thead>
                        <tbody class="table__body"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-right"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('appendJs')
<script src="{{ asset('assets/js/vendor/jquery-3.7.0.js') }}"></script>

<!-- Plugins (order matters!) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('assets/js/vendor/jquery.barrating.js') }}"></script>


<!-- Other vendor scripts -->
<script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/dropzone.js') }}"></script>
<script src="{{ asset('assets/js/plugins/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script>
    var listUrl = "{{ route('users.list') }}";

    $(document).ready(function() {
        recordList();
    });

    function recordList() {
        let name = $('#name').val();
        let email = $('#email').val();
        let phone = $('#phone').val();
        let offset = $('#offset').val();

        $.ajax({
            url: listUrl,
            type: 'GET',
            data: { name, email, phone, offset },
            success: function(response) {
                if (response.success) {
                    let rows = '';
                    response.data.forEach((user, index) => {
                        rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${user.name ?? '-'}</td>
                                <td>${user.email ?? '-'}</td>
                                <td>${user.phone ?? '-'}</td>
                                <td>${user.address ?? '-'}</td>
                                <td>${user.city ?? '-'}</td>
                                <td>${user.zip ?? '-'}</td>
                            </tr>
                        `;
                    });
                    $('#record-list tbody').html(rows);
                }
            }
        });
    }
</script>
@endpush
