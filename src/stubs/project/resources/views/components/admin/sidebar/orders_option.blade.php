@can('view', \App\Models\Cart::class)
    {{-- ORDERS --}}
    <li class="nav-item @if(request()->route()->named("{$whereIam}.orders.*")) active @endif">
        <a class="nav-link " href="{{route("{$whereIam}.orders.index")}}">
            <i class="fa fa-fw fa-cubes mr-1"></i>
            <span>@lang("admin/orders/orders.sidebar")</span>
        </a>
    </li>
@endcan
