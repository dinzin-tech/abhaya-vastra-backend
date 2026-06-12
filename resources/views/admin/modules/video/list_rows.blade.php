@forelse($items as $item)
<tr>
    <!-- Serial No -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Video Title -->
    <td>{{ $item->title ?? 'Untitled' }}</td>

    <!-- Short Video Preview -->
    <td>
        @if($item->video)
            <video width="120" height="70" controls>
                <source src="{{ asset('storage/' . $item->video) }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        @else
            <span>No Video</span>
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <a type="button" class="table__icon edit" href="{{ route('video.edit', $item->id) }}">
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
    <td colspan="4" align="center">No Short Video Found!</td>
</tr>
@endforelse
