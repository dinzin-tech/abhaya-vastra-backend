@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Product Name -->
    <td>{{ $item->product->name ?? '-' }}</td>

    <!-- Color -->
    <td>{{ $item->color ?? '-' }}</td>

    <!-- Images -->
    <td>
        @if(!empty($item->images))
            @foreach($item->images as $img)
                <img src="{{ asset('storage/'.$img) }}" 
                     alt="Color Image" 
                     width="50" height="50" 
                     style="object-fit:cover; margin-right:5px; border-radius:4px;">
            @endforeach
        @else
            -
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- View / Edit -->
            <a type="button" class="table__icon edit" href="{{ route('product-colors.edit', $item->id) }}">
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
    <td colspan="5" align="center">No Product Colors Found!</td>
</tr>
@endforelse
