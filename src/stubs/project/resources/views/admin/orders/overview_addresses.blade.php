<table cellpadding="0" cellspacing="0" border="0" style="width:800px; margin:20px auto; border: none;" class="boxTable">
    <tr>
        <td valign="top">
            <div style="padding:2px; border:solid 1px #DEDEDE;">
                <h1 style="margin:2px 2px 10px 2px; padding:2px 5px; background:#efefef; font-size:16px;">
                    @lang('admin/orders/overview.factura_address')</h1>
                <div style="padding:5px 10px;">
                    {{$chOrderFAddr->fullName}} <br/>
                    {{-- @HOOK_FACTURA_NAME --}}
                    {{$chOrderFAddr->phone}}<br/>
                    {{-- @HOOK_FACTURA_PHONE --}}
                    {{$chOrderFAddr->email}}<br/>
                    {{-- @HOOK_FACTURA_EMAIL--}}
                    {{$chOrderFAddr->street}} <br/>
                    {{-- @HOOK_FACTURA_STREET --}}
                    @if($chOrderFAddr->company)
                        {{$chOrderFAddr->company}} {{$chOrderFAddr->orgnum}} <br/>
                        {{-- @HOOK_COMPANY --}}
                    @endif
                    {{$chOrderFAddr->postcode}} {{$chOrderFAddr->city}} <br/>
                    {{-- @HOOK_FACTURA_CITY--}}
                    {{$chOrderFAddr->country}}<br/>
                    {{-- @HOOK_FACTURA_COUNTRY --}}
                </div>
            </div>
        </td>
        {{-- @HOOK_FACTURA --}}
        <td valign="top">
            <div style="padding:2px; border:solid 1px #DEDEDE;">
                <h1 style="margin:2px 2px 10px 2px; padding:2px 5px; background:#efefef; font-size:16px;">
                    @lang('admin/orders/overview.delivery_address')</h1>
                <div style="padding:5px 10px;">
                    {{$chOrderDAddr->fullName}} <br/>
                    {{-- @HOOK_DELIVERY_NAME --}}
                    {{$chOrderDAddr->phone}}<br/>
                    {{-- @HOOK_DELIVERY_PHONE --}}
                    {{$chOrderDAddr->email}}<br/>
                    {{-- @HOOK_DELIVERY_EMAIL --}}
                    {{$chOrderDAddr->street}} <br/>
                    {{-- @HOOK_DELIVERY_STREET--}}
                    @if($chOrderDAddr->company)
                        {{$chOrderDAddr->company}} {{$chOrderDAddr->orgnum}} <br/>
                        {{-- @HOOK_DELIVERY_COMPANY --}}
                    @endif
                    {{$chOrderDAddr->postcode}} {{$chOrderDAddr->city}} <br/>
                    {{-- @HOOK_DELIVERY_CITY --}}
                    {{$chOrderDAddr->country}}<br/>
                    {{-- @HOOK_DELIVERY_COUNTRY--}}
                </div>
            </div>
        </td>
    </tr>
    {{-- @HOOK_DELIVERY --}}
</table>
