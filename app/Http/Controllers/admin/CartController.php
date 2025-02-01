<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AddToCart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CartController extends Controller
{

    public function addToCart(Request $request)
    {

        $product = Product::with('sizes')->find($request->product_id);

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',

            'color' => [
                'nullable',
                'string',
                Rule::requiredIf($product && !empty($product->colors)),
            ],

            'size' => [
                'nullable',
                'string',
                Rule::requiredIf($product && $product->sizes->isNotEmpty()),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $cartItem = AddToCart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->where('size', $request->size)
            ->where('color', $request->color)
            ->first();

        if ($request->quantity > $product->qty) {
            return response()->json([
                'status' => 402,
                'message' => "Quantity Not Available"
            ], 402);
        }

        if ($cartItem) {

            // $cartItem->quantity += $request->quantity;
            // $cartItem->save();

            return response()->json([
                'status' => 401,
                'message' => "Product Already In The Cart"
            ], 401);

        } else {

            $cart = new AddToCart();
            $cart->user_id = Auth::id();
            $cart->product_id = $request->product_id;
            $cart->quantity = $request->quantity;
            $cart->color = $request->color;
            $cart->size = $request->size;
            $cart->image = $product->image;
            $cart->price = $product->price;
            $cart->category = $product->category->name;
            $cart->title = $product->title;
            $cart->brand = $product->brand->name;
            $cart->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product added to cart successfully!',
        ], 200);
    }

    public function getCart()
    {

        $cart =  AddToCart::where('user_id', Auth::user()->id)->get();
        return response()->json([
            'status' => 200,
            'cart' => $cart
        ], 200);
    }

    public function removeCart(string $id)
    {

        $product = AddToCart::find($id);
        $product->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Product Remove From Cart',
        ], 200);
    }

    public function updateCartQuantity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:add_to_carts,product_id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        $cartItem = AddToCart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            return response()->json([
                'status' => 200,
                'message' => 'Cart quantity updated successfully!',
            ]);
        }

        return response()->json([
            'status' => 404,
            'message' => 'Cart item not found!',
        ], 404);
    }

    public function cartCount()
    {

        $cartItem = AddToCart::where('user_id', Auth::id())->count();
        return response()->json([
            'status' => 200,
            'data' => $cartItem
        ], 200);
    }
}
