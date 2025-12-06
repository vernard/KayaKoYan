<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(Order $order): View
    {
        $this->authorize('pay', $order);

        if ($order->status !== OrderStatus::PendingPayment) {
            abort(403, 'Payment has already been submitted for this order.');
        }

        $order->load('worker.workerProfile');

        return view('customer.orders.payment', compact('order'));
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('pay', $order);

        if ($order->status !== OrderStatus::PendingPayment) {
            return back()->with('error', 'Payment has already been submitted for this order.');
        }

        $validated = $request->validate([
            'method' => ['required', 'in:gcash,bank_transfer'],
            'reference_number' => ['required', 'string', 'max:255'],
            'proof' => ['required', 'image', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs/' . $order->id, 'public');

        Payment::create([
            'order_id' => $order->id,
            'method' => PaymentMethod::from($validated['method']),
            'amount' => $order->total_price,
            'reference_number' => $validated['reference_number'],
            'proof_path' => $proofPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Payment submitted! Please wait for the worker to verify.');
    }
}
