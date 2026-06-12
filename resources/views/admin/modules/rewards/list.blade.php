@extends('admin.layouts.app')

@push('meta')
    <title>Reward Transactions | {{ config('app.name') }}</title>
    <meta content="Reward Transactions" name="description" />
    <meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reward Transactions</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12">
            <div class="card__wrapper">
                <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <h4>Manage Reward Transactions</h4>
                    <a href="{{ route('reward-settings.index') }}" class="btn btn-info">
                        <i class="fa fa-cog"></i> Settings
                    </a>
                </div>

                <div class="table__wrapper table-responsive">
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{ route('rewards.list') }}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table__body">
                            <!-- AJAX rows will be injected here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('appendJs')
    <script src="{{ asset('assets/js/vendor/jquery-3.7.0.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>

    <script type="text/javascript">
        var searchUrl = "{{ route('rewards.list') }}";
        var listUrl = "{{ route('rewards.index') }}";
        var approveUrl = "{{ route('rewards.approve') }}";
        var reverseUrl = "{{ route('rewards.reverse') }}";
        var tblObj = $("#record-list");

        $(document).ready(function () {
            @if(session('success'))
                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
                toastr.success("{{ session('success') }}");
            @endif
            @if(session('error'))
                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
                toastr.error("{{ session('error') }}");
            @endif

            recordList();
        });

        // Approve transaction
        $(document).on('click', '.approve-transaction', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            
            Swal.fire({
                title: 'Approve Transaction?',
                text: "This will credit the points to user's wallet.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: approveUrl,
                        type: 'POST',
                        data: { 
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            recordList();
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error approving transaction');
                        }
                    });
                }
            });
        });

        // Reverse transaction
        $(document).on('click', '.reverse-transaction', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            
            Swal.fire({
                title: 'Reverse Transaction?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reverse it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: reverseUrl,
                        type: 'POST',
                        data: { 
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            recordList();
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error reversing transaction');
                        }
                    });
                }
            });
        });
    </script>

    <script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush
