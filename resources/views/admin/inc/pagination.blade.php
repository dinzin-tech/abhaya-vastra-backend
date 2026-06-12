
    {{-- {!!$result->links()!!} --}}
    @if(count($result))
    {!!$result->links('admin.inc.paginator')!!}
    @endif