<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => ['required'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*.id' => ['required', 'integer', 'exists:products,id'],
            'product_ids.*.quantity' => ['required', 'integer', 'min:1'],
        ]);
        $products = $request->product_ids ?? [];
        $totalPrice = 0;
        $productDetails = [];
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if (!$productModel) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $quantity = $product['quantity'];

            if ($productModel->instock < $quantity) {
                return response()->json(['error' => 'Not enough stock for product ID: ' . $product['id']], 400);
            }

            $price = $productModel->price;
            $totalPrice += $price * $quantity;

            $productModel->decrement('instock', $quantity);
            // Chuẩn bị dữ liệu để đính kèm vào bảng pivot
            $productDetails[$product['id']] = ['quantity' => $quantity];
        }

        $neworder = Order::create([
            'customer_id' => $request->customer_id,
            'totalprice' => $totalPrice,
        ]);
        $neworder->products()->attach($productDetails);
        return new OrderResource($neworder);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'customer_id' => ['required'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*.id' => ['required', 'integer', 'exists:products,id'],
            'product_ids.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = Order::findOrFail($request->id);

        if ($order) {
            $products = $request->product_ids ?? [];
            $totalPrice = 0;
            $productDetails = [];

            foreach ($products as $product) {
                $productModel = Product::find($product['id']);
                $price = $productModel->price;
                $quantity = $product['quantity'];
        
                $totalPrice += $price * $quantity;
        
                // Chuẩn bị dữ liệu để đính kèm vào bảng pivot
                $productDetails[$product['id']] = ['quantity' => $quantity];
            }

            $order->update([
                'id' => $request->id,
                'customer_id' => $request->customer_id,
                'totalprice' => $totalPrice,
            ]);

            $order->products()->detach();
            $order->products()->attach($productDetails);

            return new OrderResource($order);
        } else {
            return response()->json([
                'error' => "Can't find product",
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $order = Order::findOrFail($request->id);

        if ($order) {
            $order->delete();
            return new OrderResource($order);
        } else {
            return response()->json([
                'error' => "Can't find customer",
            ]);
        }
    }

    public function search(Request $request)
    {
        $searchParam = $request->query('s');
        $searchType = $request->query('type');
        $minPrice = $request->query('minp');
        $maxPrice = $request->query('maxp');
        switch ($searchType) {
            case "none":
                $orders = Order::query()->where('id', 'LIKE', "%{$searchParam}%")
                ->get();
                return response()->json([
                    'orders' => $orders,
                ]);
            case "date":
                $orders = Order::query()
                ->whereDate('created_at', $searchParam)
                ->get();
                return response()->json([
                    'orders' => $orders,
                ]);
            case "price":
                $orders = Order::query()
                ->when($minPrice && $maxPrice, function ($query) use ($minPrice, $maxPrice) {
                    return $query->whereBetween('totalprice', [$minPrice, $maxPrice]);
                })
                ->get();
                return response()->json([
                    'orders' => $orders,
                ]);
            case "quantity":
                $order = Order::with(['products' => function ($query) {
                    $query->select('products.id', 'products.name', 'order_products.quantity');
                }])->find($searchParam);
                
                if (!$order) {
                    return response()->json(['error' => 'Order not found'], 404);
                }
                
                return response()->json([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'products' => $order->products->map(function($product) {
                        return [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $product->pivot->quantity,
                        ];
                    }),
                ]);
            default:
                return response()->json([
                    'error' => "invalid type search",
                ]);
        }
    }
}
