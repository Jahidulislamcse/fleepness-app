<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\MainOrder;
use App\Models\Product;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function index()
    {

        $data['user'] = auth()->user();
        $data['pending_order'] = MainOrder::where('vendor_id', Auth::id())
            ->where('status', 'pending')->count();
        $data['total_sales'] = MainOrder::where('vendor_id', Auth::id())
            ->where('status', 'deliverd')->sum('amount');
        $data['total_products'] = Product::where('user_id', Auth::id())->count();
        return view('vendor.index', $data);
    }
}
