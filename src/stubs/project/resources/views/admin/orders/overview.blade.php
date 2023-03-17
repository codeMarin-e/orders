<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        html, body {
            font-size: 12px;
            font-family: Tahoma, Arial, Verdana, sans-serif;
        }

        table {
            font-size: 12px;
            border-top: 1px solid #DEDEDE;
            border-left: 1px solid #DEDEDE;
            color: #4B3B3C;
        }

        td {
            border-bottom: 1px solid #DEDEDE;
            border-right: 1px solid #DEDEDE;
            padding: 2px 4px;
        }

        .boxTable td {
            border: none;
        }
    </style>
</head>
<body>

<table cellpadding="0" cellspacing="0" border="0" style="width:800px; margin:0px auto; border: none;" class="boxTable">
    <tr>
        <td>

            @if($logo = $chSite->getMainAttachment('logo'))
                <img src="{{$logo->getThumbnail('190x108')->getUrl()}}" alt="{{$chSite->domain}}" />
            @endif
        </td>
    </tr>
</table>
<br/>
@if( ($overviewText = trans('admin/orders/overview.text')) != 'admin/orders/overview.text')
    <div style="width:800px; margin:0px auto;">
        {{$overviewText}}
    </div>
@endif

@php
    $chOrderFAddr = $chOrder->getFacturaAddress();
    $chOrderDAddr = $chOrder->getDeliveryAddress();
@endphp
<table cellpadding="0" cellspacing="0" border="0" style="width:800px; margin:20px auto; border: none;" class="boxTable">
    <tr>
        <td>
            <div style="padding:2px; border:solid 1px #DEDEDE;">
                <h1 style="margin:2px 2px 10px 2px; padding:2px 5px; background:#efefef; font-size:16px;">
                    @lang('admin/orders/overview.order_info')</h1>
                <div style="padding:5px 10px;">
                    <strong>@lang('admin/orders/overview.factura.name')</strong>: {{$chOrderFAddr->fullname}} <br/>
                    {{-- @HOOK_AFTER_CUSTOMER_NAME --}}
                    <strong>@lang('admin/orders/overview.order_ref')</strong>: {{$chOrder->order_id}} |
                    {{-- @HOOK_AFTER_ORDER_ID --}}
                    <strong>@lang('admin/orders/overview.confirmed')</strong>: {{$chOrder->confirmed_at->format('d.m.Y H:i')}}
                    {{-- @HOOK_AFTER_CONFIRMED_AT --}}

                    @if($comments = $chOrder->aVar('comments'))
                        <br/><strong>@lang('admin/orders/overview.comments')</strong>: {{$comments}}<br/>
                    @endif
                    {{-- @HOOK_AFTER_COMMENTS --}}
                    @if($chOrder->delivery->overview)
                        @includeif($chOrder->delivery->overview)
                    @endif
                    {{-- @HOOK_AFTER_DELIVERY_OVERVIEW --}}
                    @if($chOrder->payment->overview)
                        @includeif($chOrder->payment->overview)
                    @endif
                    {{-- @HOOK_AFTER_PAYMENT_OVERVIEW --}}
                </div>
            </div>
        </td>
    </tr>
</table>
{{-- @HOOK_AFTER_CUSTOMER_INFO --}}

@include('admin/orders/overview_table')
{{-- @HOOK_AFTER_TABLE --}}
@include('admin/orders/overview_addresses')
{{-- @HOOK_AFTER_ADDRESSES --}}
@if(isset($print))
<script language="javascript">
    window.print();
</script>
@endif
</body>
</html>
