@foreach($items as $item)
<tr>
    <td>{{ $item->id }}</td>
    <td>
        @if($item->wallet && $item->wallet->user)
            <strong>{{ $item->wallet->user->name }}</strong><br>
            <small class="text-muted">{{ $item->wallet->user->email }}</small>
        @else
            <span class="text-muted">N/A</span>
        @endif
    </td>
    <td>
        @if($item->type == 'credit')
            <span class="badge bg-success">
                <i class="fa fa-arrow-up"></i> Credit
            </span>
        @else
            <span class="badge bg-danger">
                <i class="fa fa-arrow-down"></i> Debit
            </span>
        @endif
    </td>
    <td>
        <strong>{{ $item->points }}</strong> points
        <br>
        <small class="text-muted">(₹{{ number_format($item->points * 1, 2) }})</small>
    </td>
    <td>
        @if($item->status == 'pending')
            <span class="badge bg-warning">Pending</span>
        @elseif($item->status == 'completed')
            <span class="badge bg-success">Completed</span>
        @else
            <span class="badge bg-secondary">Reversed</span>
        @endif
    </td>
    <td>{{ $item->description ?? '-' }}</td>
    <td>
        <span class="badge bg-info">{{ $item->reference ?? '-' }}</span>
    </td>
    <td>
        {{ $item->created_at->format('d M Y') }}<br>
        <small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
    </td>
    <td>
        <div class="btn-group" role="group">
            @if($item->status == 'pending' && $item->type == 'credit')
                <button type="button" class="btn btn-sm btn-success approve-transaction" 
                    data-id="{{ $item->id }}" title="Approve">
                    <i class="fa fa-check"></i>
                </button>
            @endif
            
            @if($item->status == 'pending')
                <button type="button" class="btn btn-sm btn-danger reverse-transaction" 
                    data-id="{{ $item->id }}" title="Reverse">
                    <i class="fa fa-times"></i>
                </button>
            @endif
            
            @if($item->status != 'pending')
                <span class="badge bg-secondary">No Action</span>
            @endif
        </div>
    </td>
</tr>
@endforeach

@if($items->isEmpty())
<tr>
    <td colspan="9" class="text-center text-muted py-4">
        <i class="fa fa-inbox fa-3x mb-3"></i>
        <p>No reward transactions found</p>
    </td>
</tr>
@endif
