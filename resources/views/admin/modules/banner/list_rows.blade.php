@forelse($items as $item)
<tr>
    <!-- Serial No -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Banner Image -->
    <td>
        @if($item->image)
            @php
                $imgUrl = \Illuminate\Support\Str::startsWith($item->image, ['http://', 'https://']) ? $item->image : asset('storage/' . $item->image);
            @endphp
            <img src="{{ $imgUrl }}" alt="Banner" style="height:60px;object-fit:cover;border-radius:4px;">
        @else
            <span>No Image</span>
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <a type="button" class="table__icon edit" href="{{ route('banner.edit', $item->id) }}">
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
    <td colspan="3" align="center">No Banner Found!</td>
</tr>
@endforelse
