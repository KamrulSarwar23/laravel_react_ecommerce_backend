<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {

        $product = Product::find($request->product_id);

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'color' => 'nullable',
            'size' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $size = $request->size;
        $color = $request->color;

        $cart[] = [
            'id' => $productId,
            'title' => $product->title,
            'price' => $product->price,
            'image' => $product->image_url,
            'quantity' => $quantity,
            'size' => $size,
            'color' => $color,
        ];

        Session::put('cart', $cart);

        return response()->json([
            'status' => 200,
            'message' => 'Product added to cart',
            'data' => $cart
        ]);
    }

    public function getCart()
    {
        $cart = session()->get('cart');

        return response()->json(['status' => 200, 'cart' => $cart]);
    }

}
