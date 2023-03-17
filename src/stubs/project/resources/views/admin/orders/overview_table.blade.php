<table border="0" width="100%" cellspacing="0" cellpadding="0" style="width:800px; margin:0px auto;">
    @php $columnCount = 8; @endphp
    {{-- @HOOK_AFTER_COLUMN_SPAN_COUNT --}}
    <tr style="font-weight: bold; text-align: center;">
        <td style="background:#eeeeee;">@lang('admin/orders/overview.table.product_name')</td>
        {{-- @HOOK_AFTER_NAME_TH --}}
        <td style="background:#eeeeee;">@lang('admin/orders/overview.table.product_size')</td>
        {{-- @HOOK_AFTER_SIZE_TH --}}
        <td style="background:#eeeeee;">@lang('admin/orders/overview.table.product_price')</td>
        {{-- @HOOK_AFTER_PRICE_TH --}}
        <td style="background:#eeeeee;">@lang('admin/orders/overview.table.product_vat')</td>
        {{-- @HOOK_AFTER_VAT_TH --}}
        <td style="background:#eeeeee;">@lang('admin/orders/overview.table.product_discount')</td>
        {{-- @HOOK_AFTER_DISCOUNT_TH --}}
        <td style="background:#eeeeee;">@lang('admin/orders/overview.table.product_quantity')</td>
        {{-- @HOOK_AFTER_QUANTITY_TH --}}
        <td style="background:#eeeeee;">@lang('admin/orders/overview.table.product_sum')</td>
        {{-- @HOOK_AFTER_SUM_TH --}}
    </tr>
    @foreach($chOrder->products as $cartProduct)
        @if($cartProduct->owner_type === \App\Models\ProductSize::class)
            @php
                $productableLink = '';
                if($size = $cartProduct->owner) {
                    $product = $size->product;
                    //category = $product->categories()->first();
                    //$productableLink = route("{$route_namespace}.categories.products.edit", [$category, $product]);
                }
                $name = explode('-', $cartProduct->aVar('parts_name'));
                $productName = array_shift($name);
                $sizeName = empty($name)? '#' : implode('-', $name);
            @endphp
            <tr style="text-align: center;">
                <td align="left">{{$productName}}</td>
                {{-- @HOOK_AFTER_NAME --}}
                <td align="left">{{$sizeName}}</td>
                {{-- @HOOK_AFTER_SIZE --}}
                <td>{{number_format($cartProduct->getPrice(false, false), 2)}}</td>
                {{-- @HOOK_AFTER_PRICE --}}
                <td>{{ number_format($cartProduct->getVat(), 2, '.', ' ') }}</td>
                {{-- @HOOK_AFTER_VAT --}}
                <td>{{ number_format($cartProduct->getDiscountValue(), 2, '.', ' ') }}</td>
                {{-- @HOOK_AFTER_DISCOUNT --}}
                <td>{{$cartProduct->quantity}}</td>
                {{-- @HOOK_AFTER_QUANTITY --}}
                <td>{{ number_format($cartProduct->getTotalPrice(), 2, '.', ' ') }}</td>
                {{-- @HOOK_AFTER_SUM --}}
            </tr>
        @endif
        {{-- @HOOK_AFTER_ROW --}}
    @endforeach
    @if($chOrder->delivery)
        <tr style="text-align: center;">
            <td align="left" colspan="2">{{$chOrder->delivery->aVar('name')}}</td>
            {{-- @HOOK_DELIVERY_NAME --}}
            <td>{{ number_format($chOrder->delivery->getTax(false, false), 2, '.', ' ') }}</td>
            {{-- @HOOK_DELIVERY_PURE_TAX --}}
            <td>{{ number_format($chOrder->delivery->getVat(), 2, '.', ' ') }}</td>
            {{-- @HOOK_DELIVERY_VAT --}}
            <td>{{ number_format($chOrder->delivery->getDiscountValue(), 2, '.', ' ') }}</td>
            {{-- @HOOK_DELIVERY_DISCOUNT --}}
            <td>1</td>
            {{-- @HOOK_DELIVERY_QUANTITY --}}
            <td>{{ number_format($chOrder->delivery->getTax(), 2, '.', ' ') }}</td>
            {{-- @HOOK_DELIVERY_TAX --}}
        </tr>
    @endif
    @if($chOrder->payment)
        <tr style="text-align: center;">
            <td align="left" colspan="2">{{$chOrder->payment->aVar('name')}}</td>
            {{-- @HOOK_PAYMENT_NAME --}}
            <td>{{ number_format($chOrder->payment->getTax(false, false), 2, '.', ' ') }}</td>
            {{-- @HOOK_PAYMENT_PURE_TAX --}}
            <td>{{ number_format($chOrder->payment->getVat(), 2, '.', ' ') }}</td>
            {{-- @HOOK_PAYMENT_VAT --}}
            <td>{{ number_format($chOrder->payment->getDiscountValue(), 2, '.', ' ') }}</td>
            {{-- @HOOK_PAYMENT_DISCOUNT --}}
            <td>1</td>
            {{-- @HOOK_PAYMENT_QUANTITY --}}
            <td>{{ number_format($chOrder->payment->getTax(), 2, '.', ' ') }}</td>
            {{-- @HOOK_PAYMENT_TAX --}}
        </tr>
    @endif

    <tr style="text-align: right;">
        <td colspan="{{$columnCount}}">@lang('admin/orders/overview.table.discount'):{{ number_format($chOrder->getDiscountValue(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
    </tr>
    {{-- @HOOK_DISCOUNT --}}

    <tr style="text-align: right;">
        <td colspan="{{$columnCount}}">@lang('admin/orders/overview.table.vat'): {{ number_format($chOrder->getTotalVat(), 2, '.', ' ') }} {{ $siteCurrency }}</td>
    </tr>
    {{-- @HOOK_VAT--}}

    <tr style="text-align: right;">
        <td colspan="{{$columnCount}}"><b>@lang('admin/orders/overview.table.total'): {{ number_format($chOrder->getTotalPrice(), 2, '.', ' ') }} {{ $siteCurrency }}</b></td>
    </tr>
    {{-- @HOOK_TOTAL --}}
</table>
