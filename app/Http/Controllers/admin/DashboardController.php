<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ShippingMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboardAnalytics()
    {

        $users = User::where('role', 'customer')->count();
        $products = Product::count();
        $orders = Order::count();
        $categories = Category::count();
        $brands = Brand::count();
        $transactions = Transaction::count();
        $shipping = ShippingMethod::count();
        $reviews = ProductReview::count();
        $totalProductsSale = OrderItem::sum('qty');

        return response()->json([
            'status' => 200,
            'users' => $users,
            'products' => $products,
            'orders' => $orders,
            'categories' => $categories,
            'brands' => $brands,
            'transactions' => $transactions,
            'shipping' => $shipping,
            'reviews' => $reviews,
            'totalProductsSale' => $totalProductsSale
        ], 200);
    }


    public function userList()
    {

        $userlist = User::where('role', 'customer')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $userlist
        ]);
    }

    public function OrderList()
    {
        $orderList = Order::orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $orderList
        ]);
    }

    public function TransactionList()
    {

        $transactionList = Transaction::with('order')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $transactionList
        ]);
    }


    public function Invoice(string $id)
    {

        $orderItems = Order::with('orderItems', 'shippingAddress')->orderBy('created_at', 'DESC')->find($id);

        return response()->json([
            'status' => 200,
            'data' => $orderItems
        ]);
    }


    public function ChangePaymentStatus(string $id, Request $request)
    {

        $order = Order::find($id);
        $order->payment_status = $order->payment_status == 0 ? 1 : 0;
        $order->save();

        $transaction = Transaction::where('order_id', $id)->first();
        $transaction->payment_status = $transaction->payment_status == 0 ? 1 : 0;
        $transaction->save();

        return response()->json([
            'status' => 200,
            'message' => "Payment Status Changed"
        ]);
    }

    public function ChangeOrderStatus(string $id)
    {
        $order = Order::find($id);
        $order->order_status = $order->order_status == 'pending' ? 'delivered' : 'pending';
        $order->save();

        return response()->json([
            'status' => 200,
            'message' => "Order Status Changed"
        ]);
    }
}
