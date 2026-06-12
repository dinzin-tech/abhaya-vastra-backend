@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Product Name -->
    <td>{{ $item->product->name ?? '-' }}</td>

    <!-- Color -->
    {{-- FIX 1: Access the color name via the 'color' relationship. $item->color is the ProductColor model. --}}
    <td>{{ $item->color->color ?? '-' }}</td>

    <!-- Size -->
    <td>{{ $item->size ?? '-' }}</td>

    <!-- Price -->
    <td>₹{{ number_format($item->price, 2) }}</td>

    <!-- Stock -->
    <td>{{ $item->stock }}</td>
    <td>{{ $item->weight }}</td>


    <!-- Images -->
    <td>
        {{-- FIX 2: Access images from the related ProductColor model ($item->color).
             Since we enabled casting in the ProductColor model, $item->color->images should be a PHP array.
        --}}
        @php
            $variantImages = $item->color->images ?? [];
            // Show max 4 images for cleanliness
            $displayImages = array_slice($variantImages, 0, 4);
        @endphp

        @if(!empty($displayImages))
            @foreach($displayImages as $img)
                <img src="{{ asset('storage/'.$img) }}" 
                     alt="{{ $item->product->name ?? 'Variant' }} Image" 
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
            <a type="button" class="table__icon edit" href="{{ route('product-variants.edit', $item->id) }}">
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
    <td colspan="8" align="center">No Product Variants Found!</td>
</tr>
@endforelse
