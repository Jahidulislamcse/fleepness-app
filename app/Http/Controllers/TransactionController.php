<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$user->balance],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
        ]);

        $user->balance -= $validated['amount'];
        $user->save();

        $transaction = \App\Models\Transaction::query()->create([
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

    public function PaymentRequests(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $bills = \App\Models\Transaction::query()->where('status', 'pending')->get();

        return view('admin.payment.requests', ['bills' => $bills]);
    }

    public function update(Request $request, $billId)
    {
        $bill = \App\Models\Transaction::query()->findOrFail($billId);
        $bill->status = 'approved';
        $bill->transaction_id = $request->transaction_id;
        $bill->save();

        $bill->notifySellerAboutWithdrawalApproval();

        return back()->with('success', 'Payment details updated successfully.');
    }

    public function AdminPaymentHistory(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $status = $request->query('status');

        $query = Transaction::with(['user', 'paymentMethod']);

        if (in_array($status, ['approved', 'rejected'])) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['approved', 'rejected']);
        }

        $bills = $query->latest()->get();

        return view('admin.payment.history', ['bills' => $bills, 'status' => $status]);
    }

    public function reject(Request $request, $billId)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $bill = \App\Models\Transaction::query()->findOrFail($billId);
        $bill->update([
            'status' => 'rejected',
            'note' => $request->reason,
        ]);

        return back()->with('success', 'Payment request rejected successfully.');
    }
}
