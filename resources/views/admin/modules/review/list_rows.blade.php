@forelse($items as $item)
<tr>
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Reviewer Image -->
    <td>
        @if($item->image)
            <img src="{{ asset('storage/' . $item->image) }}" alt="Reviewer Image" width="60" height="60" class="rounded-circle object-cover">
        @else
            <span class="text-muted">No Image</span>
        @endif
    </td>

    <!-- Reviewer Name -->
    <td>{{ $item->name ?? '-' }}</td>

    <!-- Rating -->
    <td>
        @for($i = 1; $i <= 5; $i++)
            @if($i <= $item->rating)
                <i class="fa-solid fa-star text-warning"></i>
            @else
                <i class="fa-regular fa-star text-muted"></i>
            @endif
        @endfor
    </td>

    <!-- Review Text -->
    <td style="max-width: 350px;">{{ Str::limit($item->review, 100) }}</td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- View / Edit -->
            <a type="button" class="table__icon edit" href="{{ route('reviews.edit', $item->id) }}">
                <i class="fa-regular fa-eye"></i>
            </a>

            <!-- Delete -->
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" align="center">No Reviews Found!</td>
</tr>
@endforelse
