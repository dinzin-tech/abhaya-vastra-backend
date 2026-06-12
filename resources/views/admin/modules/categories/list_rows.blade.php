@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Category Name -->
    <td>{{ $item->name }}</td>

    <!-- Gender -->
    <!-- <td>{{ ucfirst($item->gender) }}</td> -->

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- Edit / View -->
            <a type="button" class="table__icon edit" href="{{ route('categories.edit', $item->id) }}">
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
    <td colspan="4" align="center">No Categories Found!</td>
</tr>
@endforelse