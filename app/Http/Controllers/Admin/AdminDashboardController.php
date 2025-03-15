<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainOrder;
use App\Models\Product;
use App\Models\User;
use Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {

        $data['pending_order'] = MainOrder::where('status', 'pending')
            ->count();
        $data['total_sales'] = MainOrder::where('status', 'deliverd')->where('payment_status', 'received')
            ->sum('amount');
        $data['total_products'] = Product::all()
            ->count();
        return view('admin.index', $data);
    }
}
