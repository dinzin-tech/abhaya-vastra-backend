@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- User Details -->
    <td>{{ $item->name }}</td>
    <td>{{ $item->email }}</td>
    <td>{{ $item->phone }}</td>
    <td>{{ $item->address }}</td>
</tr>
@empty
<tr>
    <td colspan="5" align="center" class="text-muted">No Users Found!</td>
</tr>
@endforelse
