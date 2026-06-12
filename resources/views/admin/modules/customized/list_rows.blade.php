@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Title -->
    <td>{{ $item->title ?? '-' }}</td>

    <!-- Description -->
    <td>{{ Str::limit($item->description, 50) ?? '-' }}</td>

    <!-- Images -->
    <td>
        @if(!empty($item->images))
            @foreach(json_decode($item->images, true) as $img)
                <img src="{{ asset('storage/'.$img) }}" 
                     alt="Customize Image" 
                     width="50" height="50" 
                     style="object-fit:cover; margin-right:5px; border-radius:4px;">
            @endforeach
        @else
            -
        @endif
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- View / Edit -->
            <a type="button" class="table__icon edit" href="{{ route('customized.edit', $item->id) }}">
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
    <td colspan="5" align="center">No Customized Products Found!</td>
</tr>
@endforelse
