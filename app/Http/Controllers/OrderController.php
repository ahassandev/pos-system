<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(\App\Models\Order::with('customer')->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $total = 0;
            $items = [];

            // Find or create customer if name provided
            $customerId = $request->customer_id;
            if (!$customerId && $request->customer_name) {
                $customer = \App\Models\Customer::firstOrCreate(
                    ['name' => $request->customer_name],
                    ['phone' => $request->customer_phone]
                );
                $customerId = $customer->id;
            }

            foreach ($request->items as $item) {
                $product = \App\Models\Product::lockForUpdate()->find($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items" => ["Out of stock for product: {$product->name}"]
                    ]);
                }

                $product->decrement('stock', $item['quantity']);
                $total += $product->price * $item['quantity'];

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];
            }

            $subtotal = $total;
            $discountPercentage = (float) $request->discount_percentage;
            $discountAmount = ($subtotal * $discountPercentage) / 100;
            $finalTotal = $subtotal - $discountAmount;

            $order = \App\Models\Order::create([
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'total' => $finalTotal,
                'invoice_number' => 'INV-' . strtoupper(\Illuminate\Support\Str::random(8)),
            ]);

            $order->items()->createMany($items);

            return response()->json($order->load('items.product'), 201);
        });
    }

    public function show(\App\Models\Order $order)
    {
        return response()->json($order->load(['customer', 'items.product']));
    }

    public function stats()
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[$date] = [
                'date' => $date,
                'day_name' => now()->subDays($i)->format('D'),
                'total' => 0
            ];
        }

        $sales = \App\Models\Order::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach ($sales as $sale) {
            if (isset($days[$sale->date])) {
                $days[$sale->date]['total'] = (float) $sale->total;
            }
        }

        return response()->json([
            'total_sales_today' => (float) \App\Models\Order::whereDate('created_at', today())->sum('total'),
            'total_products' => (int) \App\Models\Product::count(),
            'total_customers' => (int) \App\Models\Customer::count(),
            'revenue_chart' => array_values($days),
        ]);
    }
}
