@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Product Name -->
    <td>{{ $item->name }}</td>

    <!-- Category Name -->
    <td>{{ $item->category->name ?? '-' }}</td>
    <td>{{ $item->gender }}</td>

    <!-- Price -->
    <td>{{ number_format($item->price, 2) }}</td>

    <!-- Discount -->
    <td>{{ $item->discount ? number_format($item->discount, 2) : '-' }}</td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- View / Edit -->
            <a type="button" class="table__icon edit" href="{{ route('products.edit', $item->id) }}">
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
    <td colspan="6" align="center">No Products Found!</td>
</tr>
@endforelse