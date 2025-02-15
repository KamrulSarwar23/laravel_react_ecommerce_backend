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
use Stripe\Stripe;
use Stripe\Checkout\Session;


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

    public function stripePayment(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'address' => 'required',
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'city' => 'required',
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

            // Calculate total payable amount
            $subTotal = AddToCart::where('user_id', Auth::user()->id)
                ->selectRaw('SUM(price * quantity) as total_price')
                ->pluck('total_price')
                ->first();

            $totalPayable = ($subTotal + 60) * 100; // Convert to cents

            // Store order data temporarily in the session
            $orderData = [
                'address' => $request->address,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'city' => $request->city,
                'sub_total' => $subTotal,
                'total_payable' => $totalPayable,
                'cart_products' => $cartProducts,
            ];
            session(['temp_order' => $orderData]); // Store in session

            // Create Stripe Checkout Session
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Order Payment',
                        ],
                        'unit_amount' => $totalPayable,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://localhost:5173/checkout/success?session_id={CHECKOUT_SESSION_ID}', // Redirect to React frontend
                'cancel_url' => 'http://localhost:5173/checkout/cancel',
            ]);

            // Return the session ID to the frontend
            return response()->json([
                'status' => 200,
                'sessionId' => $session->id,
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Stripe Error: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong, please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function verifyPayment(Request $request)
    {
        DB::beginTransaction();

        try {
            // Set Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));

            // Retrieve the session ID from the request
            $sessionId = $request->input('session_id');

            if (!$sessionId) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Session ID is required.',
                ], 400);
            }

            // Retrieve the Checkout Session from Stripe
            $session = Session::retrieve($sessionId);

            // Check if the payment was successful
            if ($session->payment_status !== 'paid') {
                return response()->json([
                    'status' => 400,
                    'message' => 'Payment was not successful.',
                ], 400);
            }

            // Retrieve temporary order data from the session
            $orderData = session('temp_order');

            if (!$orderData) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Order data not found.',
                ], 400);
            }

            // Extract order data
            $address = $orderData['address'];
            $name = $orderData['name'];
            $email = $orderData['email'];
            $phone = $orderData['phone'];
            $city = $orderData['city'];
            $subTotal = $orderData['sub_total'];
            $totalPayable = $orderData['total_payable'] / 100; // Convert back to dollars
            $cartProducts = $orderData['cart_products'];

            // Create order
            $order = new Order();
            $order->invoice_id = time() . '-' . rand(1, 999999);
            $order->user_id = Auth::user()->id;
            $order->sub_total = $subTotal;
            $order->amount = $totalPayable;
            $order->product_qty = AddToCart::where('user_id', Auth::user()->id)->sum('quantity');
            $order->payment_method = 'stripe';
            $order->payment_status = 1; // Mark as paid
            $order->order_status = 'pending';
            $order->save();

            // Create order items
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

                // Update product quantity
                Product::where('id', $product->product_id)->decrement('qty', $product->quantity);
            }

            // Save shipping address
            $address = new ShippingAddress();
            $address->order_id = $order->id;
            $address->address = $orderData['address'];
            $address->name = $orderData['name'];
            $address->email = $orderData['email'];
            $address->phone = $orderData['phone'];
            $address->city = $orderData['city'];
            $address->save();

            // Create transaction record
            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->transaction_id = $session->id; // Use Stripe session ID
            $transaction->payment_method = 'stripe';
            $transaction->payment_status = 1; // Mark as paid
            $transaction->amount = $totalPayable;
            $transaction->save();

            // Clear cart
            foreach ($cartProducts as $product) {
                $product->delete();
            }

            // Clear temporary order data from the session
            session()->forget('temp_order');

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Order created successfully.',
                'order' => $order,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Payment Verification Error: " . $e->getMessage(), ['exception' => $e]);

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

        $orderItems = Order::with('orderItems')->where('user_id', Auth::user()->id)->find($id);

        return response()->json([
            'status' => 200,
            'data' => $orderItems
        ]);
    }
}
