<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'price' => ['required'],
            'instock' => ['required'],
        ]);
        $newproduct = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'instock' => $request->instock,
        ]);
        return new ProductResource($newproduct);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'name' => ['required'],
            'price' => ['required'],
            'instock' => ['required'],
        ]);

        $product = Product::find($request->id);

        if ($product) {
            $product->update($data);
            $productnew = Product::find($request->id);
            return new ProductResource($productnew);
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

        $product = Product::findOrFail($request->id);

        if ($product) {
            $product->delete();
            return new ProductResource($product);
        } else {
            return response()->json([
                'error' => "Can't find customer",
            ]);
        }
    }

    public function search(Request $request)
    {
        $searchParam = $request->query('s');
        $products = Product::query()->where('name', 'LIKE', "%{$searchParam}%")->get();
        return response()->json([
            'customers' => $products,
        ]);
    }
}
