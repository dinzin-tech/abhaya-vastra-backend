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
        <span class="color-swatch-circle" style="background: {{ ($item->color && str_starts_with($item->color->color, '#')) ? $item->color->color : '#e2e8f0' }}"></span>
        <span style="font-weight:600;color:#475569;">{{ ucfirst($item->color->color ?? '-') }}</span>
    </td>

    <!-- Size -->
    <td>
        <span class="variant-size-badge">{{ $item->size ?? '-' }}</span>
    </td>

    <!-- Price -->
    <td style="font-weight:700;color:#1e293b;">
        ₹{{ number_format($item->price, 2) }}
        @if($item->discount > 0)
            <div style="font-size:0.75rem;color:#16a34a;">-{{ number_format($item->discount, 1) }}%</div>
        @endif
    </td>

    <!-- Stock -->
    <td>
        @if($item->stock == 0)
            <span style="background:#fee2e2;color:#ef4444;border-radius:6px;padding:4px 10px;font-size:.78rem;font-weight:700;">Out of Stock</span>
        @elseif($item->stock < 5)
            <span style="background:#fffbeb;color:#d97706;border-radius:6px;padding:4px 10px;font-size:.78rem;font-weight:700;">Low Stock ({{ $item->stock }})</span>
        @else
            <span style="background:#f0fdf4;color:#16a34a;border-radius:6px;padding:4px 10px;font-size:.78rem;font-weight:700;">In Stock ({{ $item->stock }})</span>
        @endif
    </td>

    <!-- Weight -->
    <td style="color:#64748b;font-weight:600;">
        {{ $item->weight ? $item->weight . ' kg' : '—' }}
    </td>

    <!-- Images -->
    <td>
        @php
            $variantImages = $item->color->images ?? [];
            $displayImages = array_slice($variantImages, 0, 4);
        @endphp

        @if(!empty($displayImages))
            @foreach($displayImages as $img)
                <img class="variant-thumb" src="{{ asset('storage/'.$img) }}" alt="Variant Image">
            @endforeach
        @else
            <span style="color:#94a3b8;font-size:.8rem;">—</span>
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center gap-2">
            <!-- View / Edit -->
            <a href="{{ route('product-variants.edit', $item->id) }}" class="table__icon edit" title="View / Edit">
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
    <td colspan="9" style="text-align:center;padding:50px 0;color:#94a3b8;">
        <div style="font-size:2rem;margin-bottom:10px;">📦</div>
        <div style="font-weight:700;font-size:.95rem;color:#64748b;">No Variants Found</div>
    </td>
</tr>
@endforelse
