@forelse($items as $item)
<tr>
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
    <td>{{ $item->title }}</td>

    <td>{{ \Illuminate\Support\Str::words(strip_tags($item->description), 20, '...') }}</td>

    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <a type="button" class="table__icon edit" href="{{ route('terms.edit', $item->id) }}">
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
    <td colspan="3" align="center">No Terms & Conditions Found!</td>
</tr>
@endforelse
