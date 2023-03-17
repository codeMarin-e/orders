<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Address;
use App\Models\Cart;
use App\Http\Requests\OrderRequest;

class OrderController extends Controller {
    public static $toExcellExport;

    public function __construct() {
        if(!request()->route()) return;

        $this->db_table = Cart::getModel()->getTable();
        $this->routeNamespace = Str::before(request()->route()->getName(), '.orders');
        View::composer('admin/orders/*', function($view)  {
            $viewData = [
                'route_namespace' => $this->routeNamespace,
            ];
            // @HOOK_VIEW_COMPOSERS
            $view->with($viewData);
        });
        // @HOOK_CONSTRUCT
    }

    public function index($xlsx = null) {
        $viewData = [];

        $bldQry = Cart::select($this->db_table.".*")
            ->with(['addresses', 'user', 'payment.payment', 'delivery.delivery'])
            ->where('status', '!=', null)
            ->where('status', '!=', 'processing')
            ->where($this->db_table.".site_id", app()->make('Site')->id);
        $viewData['statuses'] = Cart::$statuses;

        if($filters = request()->get('filters')) {
            //BY STATUS
            if(isset($filters['status'])) {
                $filterStatus = (string)$filters['status'];
                if($filters['status'] == 'all') {
                    $routeQry = request()->query();
                    unset($routeQry['filters']['status']);
                    return redirect( now_route(null, $routeQry) );
                }
                $bldQry->where('status', $filterStatus);
                $viewData['filters']['status'] = $filterStatus;
            }
            //END BY STATUS
            //BY FROM DATE
            if(isset($filters['from_date'])) {
                if($fromDate = Carbon::createFromFormat('d.m.Y H:i:s', $filters['from_date'].' 00:00:00')) {
                    $bldQry->where('confirmed_at', '>', $fromDate);
                    $viewData['filters']['from_date'] = $filters['from_date'];
                } else {
                    $routeQry = request()->query();
                    unset($routeQry['filters']['from_date']);
                    return redirect( now_route(null, $routeQry) );
                }
            }
            //END BY FROM DATE
            //BY TO DATE
            if(isset($filters['to_date'])) {
                if($fromDate = Carbon::createFromFormat('d.m.Y H:i:s', $filters['to_date'].' 23:59:59')) {
                    $bldQry->where('confirmed_at', '<', $fromDate);
                    $viewData['filters']['to_date'] = $filters['to_date'];
                } else {
                    $routeQry = request()->query();
                    unset($routeQry['filters']['to_date']);
                    return redirect( now_route(null, $routeQry) );
                }
            }
            //END BY TO DATE
        }

        //SEARCH
        if(request()->has('search')) {
            $search = request()->get('search');
            if (is_numeric($search)) {
                $search = (int)$search;
                $bldQry->where(function($qry) use ($search) {
                    $qry->where('id', $search)
                        ->orWhere('order_id', $search);
                });
            } else {
                $searchParts = explode(' ', $search);
                $searchParts = array_filter($searchParts, 'trim');
                if($searchParts($searchParts)) {
                    $routeQry = request()->query();
                    unset($routeQry['search']);
                    return redirect( now_route(queries: $routeQry) );
                }
                $qryRaw = [];
                foreach ($searchParts as $sWord) {
                    $sWord = trim($sWord);
                    if ($sWord === '') continue;
                    $qryRaw[] = '(' . implode(' OR ', [
                            "fname LIKE '%{$sWord}%'",
                            "lname LIKE '%{$sWord}%'",
                            "mail LIKE '%{$sWord}%'",
                        ]) . ')';
                }
                $bldQry->whereHas('addresses', function($qry) use ($searchParts) {
                    $qry->whereRaw('0 = 1');
                    foreach($searchParts as $searchPart) {
                        $qry->orWhere("fname", 'LIKE', "%{$searchPart}%");
                        $qry->orWhere("lname", 'LIKE', "%{$searchPart}%");
                        $qry->orWhere("email", 'LIKE', "%{$searchPart}%");
                        $qry->orWhere("phone", 'LIKE', "%{$searchPart}%");
                    }
                });
            }
            $viewData['search'] = $search;
        }
        //END SEARCH

        $bldQry->orderBy($this->db_table.".confirmed_at", 'DESC');

        // @HOOK_INDEX_END

        if($xlsx) {
            $this->getXLSX( $bldQry->get() );
        }
        $viewData['orders'] = $bldQry->paginate(20)->appends( request()->query() );

        return view('admin/orders/orders', $viewData);
    }

    private function getXLSX($orders) {
        if (static::$toExcellExport) {
            return call_user_func(static::$toExcellExport, $orders);
        }

        //https://opensource.box.com/spout/
        $writer = WriterEntityFactory::createXLSXWriter();

//            $writer->openToFile($filePath); // write data to a file or to a PHP stream
        $fileName = 'orders.xlsx';
        // @HOOK_INDEX_EXPORT_XLS_FILENAME
        $writer->openToBrowser($fileName); // stream data directly to the browser

        $border = (new BorderBuilder())
            ->setBorderBottom(Color::GREEN, Border::WIDTH_THIN, Border::STYLE_DASHED)
            ->build();

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setBorder($border)
//                ->setFontSize(15)
            ->setFontColor(Color::BLUE)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::YELLOW)
            ->build();

