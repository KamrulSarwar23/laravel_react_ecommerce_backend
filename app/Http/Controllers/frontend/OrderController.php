<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\AddToCart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{

    public function getShipping()
    {

        $shipping = ShippingMethod::orderBy('created_at', 'DESC')->get();


        if ($shipping->isEmpty()) {
            return response()->json([
                'status' => 400,
                'message' => 'Shipping Not Found'
            ], 400);
        }

        return response()->json([
            'status' => 200,
            'data' => $shipping
        ], 200);
    }


    public function CashOnDelivery(Request $request)
    {
        // Start the transaction
        DB::beginTransaction();

        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'address' => 'required',
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->errors()
                ], 400);
            }

            // Get cart products
            $cartProducts = AddToCart::where('user_id', Auth::user()->id)->get();

            if ($cartProducts->isEmpty()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Product Not Found'
                ], 400);
            }

            // Calculate subTotal
            $subTotal = AddToCart::where('user_id', Auth::user()->id)
                ->selectRaw('SUM(price * quantity) as total_price')
                ->pluck('total_price')
                ->first();

            // Calculate total quantity of products
            $productQty = AddToCart::where('user_id', Auth::user()->id)->select('quantity')->sum('quantity');

            // Calculate the total payable amount (with shipping cost)
            $totalPayable = $subTotal + $request->shipping_amount;

            // Create the order
            $order = new Order();
            $order->invoice_id = time() . '-' . rand(1, 999999);
            $order->user_id = Auth::user()->id;
            $order->sub_total = $subTotal;
            $order->amount = $totalPayable;
            $order->product_qty = $productQty;
            $order->payment_method = 'cod';
            $order->shipping_method = $request->shipping_method;
            $order->shipping_amount = $request->shipping_amount;
            $order->payment_status = 0;
            $order->order_status = 'pending';
            $order->save();


            // Create order items for each cart product
            foreach ($cartProducts as $product) {
                $itemOrder = new OrderItem();
                $itemOrder->order_id = $order->id;
                $itemOrder->product_id = $product->product_id;
                $itemOrder->product_name = $product->title;
                $itemOrder->unit_price = $product->price;
                $itemOrder->qty = $product->quantity;
                $itemOrder->image = $product->image;
                $itemOrder->size = $product->size;
                $itemOrder->color = $product->color;
                $itemOrder->save();

                $manageQty = Product::where('id', $product->product_id)->get();

                foreach ($manageQty as $productQty) {
                    $productQty->qty = $productQty->qty - $product->quantity;
                    $productQty->save();
                }
            }

            // Create the shipping address
            $address = new ShippingAddress();
            $address->order_id = $order->id;
            $address->address = $request->address;
            $address->name = $request->name;
            $address->email = $request->email;
            $address->phone = $request->phone;
            $address->city = $request->city;
            $address->save();

            // Create a transaction record
            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->transaction_id = time() . '-' . rand(1, 999999);
            $transaction->payment_method = 'cod';
            $transaction->payment_status = 0;
            $transaction->amount = $totalPayable;
            $transaction->save();


            foreach ($cartProducts  as $product) {
                $product->delete();
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Order Placed Successfully',
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong, please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function CustomerOrderList()
    {
        $orderList = Order::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $orderList
        ]);
    }


    public function CustomerInvoice(string $id)
    {

        $orderItems = Order::with('orderItems', 'shippingAddress')->where('user_id', Auth::user()->id)->find($id);

        return response()->json([
            'status' => 200,
            'data' => $orderItems
        ]);
    }
}
