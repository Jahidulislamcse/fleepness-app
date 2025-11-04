<?php

namespace App\Http\Controllers;

use App\Models\MainOrder;
use Illuminate\Http\Request;
use App\Models\Bill;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function request()
    {
        $total_pending_amount = Bill::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->sum('amount');

        $current_balance = auth()->user()->balance;
        $max_withdrawable = $current_balance - $total_pending_amount;

        return view('vendor.payment.request', compact('max_withdrawable'));
    }


    public function paymentHistory()
    {
        $bills = Bill::where('user_id', Auth::id())->get();
        return view('vendor.payment.history', compact('bills'));
    }


}
