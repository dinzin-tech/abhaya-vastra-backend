@forelse($items as $item)
<tr>
    <!-- Serial No -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Gallery Image -->
    <td>
        @if($item->image)
            <img src="{{ asset('storage/' . $item->image) }}" alt="Gallery" style="height:60px;">
        @else
            <span>No Image</span>
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <a type="button" class="table__icon edit" href="{{ route('gallery.edit', $item->id) }}">
                <i class="fa-regular fa-eye"></i>
            </a>
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="3" align="center">No Gallery Found!</td>
</tr>
@endforelse
