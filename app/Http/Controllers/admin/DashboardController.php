<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboardAnalytics(){

        $users = User::where('role', 'customer')->count();
        $products = Product::count();

        return response()->json([
            'status' => 200,
            'users' => $users,
            'products' => $products
        ], 200);
    }
}
