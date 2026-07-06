@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td style="font-weight:700;color:#94a3b8;">
        {{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}
    </td>

    <!-- Title -->
    <td>
        <div style="font-weight:700;color:#1e293b;font-size:.9rem;">{{ $item->title ?? '-' }}</div>
        <div style="font-size:.72rem;color:#94a3b8;font-family:monospace;margin-top:2px;">/{{ $item->slug ?? '-' }}</div>
    </td>

    <!-- Description -->
    <td style="color:#475569;font-size:0.85rem;max-width:300px;word-wrap:break-word;">
        {{ Str::limit($item->description, 70) ?? '-' }}
    </td>

    <!-- Images -->
    <td>
        @if(!empty($item->images))
            @foreach(is_array($item->images) ? $item->images : json_decode($item->images, true) as $img)
                <img class="custom-thumb" src="{{ asset('storage/'.$img) }}" alt="Customized Image">
            @endforeach
        @else
            <span style="color:#94a3b8;font-size:.8rem;">—</span>
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center gap-2">
            <!-- View / Edit -->
            <a href="{{ route('customized.edit', $item->id) }}" class="table__icon edit" title="View / Edit">
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
        <div style="font-size:2rem;margin-bottom:10px;">✨</div>
        <div style="font-weight:700;font-size:.95rem;color:#64748b;">No Customized Designs Found</div>
    </td>
</tr>
@endforelse
