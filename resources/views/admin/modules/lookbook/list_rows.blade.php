@forelse($items as $index => $item)
<tr>
    <td>{{ $index + 1 }}</td>
    <td>
        @if($item->image)
            @php
                $imgUrl = \Illuminate\Support\Str::startsWith($item->image, ['http://', 'https://']) ? $item->image : asset('storage/' . $item->image);
            @endphp
            <img src="{{ $imgUrl }}" alt="{{ $item->title }}"
                 style="width:80px;height:60px;object-fit:cover;border-radius:4px;" />
        @else
            <span class="text-muted">No image</span>
        @endif
    </td>
    <td><strong>{{ $item->title }}</strong></td>
    <td>{{ $item->subtitle ?? '—' }}</td>
    <td>
        <span class="badge badge-{{ $item->size === 'large' ? 'primary' : ($item->size === 'full' ? 'warning' : 'secondary') }}">
            {{ strtoupper($item->size) }}
        </span>
    </td>
    <td><a href="{{ $item->link }}" target="_blank">{{ $item->link }}</a></td>
    <td>
        @if($item->is_active)
            <span class="badge badge-success">Active</span>
        @else
            <span class="badge badge-danger">Inactive</span>
        @endif
    </td>
    <td>
        <a href="{{ route('lookbook.edit', $item->id) }}" class="btn btn-sm btn-info mr-1">
            <i class="icon-pencil3"></i> Edit
        </a>
        <button class="btn btn-sm btn-danger delete-lookbook" data-id="{{ $item->id }}">
            <i class="icon-trash"></i> Delete
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center text-muted py-4">No lookbook banners found. <a href="{{ route('lookbook.create') }}">Add one now</a>.</td>
</tr>
@endforelse

<script>
document.querySelectorAll('.delete-lookbook').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Are you sure you want to delete this lookbook banner?')) return;
        const id = this.dataset.id;
        fetch('{{ route("lookbook.delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id })
        }).then(r => r.json()).then(data => {
            if (data.message) location.reload();
        });
    });
});
</script>
