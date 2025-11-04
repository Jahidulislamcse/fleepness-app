<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentMethod;
use App\Models\Transaction;

class TransactionController extends Controller
{

    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $user->balance],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
        ]);

        $user->balance -= $validated['amount'];
        $user->save();

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'payment_method_id' => $validated['payment_method_id'],
            'amount' => $validated['amount'],
            'type' => 'withdraw',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Withdrawal request submitted successfully.',
            'transaction' => $transaction,
        ], 201);
    }

    public function PaymentRequests()
    {
        $bills = Transaction::where('status', 'pending')->get();
        return view('admin.payment.requests', compact('bills'));
    }

    public function update(Request $request, $billId)
    {
        $bill = Transaction::findOrFail($billId);
        $bill->status = 'approved';
        $bill->transaction_id = $request->transaction_id;
        $bill->save();

        return redirect()->back()->with('success', 'Payment details updated successfully.');
    }

    public function AdminPaymentHistory(Request $request)
    {
        $status = $request->query('status'); 

        $query = Transaction::with(['user', 'paymentMethod']);

        if (in_array($status, ['approved', 'rejected'])) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['approved', 'rejected']);
        }

        $bills = $query->latest()->get();

        return view('admin.payment.history', compact('bills', 'status'));
    }


    public function reject(Request $request, $billId)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $bill = Transaction::findOrFail($billId);
        $bill->update([
            'status' => 'rejected',
            'note' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Payment request rejected successfully.');
    }
}
