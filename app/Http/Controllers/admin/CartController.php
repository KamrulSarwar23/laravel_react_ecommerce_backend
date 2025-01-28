<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
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

        $cart = Cart::add( $productId, $product->title, $quantity, $product->price, ['size' => $size, 'color' => $color]);

        return response()->json([
            'status' => 200,
            'message' => 'Product added to cart',
            'data' => $cart
        ]);
    }

    public function getCart()
    {
        $cart = Cart::content();
        dd($cart);
        // return response()->json(['status' => 200, 'cart' => $cart]);
    }

}
