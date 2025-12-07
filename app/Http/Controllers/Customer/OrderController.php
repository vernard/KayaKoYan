<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = $user->customerOrders()
            ->with(['listing.images', 'worker', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(10);

        $activeOrders = $user->customerOrders()->active()->count();
        $completedOrders = $user->customerOrders()->completed()->count();

        return view('customer.orders.index', compact('orders', 'activeOrders', 'completedOrders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['listing.images', 'worker.workerProfile', 'payments', 'delivery.files', 'chatMessages.sender']);

        return view('customer.orders.show', compact('order'));
    }

    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::create([
            'listing_id' => $listing->id,
            'customer_id' => auth()->id(),
            'worker_id' => $listing->user_id,
            'quantity' => 1,
            'unit_price' => $listing->price,
            'total_price' => $listing->price,
            'status' => OrderStatus::PendingPayment,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('customer.payment.create', $order)
            ->with('success', 'Order placed successfully! Please submit payment.');
    }

    public function accept(Order $order): RedirectResponse
    {
        $this->authorize('accept', $order);

        if ($order->status !== OrderStatus::Delivered) {
            return back()->with('error', 'This order cannot be accepted.');
        }

        $order->transitionTo(OrderStatus::Completed);

        return back()->with('success', 'Order completed successfully!');
    }
}
