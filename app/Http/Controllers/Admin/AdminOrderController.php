<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainOrder;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orderList = MainOrder::with(['customerInfo', 'vendor'])->orderBy('id', 'desc')->get();
        return view('admin.order.index', compact('orderList'));
    }

    public function orderDetails(Request $request, $id = null)
    {

        $orderInfo = MainOrder::with('orderDetails', 'vendor', 'customerInfo', 'orderDetails.productInfo', 'orderDetails.productInfo.imagesProduct', 'orderDetails.stockInfo')->where('id', $request->id)->first();
        return view('admin.order.details', compact('orderInfo'));
    }

    public function PendingOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'pending')->orderBy('id', 'DESC')->get();

        $countOrder = MainOrder::where('status', 'pending')->count('id');
        return view('admin.order.pending', compact('orderList', 'countOrder'));
    }

    public function ConfirmedOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'confirm')->orderBy('id', 'DESC')->get();


        return view('admin.order.confirm', compact('orderList'));
    }

    public function ProcessingOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'processing')->orderBy('id', 'DESC')->get();

        return view('admin.order.processing', compact('orderList'));
    }

    public function ReadyOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'ready')->orderBy('id', 'DESC')->get();

        return view('admin.order.ready', compact('orderList'));
    }

    public function shippedOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'shipped')->orderBy('id', 'DESC')->get();

        return view('admin.order.shipped', compact('orderList'));
    }

    public function DeliveredOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'deliverd')->orderBy('id', 'DESC')->get();

        return view('admin.order.delivered', compact('orderList'));
    }

    public function CompletedOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'deliverd')->where('payment_status', 'received')->orderBy('id', 'DESC')->get();


        return view('admin.order.completed', compact('orderList'));
    }

    public function CancledOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'cancel')->orderBy('id', 'DESC')->get();


        return view('admin.order.cancle', compact('orderList'));
    }

    public function ReadyToShipped($order_id)
    {
        MainOrder::findOrFail($order_id)->update(['status' => 'shipped', 'shipped_date' => Carbon::now()->format('d F Y')]);

        $notification = array(
            'message' => 'Order Shipped Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.order.shipped')->with($notification);
    }

    public function shippedToDelivered($order_id)
    {
        MainOrder::findOrFail($order_id)->update(['status' => 'deliverd', 'delivered_date' => Carbon::now()->format('d F Y')]);

        $notification = array(
            'message' => 'Order Delivered Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.order.delivered')->with($notification);
    }

    public function DeliveredToCompleted($order_id)
    {
        $order = MainOrder::findOrFail($order_id);

        if ($order && $order->vendor_id) {
            $vendor = User::find($order->vendor_id);
            if ($vendor) {
                $vendor->total_sales += $order->amount;
                $vendor->save();
            }
        }

        $order->update(['payment_status' => 'received']);

        $notification = [
            'message' => 'Order Completed Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.order.completed')->with($notification);
    }



    public function AdminInvoiceDownload($order_id)
    {

        $order = MainOrder::with('customerInfo')->where('id', $order_id)->first();
        $orderItem = OrderItem::with('productInfo', 'productInfo.imagesProduct', 'stockInfo')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();
        $currency = env('currency', '৳');
        $pdf = Pdf::loadView('admin.order.invoice', compact('order', 'orderItem', 'currency'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);
        return $pdf->download($order->invoice_no . '.pdf');
    }
}
