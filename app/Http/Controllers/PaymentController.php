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


    public function storeWithdrawRequest(Request $request)
    {
        // Validate the request
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . Auth::user()->balance],
        ]);

        // Get authenticated user
        $user = Auth::user();

        // Create a new bill entry
        $bill = new Bill();
        $bill->user_id = $user->id;
        $bill->amount = $request->amount;
        $bill->status = 'pending';
        $bill->save();

        return redirect()->back()->with('success', 'Withdrawal request submitted successfully.');
    }

    public function paymentHistory()
    {
        $bills = Bill::where('user_id', Auth::id())->get();
        return view('vendor.payment.history', compact('bills'));
    }

    public function PaymentRequests()

    {
        $bills = Bill::where('status', 'pending')->get();
        return view('admin.payment.requests', compact('bills'));
    }

    public function AdminPaymentHistory()

    {
        $bills = Bill::where('status', 'completed')->get();
        return view('admin.payment.history', compact('bills'));
    }

    public function update(Request $request, $billId)
    {
        $bill = Bill::findOrFail($billId);
        $bill->status = $request->status;
        $bill->transaction_id = $request->transaction_id;
        $bill->payment_media = $request->payment_media;
        $bill->save();

        return redirect()->back()->with('success', 'Payment details updated successfully.');
    }
}
