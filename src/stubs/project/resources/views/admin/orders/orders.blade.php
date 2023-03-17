@pushonce('above_css')
<!-- JQUERY UI -->
<link href="{{ asset('admin/vendor/jquery-ui-1.12.1/jquery-ui.min.css') }}" rel="stylesheet" type="text/css" />
@endpushonce

@pushonce('below_js')
<script language="javascript"
        type="text/javascript"
        src="{{ asset('admin/vendor/jquery-ui-1.12.1/jquery-ui.min.js') }}"></script>
@endpushonce

@pushonceOnReady('below_js_on_ready')
<script>
    $(document).on('datePickersInstance', function(e) {
        $('.datepicker').datepicker({
            dateFormat: "dd.mm.yy"
        });
    });
    $(document).trigger('datePickersInstance');
    //CHANGE FILTER
    $(document).on('change', '.js_filter', function(e) {
        var $this = $(this);
        var $thisVal = $this.val();
        if($thisVal == 'all' || $thisVal == '') {
            window.location.href= $this.attr('data-action_all')
            return;
        }
        window.location.href= $this.attr('data-action').replace('__VAL__', $this.val());
    });
</script>
@endpushonceOnReady

<x-admin.main>
    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route("{$route_namespace}.home")}}"><i class="fa fa-home"></i></a></li>
            <li class="breadcrumb-item active">@lang('admin/orders/orders.orders')</li>
        </ol>

        <form autocomplete="off">
            <div class="row">
                {{-- STATUSES --}}
                <div class="form-group row col-lg-3">
                    <label for="filters[role]" class="col-form-label col-sm-3">Statuses:</label>
                    <div class="col-sm-9">
                        <select id="filters[status]"
                                name="filters[status]"
                                data-action_all="{{marinarFullUrlWithQuery( ['filters' => ['status' => null]] )}}"
                                data-action="{{marinarFullUrlWithQuery( ['filters' => ['status' => '__VAL__']] )}}"
                                class="form-control js_filter">
                            <option value='all'>@lang('admin/orders/orders.statuses.all')</option>

                            @foreach($statuses as $status_index => $statusName)
                                <option value="{{ $status_index }}"
                                        @isset($filters['status']) @if($filters['status'] == $status_index) selected="selected" @endif @endisset
                                > @lang($statusName)
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- @HOOK_AFTER_STATUSES_FILTER --}}

                <div class="form-group row col-lg-4">
                    <div class="col-sm-6">
                        <input class="form-control datepicker js_filter"
                               name=' name="filters[from_date]"'
                               data-action_all="{{marinarFullUrlWithQuery( ['filters' => ['from_date' => null]] )}}"
                               data-action="{{marinarFullUrlWithQuery( ['filters' => ['from_date' => '__VAL__']] )}}"
                               value="@isset($filters['from_date']){{$filters['from_date']}}@endisset"
                               placeholder="@lang('admin/orders/orders.from_date')"
                        />
                    </div>
                    <div class="col-sm-6">
                        <input class="form-control datepicker js_filter"
                               name=' name="filters[to_date]"'
                               data-action_all="{{marinarFullUrlWithQuery( ['filters' => ['to_date' => null]] )}}"
                               data-action="{{marinarFullUrlWithQuery( ['filters' => ['to_date' => '__VAL__']] )}}"
                               value="@isset($filters['to_date']){{$filters['to_date']}}@endisset"
                               placeholder="@lang('admin/orders/orders.to_date')"
                        />
                    </div>
                </div>
                {{-- @HOOK_AFTER_DATE_FILTER --}}

                {{-- SEARCH --}}
                <div class="form-group row col-lg-3">
                    <div class="col-sm-10">
                        <div class="input-group">
                            <input type="text"
                                   name="search"
                                   id="search"
                                   placeholder="@lang('admin/orders/orders.search')"
                                   value="@if(isset($search)){{$search}}@endif"
                                   class="form-control "
                            />
                            <div class="input-group-append">
                                <button class="btn btn-primary"><i class="fas fa-search text-grey"
                                                                   aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- @HOOK_AFTER_SEARCH --}}

                <div class="form-group col-lg-1">
                    @php $xlsQueryParams = request()->query(); unset($xlsQueryParams['page']); @endphp
                    <a href="{{route("{$route_namespace}.orders.index_xlsx", $xlsQueryParams)}}"
                       class="btn btn-success">@lang('admin/orders/orders.xlsx')</a>
                </div>
                {{-- @HOOK_AFTER_XLSX_EXPORT --}}
            </div>

        </form>

        <x-admin.box_messages />

        @include('admin/orders/orders_table')
    </div>
</x-admin.main>
