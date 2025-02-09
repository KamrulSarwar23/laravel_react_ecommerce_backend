<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

        return response()->json([
            'status' => 200,
            'users' => $users,
            'products' => $products,
            'orders' => $orders
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

        $transactionList = Transaction::orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $transactionList
        ]);
    }


    public function Invoice(string $id)
    {

        $orderItems = Order::with('orderItems')->orderBy('created_at', 'DESC')->find($id);

        return response()->json([
            'status' => 200,
            'data' => $orderItems
        ]);
    }

    public function ChangePaymentStatus(string $id){

        $order = Order::find($id);
        $order->payment_status = $order->payment_status == 0 ? 1 : 0;
        $order->save();

        return response()->json([
            'status' => 200,
            'message' => "Payment Status Changed"
        ]);
    }

    public function ChangeOrderStatus(string $id){
        $order = Order::find($id);
        $order->order_status = $order->order_status == 'pending' ? 'delivered' : 'pending';
        $order->save();

        return response()->json([
            'status' => 200,
            'message' => "Order Status Changed"
        ]);
    }
}
