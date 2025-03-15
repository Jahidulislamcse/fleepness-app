<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainOrder;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VendorOrderController extends Controller
{
    public function index()
    {
        $orderList = MainOrder::with('customerInfo')
            ->where('vendor_id', auth()->id())
            ->orderBy('id', 'desc')
            ->get();

        //  dd($orderList);
        return view('vendor.order.index', compact('orderList'));
    }

    public function orderDetails(Request $request, $id = null)
    {

        $orderInfo = MainOrder::with('orderDetails', 'customerInfo', 'orderDetails.productInfo', 'orderDetails.productInfo.imagesProduct', 'orderDetails.stockInfo')->where('id', $request->id)->first();
        return view('vendor.order.details', compact('orderInfo'));
    }
    public function PendingToCancel($order_id)
    {
        MainOrder::findOrFail($order_id)->update([
            'status' => 'cancel',
            'cancel_date' => Carbon::now()->format('d F Y'),
        ]);
        $order = MainOrder::where('id', $order_id)->first();
        $orderItem = OrderItem::where('main_order_id', $order_id)->get();
        foreach ($orderItem as $item) {
            $product = Stock::where('id', $item->stock_info_id)->first();
            $product->order_qty = $product->order_qty - $item->qty;
            $product->quantity = $product->quantity + $item->qty;
            $product->save();
        }


        $notification = array(
            'message' => 'Order Cancel Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('vendor.order.cancled')->with($notification);
    }

    public function PendingToConfirm($order_id)
    {
        MainOrder::findOrFail($order_id)->update(['status' => 'confirm', 'confirmed_date' => Carbon::now()->format('d F Y')]);

        $notification = array(
            'message' => 'Order Confirm Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('vendor.order.confirmed')->with($notification);
    }
    public function PendingOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'pending')->orderBy('id', 'DESC')->get();

        $countOrder = MainOrder::where('status', 'pending')->count('id');
        return view('vendor.order.pending', compact('orderList', 'countOrder'));
    }
    public function ConfirmedOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'confirm')->orderBy('id', 'DESC')->get();


        return view('vendor.order.confirm', compact('orderList'));
    }
    public function ProcessingOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'processing')->orderBy('id', 'DESC')->get();


        return view('vendor.order.processing', compact('orderList'));
    }

    public function ReadyOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'ready')->orderBy('id', 'DESC')->get();


        return view('vendor.order.ready', compact('orderList'));
    }

    public function shippedOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'shipped')->orderBy('id', 'DESC')->get();


        return view('vendor.order.shipped', compact('orderList'));
    }

    public function DeliveredOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'deliverd')->orderBy('id', 'DESC')->get();


        return view('vendor.order.delivered', compact('orderList'));
    }
    public function completedOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'deliverd')->where('payment_status', 'received')->orderBy('id', 'DESC')->get();
        return view('vendor.order.completed', compact('orderList'));
    }
    public function CancledOrder()
    {

        $orderList = MainOrder::with('customerInfo')->where('vendor_id', auth()->id())->where('status', 'cancel')->orderBy('id', 'DESC')->get();


        return view('vendor.order.cancle', compact('orderList'));
    }


    public function ConfirmToProcess($order_id)
    {
        MainOrder::findOrFail($order_id)->update(['status' => 'processing', 'processing_date' => Carbon::now()->format('d F Y')]);

        $notification = array(
            'message' => 'Order Processing Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('vendor.order.processing')->with($notification);
    }

    public function ProcessToDelivered($order_id)
    {


        MainOrder::findOrFail($order_id)->update(['status' => 'deliverd', 'delivered_date' => Carbon::now()]);

        $notification = array(
            'message' => 'Order Deliverd Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('vendor.order.delivered')->with($notification);
    }

    public function ProcessToReadyToShip($order_id)
    {


        MainOrder::findOrFail($order_id)->update(['status' => 'ready']);

        $notification = array(
            'message' => 'Order is Ready to Ship',
            'alert-type' => 'success'
        );

        return redirect()->route('vendor.order.ready')->with($notification);
    }


    public function AdminInvoiceDownload($order_id)
    {

        $order = MainOrder::with('customerInfo')->where('id', $order_id)->first();
        $orderItem = OrderItem::with('productInfo', 'productInfo.imagesProduct', 'stockInfo')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();
        $currency = env('currency', '৳');
        $pdf = Pdf::loadView('vendor.order.invoice', compact('order', 'orderItem', 'currency'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);
        return $pdf->download($order->invoice_no . '.pdf');
    }
}
