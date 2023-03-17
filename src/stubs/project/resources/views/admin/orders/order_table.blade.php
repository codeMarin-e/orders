@php $columnsCount = 7; @endphp
{{-- @HOOK_AFTER_COLUMNS_SPAN_COUNT --}}
<!-- ORDER -->
<div class="row">
    <div class="table-responsive rounded ">
        <table class="table table-sm ">
            <thead class="thead-light">
            <tr>
                <th class="text-center align-middle">@lang('admin/orders/order.table.product_name')</th>
                {{-- @HOOK_AFTER_NAME_TH --}}

                <th class="text-center align-middle">@lang('admin/orders/order.table.product_size')</th>
                {{-- @HOOK_AFTER_SIZE_TH --}}

                <th class="text-center align-middle">@lang('admin/orders/order.table.product_quantity')</th>
                {{-- @HOOK_AFTER_QUANTITY_TH --}}

                <th class="text-center align-middle">@lang('admin/orders/order.table.product_price')</th>
                {{-- @HOOK_AFTER_PRICE_TH --}}

                <th class="text-center align-middle">@lang('admin/orders/order.table.product_vat')</th>
                {{-- @HOOK_AFTER_VAT_TH --}}

                <th class="text-center align-middle">@lang('admin/orders/order.table.product_discount')</th>
                {{-- @HOOK_AFTER_DISCOUNT_TH --}}

                <th class="text-center align-middle">@lang('admin/orders/order.table.product_sum')</th>
                {{-- @HOOK_AFTER_SUM_TH --}}
            </tr>
            </thead>
            <tbody>
            @foreach($chOrder->products as $cartProduct)
                @if($cartProduct->owner_type === \App\Models\ProductSize::class)
                    @php
                        $productableLink = '';
                        if($size = $cartProduct->owner) {
                            $product = $size->product;
                            $productableLink = route("{$route_namespace}.categories.products.edit", [ $product->getMainCategory(), $product]);
                        }
                        $name = explode('-', $cartProduct->aVar('parts_name'));
                        $productName = array_shift($name);
                        $sizeName = empty($name)? '#' : implode('-', $name);
                    @endphp
                    <tr>
                        <td class="text-center align-middle">
                            <a href="{{ $productableLink}}" target="_blank">{{$productName}}</a>
                        </td>
                        {{-- @HOOK_AFTER_NAME --}}

                        <td class="text-center align-middle">
                            <a href="{{ $productableLink}}" target="_blank">{{$sizeName}}</a>
                        </td>
                        {{-- @HOOK_AFTER_SIZE --}}

                        <td class="text-center align-middle w-10">
                            <input type="text"
                                   name="{{$inputBag}}[products][{{$cartProduct->id}}][quantity]"
                                   value="{{$cartProduct->quantity}}"
                                   onkeyup="this.classList.remove('is-invalid')"
                                   class="form-control text-center @if($errors->{$inputBag}->has("products.{$cartProduct->id}.quantity")) is-invalid @endif"/>
                        </td>
                        {{-- @HOOK_AFTER_QUANTITY --}}

                        <td class="text-center align-middle  w-10">
                            <input type="text"
                                   name="{{$inputBag}}[products][{{$cartProduct->id}}][price]"
                                   value="{{number_format($cartProduct->getPrice(false, false), 2)}}"
                                   onkeyup="this.classList.remove('is-invalid')"
                                   class="form-control text-center @if($errors->{$inputBag}->has("products.{$cartProduct->id}.price")) is-invalid @endif"/>
                        </td>
                        {{-- @HOOK_AFTER_PRICE --}}

                        <td class="text-center align-middle">
                            {{ number_format($cartProduct->getVat(), 2, '.', ' ') }} {{ $siteCurrency }}
                        </td>
                        {{-- @HOOK_AFTER_VAT --}}

                        <td class="text-center align-middle">
                            {{ number_format($cartProduct->getDiscountValue(), 2, '.', ' ') }} {{ $siteCurrency }}
                        </td>
                        {{-- @HOOK_AFTER_DISCOUNT --}}

                        <td class="text-center align-middle">
                            {{ number_format($cartProduct->getTotalPrice(), 2, '.', ' ') }} {{ $siteCurrency }}
                        </td>
                        {{-- @HOOK_AFTER_SUM --}}
                    </tr>
                @endif

                {{-- @HOOK_AFTER_ROW --}}
            @endforeach
            </tbody>
            <tbody>
            @if($chOrder->delivery)
                <tr>
                    <td class="text-center align-center"
                        colspan="{{$columnsCount-4}}">
                        @if($chOrder->delivery?->delivery)
                            <a href='{{route("{$route_namespace}.deliveries.edit", [$chOrder->delivery->delivery])}}'>
                                {{$chOrder->delivery->aVar('name')}}
                            </a>
                        @else
                            {{$chOrder->delivery->aVar('name')}}
                        @endif
                    </td>
                    {{-- @HOOK_AFTER_DELIVERY_NAME --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->delivery->getTax(false, false), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_DELIVERY_PURE_TAX --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->delivery->getVat(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_DELIVERY_VAT --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->delivery->getDiscountValue(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_DELIVERY_DISCOUNT --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->delivery->getTax(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_DELIVERY_TAX --}}
                </tr>
            @endif
            {{-- @HOOK_AFTER_DELIVERY --}}
            @if($chOrder->payment)
                <tr>
                    <td  class="text-center align-center"
                         colspan="{{$columnsCount-4}}">
                        @if($chOrder->payment->payment)
                            <a href='{{route("{$route_namespace}.payments.edit", [$chOrder->payment->payment])}}'>
                                {{$chOrder->payment->aVar('name')}}
                            </a>
                        @else
                            {{$chOrder->payment->aVar('name')}}
                        @endif
                    </td>
                    {{-- @HOOK_AFTER_PAYMENT_NAME --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->payment->getTax(false), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_PAYMENT_PURE_TAX --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->payment->getVat(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_PAYMENT_VAT --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->payment->getDiscountValue(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_PAYMENT_DISCOUNT --}}
                    <td  class="text-center align-middle">{{ number_format($chOrder->payment->getTax(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
                    {{-- @HOOK_AFTER_PAYMENT_TAX --}}
                </tr>
            @endif
            {{-- @HOOK_AFTER_PAYMENT --}}
            <tr>
                <td  class="text-right align-right"
                     colspan="{{$columnsCount-1}}">@lang('admin/orders/order.table.main_discount')</td>
                <td  class="text-center align-middle">{{ number_format($chOrder->getDiscountValue(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
            </tr>
            {{-- @HOOK_AFTER_DISCOUNT --}}
            <tr>
                <td  class="text-right align-right"
                     colspan="{{$columnsCount-1}}">@lang('admin/orders/order.table.total')</td>
                <td  class="text-center align-middle">{{ number_format($chOrder->getTotalPrice(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
            </tr>
            {{-- @HOOK_AFTER_TOTAL --}}
            </tbody>
        </table>
    </div>
</div>
<!-- END ORDER -->
