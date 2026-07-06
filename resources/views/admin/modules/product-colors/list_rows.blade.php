@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td style="font-weight:700;color:#94a3b8;">
        {{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}
    </td>

    <!-- Product Name -->
    <td style="font-weight:700;color:#1e293b;font-size:.9rem;">
        {{ $item->product->name ?? '-' }}
    </td>

    <!-- Color -->
    <td>
        <span class="color-swatch-circle" style="background: {{ str_starts_with($item->color, '#') ? $item->color : '#e2e8f0' }}"></span>
        <span style="font-weight:600;color:#475569;">{{ ucfirst($item->color) }}</span>
    </td>

    <!-- Images -->
    <td>
        @if(!empty($item->images))
            @foreach($item->images as $img)
                <img class="color-image-thumb" src="{{ asset('storage/'.$img) }}" alt="Color Image">
            @endforeach
        @else
            <span style="color:#94a3b8;font-size:.8rem;">—</span>
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center gap-2">
            <!-- View / Edit -->
            <a href="{{ route('product-colors.edit', $item->id) }}" class="table__icon edit" title="View / Edit">
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
    <td colspan="5" style="text-align:center;padding:50px 0;color:#94a3b8;">
        <div style="font-size:2rem;margin-bottom:10px;">🎨</div>
        <div style="font-weight:700;font-size:.95rem;color:#64748b;">No Colors Found</div>
    </td>
</tr>
@endforelse
