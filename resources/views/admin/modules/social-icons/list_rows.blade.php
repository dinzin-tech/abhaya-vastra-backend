@forelse($items as $item)
<tr>
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
    <td>{{ $item->title }}</td>
    <td>
        <i class="{{ $item->icon }}"></i> 
        <span class="ms-2">{{ $item->icon }}</span>
    </td>
    <td>
        <a href="{{ $item->url }}" target="_blank">{{ $item->url }}</a>
    </td>

    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <a type="button" class="table__icon edit" href="{{ route('social.edit', $item->id) }}">
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
    <td colspan="5" align="center">No Social Links Found!</td>
</tr>
@endforelse
