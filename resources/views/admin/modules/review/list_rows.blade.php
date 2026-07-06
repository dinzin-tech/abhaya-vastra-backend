@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td style="font-weight:700;color:#94a3b8;">
        {{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}
    </td>

    <!-- Reviewer Image -->
    <td>
        @if($item->image)
            <img class="reviewer-thumb" src="{{ asset('storage/' . $item->image) }}" alt="Reviewer">
        @else
            <div class="reviewer-thumb" style="background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-weight:700; font-size:0.95rem;">
                {{ strtoupper(substr($item->name ?? 'U', 0, 1)) }}
            </div>
        @endif
    </td>

    <!-- Reviewer Name -->
    <td style="font-weight:700;color:#1e293b;font-size:.9rem;">
        {{ $item->name ?? '-' }}
    </td>

    <!-- Product Link -->
    <td>
        @if($item->product)
            <span style="background:#eef2ff;color:#6366f1;border-radius:6px;padding:4px 10px;font-size:.78rem;font-weight:600;">
                {{ $item->product->name }}
            </span>
        @else
            <span style="background:#f1f5f9;color:#94a3b8;border-radius:6px;padding:4px 10px;font-size:.78rem;font-weight:600;">
                General Testimonial
            </span>
        @endif
    </td>

    <!-- Rating -->
    <td style="white-space:nowrap;">
        @for($i = 1; $i <= 5; $i++)
            @if($i <= $item->rating)
                <i class="fa-solid fa-star text-warning" style="font-size: 0.8rem;"></i>
            @else
                <i class="fa-regular fa-star text-muted" style="font-size: 0.8rem; opacity:0.5;"></i>
            @endif
        @endfor
    </td>

    <!-- Review Text -->
    <td style="color:#475569;font-size:0.85rem;max-width:320px;word-wrap:break-word;">
        {{ Str::limit($item->review, 80) }}
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center gap-2">
            <!-- View / Edit -->
            <a href="{{ route('reviews.edit', $item->id) }}" class="table__icon edit" title="View / Edit">
                <i class="fa-regular fa-eye"></i>
            </a>
            <!-- Delete -->
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))" title="Delete">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" style="text-align:center;padding:50px 0;color:#94a3b8;">
        <div style="font-size:2rem;margin-bottom:10px;">⭐</div>
        <div style="font-weight:700;font-size:.95rem;color:#64748b;">No Reviews Found</div>
    </td>
</tr>
@endforelse
