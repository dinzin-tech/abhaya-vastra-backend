@forelse($items as $item)
<tr>
    {{-- Serial Number --}}
    <td style="font-weight:700;color:#94a3b8;">
        {{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}
    </td>

    {{-- Thumbnail --}}
    <td>
        @if($item->main_image)
            <img src="{{ asset('storage/'.$item->main_image) }}"
                 class="product-thumb" alt="{{ $item->name }}">
        @else
            <div class="product-thumb-placeholder">📦</div>
        @endif
    </td>

    {{-- Product Name --}}
    <td>
        <div style="font-weight:700;color:#1e293b;font-size:.9rem;">{{ $item->name }}</div>
        <div style="font-size:.72rem;color:#94a3b8;font-family:monospace;margin-top:2px;">/{{ $item->slug }}</div>
    </td>

    {{-- Category --}}
    <td>
        <span style="background:#f1f5f9;color:#475569;border-radius:6px;padding:4px 10px;font-size:.78rem;font-weight:600;">
            {{ $item->category->name ?? '—' }}
        </span>
    </td>

    {{-- Gender Badge --}}
    <td>
        <span class="badge-gender badge-{{ $item->gender }}">
            {{ ucfirst($item->gender) }}
        </span>
    </td>

    {{-- Flags --}}
    <td>
        <div style="display:flex;gap:4px;flex-wrap:wrap;">
            @if($item->best_seller)
                <span class="badge-flag badge-bestseller">⭐ Best Seller</span>
            @endif
            @if($item->is_featured)
                <span class="badge-flag badge-featured">🔥 Featured</span>
            @endif
            @if(!$item->best_seller && !$item->is_featured)
                <span style="color:#94a3b8;font-size:.75rem;">—</span>
            @endif
        </div>
    </td>

    {{-- Actions --}}
    <td>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('products.edit', $item->id) }}" class="table__icon edit" title="View / Edit">
                <i class="fa-regular fa-eye"></i>
            </a>
            <button class="removeBtn table__icon delete"
                onclick="deleteRecord('{{ $item->id }}', $(this))"
                title="Delete">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="7" style="text-align:center;padding:50px 0;color:#94a3b8;">
        <div style="font-size:2rem;margin-bottom:10px;">📦</div>
        <div style="font-weight:700;font-size:.95rem;color:#64748b;">No Products Found</div>
        <div style="font-size:.8rem;margin-top:4px;">Try adjusting your filters or <a href="{{ route('products.create') }}" style="color:#6366f1;font-weight:700;">add a new product</a>.</div>
    </td>
</tr>
@endforelse