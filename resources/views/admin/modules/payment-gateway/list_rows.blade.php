@forelse($items as $item)
<tr>
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
    <td>{{ $item->gateway_name }}</td>
    <td>{{ $item->currency }}</td>
    <td>{{ $item->api_key }}</td>
    <td>{{ $item->api_secret }}</td>
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <a type="button" class="table__icon edit" href="{{ route('payment-gateway.edit', $item->id) }}">
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
    <td colspan="6" align="center">No Payment Gateways Found!</td>
</tr>
@endforelse
