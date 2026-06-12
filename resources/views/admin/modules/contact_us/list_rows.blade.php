@forelse($items as $item)
<tr>
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
    <td>{{ $item->email ?? '-' }}</td>
    <td>{{ $item->phone ?? '-' }}</td>
    <td>{{ $item->address ?? '-' }}</td>
    <td>
        <div class="d-flex align-items-center gap-10">
            <a class="table__icon edit" href="{{ route('contact-us.edit', $item->id) }}">
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
    <td colspan="5" align="center">No Contact Details Found!</td>
</tr>
@endforelse
