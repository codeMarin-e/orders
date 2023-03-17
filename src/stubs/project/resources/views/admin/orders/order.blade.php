@php $inputBag = 'order'; @endphp
@pushonce('below_templates')
@if(isset($chOrder) && $authUser->can('delete', $chOrder))
    <form action="{{ route("{$route_namespace}.orders.destroy", $chOrder) }}"
          method="POST"
          id="delete[{{$chOrder->id}}]">
        @csrf
        @method('DELETE')
    </form>
@endif
@endpushonce
<x-admin.main>
    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route("{$route_namespace}.home")}}"><i class="fa fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route("{$route_namespace}.orders.index") }}">@lang('admin/orders/orders.orders')</a></li>
            <li class="breadcrumb-item active">{{ $chOrder->id }} [{{ $chOrder->order_id }}]</li>
        </ol>
        <div class="card">
            <div class="card-body">
                <form method="POST"
                      action="{{route("{$route_namespace}.orders.update", [$chOrder])}}"
                      autocomplete="off"
                      enctype="multipart/form-data">
                    @csrf

                    @isset($chOrder)@method('PATCH')@endisset

                    <x-admin.box_messages />

                    <x-admin.box_errors :inputBag="$inputBag" />

                    {{-- @HOOK_AFTER_MESSAGES --}}

                    @include('admin/orders/order_table')

                    {{-- @HOOK_AFTER_TABLE --}}

                    @include('admin/orders/order_addresses')

                    {{-- @HOOK_AFTER_ADDRESSES --}}

                    @if($comments = $chOrder->aVar('comments'))
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">@lang('admin/orders/order.comments')</div>
                                <div class="card-body">
                                    <p class="card-text">{{$comments}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    {{-- @HOOK_AFTER_COMMENTS --}}

                    <!-- STATUS -->
                    @php
                        $sStatus = old("{$inputBag}.set_status", (isset($chOrder)? $chOrder->status : array_key_first($statuses)));
                    @endphp
                    <div class="form-group row">
                        <label for="{{$inputBag}}[set_status]"
                               class="col-sm-1 col-form-label">@lang('admin/orders/order.status')</label>
                        <div class="col-sm-2">
                            <select class="form-control"
                                    id="{{$inputBag}}[set_status]"
                                    name="{{$inputBag}}[set_status]">
                                @foreach($statuses as $status => $statusTranCode)
                                    <option value="{{$status}}"
                                            @if($status == $sStatus)selected="selected"@endif>@lang($statusTranCode)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- @HOOK_AFTER_STATUS --}}

                    <div class="form-group row">
                        <div class="col-12"><a href="{{ route("{$route_namespace}.orders.overview", [ $chOrder ]) }}">@lang('admin/orders/order.overview')</a></div>

                        {{-- @HOOK_AFTER_OTHER_DOCUMENTS --}}
                    </div>

                    {{-- @HOOK_AFTER_OVERVIEW --}}

                    <div class="form-group row">
                        @isset($chOrder)
                            @can('update', $chOrder)
                                <button class='btn btn-success mr-2'
                                        type='submit'
                                        onclick="if(!confirm('@lang('admin/orders/order.submit_ask')')) return false;"
                                        name='action'>@lang('admin/orders/order.save')
                                </button>

                                <button class='btn btn-primary mr-2'
                                        type='submit'
                                        onclick="if(!confirm('@lang('admin/orders/order.submit_ask')')) return false;"
                                        name='update'>@lang('admin/orders/order.update')</button>
                            @endcan

                            @can('delete', $chOrder)
                                <button class='btn btn-danger mr-2'
                                        type='button'
                                        onclick="if(confirm('@lang("admin/orders/order.delete_ask")')) document.querySelector( '#delete\\[{{$chOrder->id}}\\] ').submit() "
                                        name='delete'>@lang('admin/orders/order.delete')</button>
                            @endcan
                        @endisset
                        <a class='btn btn-warning'
                           href="{{ route("{$route_namespace}.orders.index") }}"
                        >@lang('admin/orders/order.cancel')</a>
                    </div>

                    <div class="form-group row">
                        {{-- @HOOK_ADDON_BUTTONS --}}
                    </div>
                </form>

            </div>
        </div>

        {{-- @HOOK_FOR_ADDONS --}}

    </div>
</x-admin.main>
