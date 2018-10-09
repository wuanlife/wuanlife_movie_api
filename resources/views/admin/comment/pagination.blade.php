@php
    $limit = env('LIMIT');
    $total_page = ceil($total/ $limit);
@endphp
@if($total > $limit)
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item {{$page == 1 ? 'disabled' : ''}}">
                <a class="page-link"
                   href="{{route($name, ['offset' => ($page - 2) * $limit, 'limit' => $limit])}}"
                   aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            @for($i = 1; $i <= $total_page; $i++)
                <li class="page-item {{$page == $i ? 'active' : ''}}"><a class="page-link"
                                                                         href="{{route($name, ['offset' => ($i - 1) * $limit, 'limit' => $limit])}}">{{$i}}</a>
                </li>
            @endfor
            <li class="page-item {{$page == $total_page ? 'disabled' : ''}}">
                <a class="page-link"
                   href="{{route($name, ['offset' => $page * $limit, 'limit' => $limit])}}"
                   aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        </ul>
    </nav>
@endif