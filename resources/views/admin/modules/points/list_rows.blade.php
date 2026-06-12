@forelse($points as $point)
    <tr>
        <!-- Sr. No. -->
        <td>{{ (($points->currentPage() - 1) * $points->perPage()) + $loop->iteration }}</td>

        <!-- Min Amount -->
        <td>₹{{ number_format($point->min_amount, 2) }}</td>

        <!-- Max Amount -->
        <td>
            @if($point->max_amount)
                ₹{{ number_format($point->max_amount, 2) }}
            @else
                ∞
            @endif
        </td>

        <!-- Points -->
        <td>{{ $point->points }}</td>

        <!-- Status -->
        <td>
            @if($point->status)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-danger">Inactive</span>
            @endif
        </td>

        <!-- Actions -->
        <td>
            <div class="d-flex align-items-center justify-content-start gap-10">
                <!-- Edit -->
                <a href="{{ route('points.edit', $point->id) }}" class="table__icon edit">
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
                <!-- Delete -->
                <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $point->id }}', $(this))">
                    <i class="fa-regular fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center">No Points Configuration Found!</td>
    </tr>
@endforelse
