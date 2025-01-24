<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class ProductController extends Controller
{
    public function latestProduct()
    {
        $products = Product::with(['ProductImages', 'ProductSizes'])->where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->limit(8)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => "Product Not Found"
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }


    public function featuredProduct()
    {
        $products = Product::with(['ProductImages', 'ProductSizes'])->where('status', 1)->where('is_featured', 'yes')
            ->orderBy('created_at', 'DESC')
            ->limit(8)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => "Product Not Found"
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }

    public function getAllProducts()
    {

        $products = Product::with(['ProductImages', 'ProductSizes'])->where('status', 1)
            ->orderBy('created_at', 'DESC')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => "Product Not Found"
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }


    public function productFilterByCategoryAndBrand(Request $request)
    {
        $categories = $request->input('categories', []);
        $brands = $request->input('brands', []);

        $query = Product::query();

        // Filter by categories
        if (!empty($categories)) {
            $query->whereIn('category_id', $categories);
        }

        // Filter by brands
        if (!empty($brands)) {
            $query->whereIn('brand_id', $brands);
        }

        $products = $query->where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => "No products found"
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }



    public function productDetails(string $id)
    {

        $productDetails = Product::with(['ProductImages', 'sizes'])
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if ($productDetails == null) {
            return response()->json([
                'message' => "Product Not Found"
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $productDetails
        ], 200);
    }

    public function getCategories()
    {
        $categories = Category::where('status', 1)->orderBy('created_at', 'DESC')->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 400,
                'message' => 'Category Not Found'
            ], 400);
        }

        return response()->json([
            'status' => 200,
            'data' => $categories
        ], 200);
    }


    public function getBrands()
    {
        $brands = Brand::where('status', 1)->orderBy('created_at', 'DESC')->get();


        if ($brands->isEmpty()) {
            return response()->json([
                'status' => 400,
                'message' => 'Brand Not Found'
            ], 400);
        }

        return response()->json([
            'status' => 200,
            'data' => $brands
        ], 200);
    }
}
