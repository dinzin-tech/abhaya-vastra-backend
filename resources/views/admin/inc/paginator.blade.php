<div class="pull-left" style="float:left">
<p>Showing {{$paginator->firstItem()}} - {{$paginator->lastItem()}} of {{$paginator->total()}}</p>
</div>
<div class="pagination pull-right" style="float:right">
<?php
// config
$link_limit = 7; // maximum number of links (a little bit inaccurate, but will be ok for now)
?>
<ul class="pagination">
    @if ($paginator->lastPage() > 1)
    <li class="page-item{{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}">
        <a class="page-link" onclick="paginationURL(1)" data-url="{{ $paginator->url(1) }}" href="javascript:void(0)"
            aria-disabled="{{ ($paginator->currentPage() == 1) ? 'true' : 'false' }}" aria-label="Previous">
            <span aria-hidden="{{ ($paginator->currentPage() == 1) ? 'true' : 'false' }}">
                First</span>
        </a>
    </li>
    <li class="page-item{{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}">
        <a class="page-link" onclick="paginationURL({{ $paginator->currentPage()-1 }})"
            data-url="{{ $paginator->url($paginator->currentPage()-1) }}" href="javascript:void(0)"
            aria-disabled="{{ ($paginator->currentPage() == 1) ? 'true' : 'false' }}" aria-label="Previous">
            <span aria-hidden="{{ ($paginator->currentPage() == 1) ? 'true' : 'false' }}">
                <</span> </a> </li> @for ($i=1; $i <=$paginator->lastPage(); $i++)
                    <?php
            $half_total_links = floor($link_limit / 2);
            $from = $paginator->currentPage() - $half_total_links;
            $to = $paginator->currentPage() + $half_total_links;
            if ($paginator->currentPage() < $half_total_links) {
               $to += $half_total_links - $paginator->currentPage();
            }
            if ($paginator->lastPage() - $paginator->currentPage() < $half_total_links) {
                $from -= $half_total_links - ($paginator->lastPage() - $paginator->currentPage()) - 1;
            }
            ?>
                    @if ($from < $i && $i < $to) <li
                        class="page-item{{ ($paginator->currentPage() == $i) ? ' active' : '' }}">
                        <a class="page-link" onclick="paginationURL({{ $i }})" data-url="{{ $paginator->url($i) }}"
                            href="javascript:void(0)">{{ $i }}</a>
    </li>
    @endif
    @endfor
    <li class="page-item{{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' }}">
        <a class="page-link" onclick="paginationURL({{ $paginator->currentPage()+1 }})"
            data-url="{{ $paginator->url($paginator->currentPage()+1) }}" href="javascript:void(0)"
            aria-disabled="{{ ($paginator->currentPage() == $paginator->lastPage()) ? 'true' : 'false' }}"
            aria-label="Next">
            <span aria-hidden="{{ ($paginator->currentPage() == $paginator->lastPage()) ? 'true' : 'false' }}">></span>
        </a>
    </li>
    <li class="page-item{{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' }}">
        <a class="page-link" onclick="paginationURL({{ $paginator->lastPage() }})"
            data-url="{{ $paginator->url($paginator->lastPage()) }}" href="javascript:void(0)"
            aria-disabled="{{ ($paginator->currentPage() == $paginator->lastPage()) ? 'true' : 'false' }}"
            aria-label="Next">
            <span
                aria-hidden="{{ ($paginator->currentPage() == $paginator->lastPage()) ? 'true' : 'false' }}">Last</span>
        </a>
    </li>
    @endif
</ul>
</div>