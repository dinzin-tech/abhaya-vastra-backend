@forelse($coupons as $coupon)
    <tr>
        <!-- Sr. No. -->
        <td>{{ (($coupons->currentPage() - 1) * $coupons->perPage()) + $loop->iteration }}</td>

        <!-- Code -->
        <td>{{ $coupon->code }}</td>

        <!-- Type -->
        <td class="text-capitalize">{{ $coupon->type }}</td>

        <!-- Value -->
        <td>
            @if($coupon->type === 'percentage')
                {{ $coupon->value }}%
            @else
                ₹{{ number_format($coupon->value, 2) }}
            @endif
        </td>

        <!-- Type -->
        <td class="text-capitalize">{{ $coupon->min_cart_amount  }}</td>

        <!-- Used / Limit -->
        <td>{{ $coupon->used_count }}/{{ $coupon->usage_limit ?? '∞' }}</td>

        <!-- Valid From -->
        <td>{{ $coupon->expires_at  ? date('d M Y', strtotime($coupon->expires_at )) : '-' }}</td>

    
        <!-- Status -->
        <td>
            @if($coupon->status  == true)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-danger">Inactive</span>
            @endif
        </td>

        <!-- Actions -->
        <td>
            <div class="d-flex align-items-center justify-content-start gap-10">
                <!-- Assign to Users -->
                <button class="table__icon" onclick="openAssignModal({{ $coupon->id }}, '{{ $coupon->code }}')"
                    title="Assign to Users">
                    <i class="fa-solid fa-user-plus"></i>
                </button>
                <!-- Edit -->
                <a href="{{ route('coupons.edit', $coupon->id) }}" class="table__icon edit">
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
                <!-- Delete -->
                <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $coupon->id }}', $(this))">
                    <i class="fa-regular fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center">No Coupons Found!</td>
    </tr>
@endforelse