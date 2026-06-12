

@extends('admin.layouts.app')

@push('meta')
<title>Coupons | {{ config('app.name') }}</title>
<meta content="Coupons" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Coupons</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="row mb-3">
            <div class="col-12 text-end">
                <a class="btn btn-primary" href="{{ route('coupons.create') }}">Add Coupon</a>
            </div>
        </div>

        <div class="col-xxl-12">
            <div class="card__wrapper">
                <div class="table__wrapper table-responsive">
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{ route('coupons.list') }}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>Sr. No.</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Min Cart</th>
                                <th>Used / Limit</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table__body">
                            <!-- AJAX rows will be injected here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-right"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Coupon Modal -->
<div class="modal fade" id="assignCouponModal" tabindex="-1" aria-labelledby="assignCouponModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white p-4 rounded-top">
                <div class="d-flex flex-column w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="fa-solid fa-ticket-alt me-2"></i>Assign Coupon to Users
                        </h5>
                        <button type="button" class="btn-close btn-close-dark btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mt-3 d-flex align-items-center">
                        <span class="badge bg-white text-primary fs-6 fw-bold px-3 py-2 shadow-sm">
                            <i class="fa-solid fa-tag me-2"></i>
                            <span id="couponCodeDisplay" class="text-uppercase"></span>
                        </span>
                    </div>
                </div>
            </div>

            <form id="assignCouponForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="couponId" name="coupon_id">
                    
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        <div>Select users to assign this coupon code. They will receive a notification with the coupon details.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-2">Select Users</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                <i class="fa-solid fa-check-double me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllBtn">
                                <i class="fa-solid fa-times me-1"></i>Clear All
                            </button>
                        </div>

                        <select id="userDropdown" class="form-select">
                            <option value="">-- Choose a user --</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}">
                                    {{ $user->name }} - {{ $user->email }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Selected Users -->
                        <div class="mt-3" id="selectedUsersArea" style="display: none;">
                            <label class="form-label fw-semibold small mb-2">
                                Selected Users (<span id="selectedCount">0</span>)
                            </label>
                            <div id="selectedUsersList"
                                 style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background: #f9fafb;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Custom Message 
                            <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <textarea name="message_body" id="messageBody" class="form-control" rows="3"
                                  placeholder="Write a personalized message..."
                                  style="resize: none;"></textarea>
                    </div>

                    <div class="alert alert-info" style="border-radius: 8px;">
                        <i class="fa-solid fa-envelope me-2"></i>
                        <span>The coupon will be sent via email to all selected users.</span>
                    </div>
                </div>

                <div class="modal-footer  p-4 rounded-bottom">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i>Send Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@stop

<style>
.selected-user-badge {
    display: inline-block;
    background: #e5e7eb;
    border-radius: 20px;
    padding: 5px 12px;
    margin: 3px;
    font-size: 0.9rem;
}
.selected-user-badge .remove-user {
    color: #dc3545;
    cursor: pointer;
    margin-left: 8px;
    font-weight: bold;
}

</style>

@push('appendJs')
<script src="{{ asset('assets/js/vendor/jquery-3.7.0.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('assets/js/vendor/jquery.barrating.js') }}"></script>
<script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/dropzone.js') }}"></script>
<script src="{{ asset('assets/js/plugins/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>

<script type="text/javascript">
    var searchUrl = "{{ route('coupons.list') }}";
    var listUrl = "{{ route('coupons.index') }}";
    var deleteUrl = "{{ route('coupons.delete') }}";
    var tblObj = $("#record-list");

    $(document).ready(function () {
        if ($('.select2').length) { $('.select2').select2(); }
        if ($('.datepicker').length) { $('.datepicker').flatpickr({ dateFormat: 'Y-m-d' }); }
        if ($('.rating').length) { $('.rating').barrating({ theme: 'fontawesome-stars' }); }

        @if(session('success'))
            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
            toastr.error("{{ session('error') }}");
        @endif

        recordList();

        // ===============================
        // Select multiple users section
        // ===============================

        let selectedUsers = [];

        $('#userDropdown').on('change', function () {
            const userId = $(this).val();
            const name = $('#userDropdown option:selected').data('name');
            const email = $('#userDropdown option:selected').data('email');

            if (userId && !selectedUsers.find(u => u.id === userId)) {
                selectedUsers.push({ id: userId, name, email });
                updateSelectedUsersList();
            }

            $(this).val('');
        });

        $('#selectAllBtn').on('click', function () {
            selectedUsers = [];
            $('#userDropdown option').each(function () {
                const userId = $(this).val();
                const name = $(this).data('name');
                const email = $(this).data('email');
                if (userId) selectedUsers.push({ id: userId, name, email });
            });
            updateSelectedUsersList();
        });

        $('#clearAllBtn').on('click', function () {
            selectedUsers = [];
            updateSelectedUsersList();
        });

        function updateSelectedUsersList() {
            const container = $('#selectedUsersList');
            const area = $('#selectedUsersArea');
            const count = $('#selectedCount');

            if (selectedUsers.length === 0) {
                area.hide();
                container.empty();
                count.text(0);
                return;
            }

            area.show();
            count.text(selectedUsers.length);

            let html = '';
            selectedUsers.forEach(user => {
                html += `
                    <div class="d-inline-flex align-items-center bg-white border rounded-pill px-3 py-1 m-1">
                        <span class="me-2">${user.name}</span>
                        <button type="button" class="btn-close btn-close-sm p-0 remove-user" style="font-size: 0.6rem;" data-user-id="${user.id}"></button>
                    </div>`;
            });

            container.html(html);

            // Use event delegation for dynamically added elements
            $(document).off('click', '.remove-user').on('click', '.remove-user', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const userId = $(this).data('user-id');
                selectedUsers = selectedUsers.filter(u => u.id != userId);
                updateSelectedUsersList();
                
                return false;
            });
        }

        window.openAssignModal = function (couponId, couponCode) {
            $('#couponId').val(couponId);
            $('#couponCodeDisplay').text(couponCode);
            $('#messageBody').val('');
            selectedUsers = [];
            updateSelectedUsersList();
            $('#assignCouponModal').modal('show');
        };

        $('#assignCouponForm').on('submit', function (e) {
            e.preventDefault();

            if (selectedUsers.length === 0) {
                toastr.error('Please select at least one user');
                return;
            }

            $.ajax({
                url: "{{ route('coupons.assign') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    coupon_id: $('#couponId').val(),
                    user_ids: selectedUsers.map(u => u.id),
                    message_body: $('#messageBody').val()
                },
                success: function (res) {
                    toastr.success(res.message);
                    $('#assignCouponModal').modal('hide');
                },
                error: function () {
                    toastr.error('Failed to assign coupon. Please try again.');
                }
            });
        });
    }); // ✅ properly closed document.ready
</script>

<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush



