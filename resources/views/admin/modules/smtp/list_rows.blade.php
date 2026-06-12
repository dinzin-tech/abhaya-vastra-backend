@forelse($items as $item)
<tr>
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
    <td>{{ $item->mailer }}</td>
    <td>{{ $item->host }}</td>
    <td>{{ $item->port }}</td>
    <td>{{ $item->username }}</td>
    <td>{{ strtoupper($item->encryption ?? '-') }}</td>
    <td>{{ $item->from_address ?? '-' }}</td>
    <td>{{ $item->from_name ?? '-' }}</td>

    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <a type="button" class="table__icon edit" href="{{ route('smtp.edit', $item->id) }}">
                <i class="fa-regular fa-eye"></i>
            </a>
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))">
                <i class="fa-regular fa-trash"></i>
            </button>
            <a type="button" class="table__icon" href="{{ route('smtp-settings.test', $item->id) }}">
                <i class="fa-regular fa-paper-plane"></i>
            </a>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" align="center">No SMTP Settings Found!</td>
</tr>
@endforelse