        $ths = WriterEntityFactory::createRowFromArray(array_values([
            'ID',
            'User',
            'Name',
            'E-mail',
            'Phone',
            'Postcode',
            'City',
            'Street',
            'Country',
            'Company',
            'Orgnum',
            'Del. Name',
            'Del. Phone',
            'Del. Postcode',
            'Del. City',
            'Del. Street',
            'Del. Country',
            'Del. Company',
            'Del. Orgnum',

            'Payment',
            'Delivery',
            'Status',

            'Total'
        ]), $style);
        // @HOOK_INDEX_EXPORT_XLS_TH_ROW
        $writer->addRow($ths);

        /** add a row at a time */
//            $singleRow = WriterEntityFactory::createRow($cells);
//            $writer->addRow($singleRow);
//
//            /** add multiple rows at a time */
//            $multipleRows = [
//                WriterEntityFactory::createRow($cells),
//                WriterEntityFactory::createRow($cells),
//            ];
//            $writer->addRows($multipleRows);
        foreach($orders as $order) {
            $orderFAddr = $order->getFacturaAddress();
            $orderDAddr = $order->getDeliveryAddress();
            $orderPayment = $order->payment;
            $orderDelivery = $order->delivery;

            /** Shortcut: add a row from an array of values */
            $rowFromValues = WriterEntityFactory::createRowFromArray([
                'id' => $order->id,
                'user_name' => $orderFAddr->fullName,
                'mail' => $orderFAddr->mail,
                'phone'=> $orderFAddr->phone ,
                'postcode' => $orderFAddr->postcode,
                'city' => $orderFAddr->city,
                'street' => $orderFAddr->street,
                'country' => $orderFAddr->country,
                'company' => $orderFAddr->company,
                'orgnum' => $orderFAddr->orgnum,

                'del_fullname' => $orderDAddr->fullName,
                'E-del_mail' => $orderDAddr->mail,
                'del_phone'=> $orderDAddr->phone ,
                'del_postcode' => $orderDAddr->postcode,
                'del_city' => $orderDAddr->city,
                'del_street' => $orderDAddr->street,
                'del_country' => $orderDAddr->country,
                'del_company' => $orderDAddr->company,
                'del_orgnum' => $orderDAddr->orgnum,

                'payment' => $orderPayment->aVar('name'),
                'delivery' => $orderDelivery->aVar('name'),

                'status' => trans(Cart::$statuses[ $order->status ]),

                'total' => number_format($order->getTotalPrice(), 2)
            ]);
            // @HOOK_INDEX_EXPORT_XLS_ROW
            $writer->addRow($rowFromValues);
        }

        $writer->close();
        exit;
    }

    public function edit(Cart $chOrder) {
        $viewData = [];
        $chOrder->load(['products.owner', 'delivery.delivery', 'payment.payment', 'user']);
        $viewData['chOrder'] = $chOrder;
        $viewData['chOrderDeliveryAddr'] = $chOrder->getDeliveryAddress();
        $viewData['chOrderFacturaAddr'] = $chOrder->getFacturaAddress();
        $viewData['statuses'] = Cart::$statuses;

        // @HOOK_EDIT

        return view('admin/orders/order', $viewData);
    }

    public function update(Cart $chOrder, OrderRequest $request) {
        $validatedData = $request->validated();

        // @HOOK_UPDATE_VALIDATE

        $chOrderDAddr = $chOrder->getDeliveryAddress();
        $chOrderFAddr = $chOrder->getFacturaAddress();
        $chOrder->loadMissing('products.owner');
        foreach($chOrder->products as $cartProduct) {
            if(!$cartProduct->owner) continue;
            $newPrice = $validatedData['products'][$cartProduct->id]['price'];
            if($cartProduct->price != $newPrice) {
                if($cartProduct->vat_in_price) {
                    $newPrice += $newPrice * ($cartProduct->vat/100);
                }
                $cartProduct->update(['price' => $newPrice]);
            }
            if($cartProduct->quantity != $validatedData['products'][$cartProduct->id]['quantity']) {
                $cartProduct->setQuantity($validatedData['products'][$cartProduct->id]['quantity']);
            }
        }
        $chOrderDAddr->update((array)$validatedData['delAddr']);
        $chOrderFAddr->update((array)$validatedData['facAddr']);
        $chOrder->setStatus($validatedData['set_status']);

        // @HOOK_UPDATE_END

        event( 'order.submited', [$chOrder, $validatedData] );

        if($request->has('action')) {
            return redirect()->route($this->routeNamespace.'.orders.index')
                ->with('message_success', trans('admin/orders/order.updated'));
        }
        return back()->with('message_success', trans('admin/orders/order.updated'));
    }

    public function overview(Cart $chOrder) {
        $viewData = [];
        $chOrder->load(['products.owner', 'payment', 'delivery']);
        $viewData['chOrder'] = $chOrder;

        // @HOOK_OVERVIEW

        return view( config('marinar_orders.overview_template', 'admin/orders/overview'), [
            'chOrder' => $chOrder,
        ] );
    }

    public function destroy(Cart $chOrder, Request $request) {
        // @HOOK_DESTROY

        $chOrder->delete();

        // @HOOK_DESTROY_END

        event( 'order.removed', [$chOrder] );

        if($request->redirect_to)
            return redirect()->to($request->redirect_to)
                ->with('message_danger', trans('admin/orders/order.deleted'));

        return redirect()->route($this->routeNamespace.'.orders.index')
            ->with('message_danger', trans('admin/orders/order.deleted'));
    }

    // @HOOK_METHODS
}
