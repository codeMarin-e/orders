<div class="table-responsive rounded ">
    <table class="table table-sm">
        <thead class="thead-light">
        <tr class="">
            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.id')</th>
            {{-- @HOOK_AFTER_ID_TH --}}

            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.order_id')</th>
            {{-- @HOOK_AFTER_ORDER_ID_TH--}}

            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.user')</th>
            {{-- @HOOK_AFTER_USER_TH --}}

            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.delivery')</th>
            {{-- @HOOK_AFTER_DELIVERY_TH --}}

            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.payment')</th>
            {{-- @HOOK_AFTER_PAYMENT_TH --}}

            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.confirmed_at')</th>
            {{-- @HOOK_AFTER_CONFIRMED_AT_TH --}}

            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.status')</th>
            {{-- @HOOK_AFTER_STATUS_TH --}}

            <th scope="col" class="text-center align-middle">@lang('admin/orders/orders.total')</th>
            {{-- @HOOK_AFTER_TOTAL_TH --}}

            @php $afterEditCount = 0; @endphp
            {{-- @HOOK_AFTER_FUNCTIONS_COLUMN_ADD_TH --}}

            <th scope="col" class="text-center align-middle" colspan="{{(2+$afterEditCount)}}">@lang('admin/orders/orders.functions')</th>
        </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            @php
                $orderEditUri = route("{$route_namespace}.orders.edit", $order);
            @endphp
            <tr data-id="{{$order->id}}">
                {{--    ID    --}}
                <td scope="row" class="text-center align-middle"><a href="{{ $orderEditUri }}"
                                                                    title="@lang('admin/orders/orders.edit')"
                    >{{ $order->id }}</a></td>
                {{-- @HOOK_AFTER_ID --}}

                {{--    ORDER ID    --}}
                <td scope="row" class="text-center align-middle"><a href="{{ $orderEditUri }}"
                                                                    title="@lang('admin/orders/orders.edit')"
                    >{{ $order->order_id }}</a></td>
                {{-- @HOOK_AFTER_ORDER_ID --}}

                {{--    USER    --}}
                <td class="text-center align-middle">
                    @if($order->user)
                        <a href="{{ route("{$route_namespace}.users.edit", $order->user) }}"
                           title="@lang('admin/orders/orders.user')"
                        >{{ \Illuminate\Support\Str::words($order->getFacturaAddress()->fullname, 12,'....') }}</a>
                    @else
                        {{ \Illuminate\Support\Str::words($order->getFacturaAddress()->fullname, 12,'....') }}
                    @endif
                </td>
                {{-- @HOOK_AFTER_USER --}}

                {{--    DELIVERY    --}}
                <td class="text-center align-middle">
                    @if($order->delivery)
                        @if($order->delivery->delivery)
                            <a href="{{ route("{$route_namespace}.deliveries.edit",  $order->delivery->delivery) }}"
                               title="@lang('admin/orders/orders.delivery')"
                            >{{ \Illuminate\Support\Str::words($order->delivery->aVar('name'), 12,'....') }}</a>
                        @else
                            {{ \Illuminate\Support\Str::words($order->delivery->aVar('name'), 12,'....') }}
                        @endif
                    @endif
                </td>
                {{-- @HOOK_AFTER_DELIVERY --}}

                {{--    PAYMENT    --}}
                <td class="text-center align-middle">
                    @if($order->payment)
                        @if($order->payment->payment)
                            <a href="{{ route("{$route_namespace}.payments.edit", $order->payment->payment) }}"
                               title="@lang('admin/orders/orders.payment')"
                            >{{ \Illuminate\Support\Str::words($order->payment->aVar('name'), 12,'....') }}</a>
                        @else
                            {{ \Illuminate\Support\Str::words($order->payment->aVar('name'), 12,'....') }}
                        @endif
                    @endif
                </td>
                {{-- @HOOK_AFTER_PAYMENT --}}

                {{--    CONFIRMED    --}}
                <td class="text-center align-middle">
                    {{$order->confirmed_at->format('d.m.Y H:i')}}
                </td>
                {{-- @HOOK_AFTER_CONFIRMED_AT --}}


                {{--    STATUS    --}}
                <td class="text-center align-middle">
                    @lang( $statuses[ $order->status ] )
                </td>
                {{-- @HOOK_AFTER_STATUS --}}

                {{--    TOTAL    --}}
                <td class="text-center align-middle">
                    {{ number_format($order->getTotalPrice(), 2, '.', ' ') }} {{ $siteCurrency }}
                </td>
                {{-- @HOOK_AFTER_TOTAL --}}

                {{--    EDIT    --}}
                <td class="text-center">
                    <a class="btn btn-link text-success"
                       href="{{ $orderEditUri }}"
                       title="@lang('admin/orders/orders.edit')"><i class="fa fa-edit"></i></a></td>
                {{-- @HOOK_AFTER_EDIT --}}

                {{--    DELETE    --}}
                <td class="text-center">
                    @can('delete', $order)
                        <form action="{{ route("{$route_namespace}.orders.destroy", $order->id) }}"
                              method="POST"
                              id="delete[{{$order->id}}]">
                            @csrf
                            @method('DELETE')
                            @php
                                $redirectTo = (!$orders->onFirstPage() && $orders->count() == 1)?
                                        $orders->previousPageUrl() : url()->full();
                            @endphp
                            <input type="hidden" name="redirect_to" value="{{$redirectTo}}" />
                            <button class="btn btn-link text-danger"
                                    title="@lang('admin/orders/orders.remove')"
                                    onclick="if(confirm('@lang("admin/orders/orders.remove_ask")')) document.querySelector( '#delete\\[{{$order->id}}\\] ').submit() "
                                    type="button"><i class="fa fa-trash"></i></button>
                        </form>
                    @endcan
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="100%">@lang('admin/orders/orders.no_orders')</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{$orders->links('admin.paging')}}

</div>
