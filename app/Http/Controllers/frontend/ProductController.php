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


    public function getCategories()
    {
        $categories = Category::where('status', 1)->orderBy('name', 'ASC')->get();

        return response()->json([
            'status' => 200,
            'data' => $categories
        ], 200);
    }


    public function getBrands()
    {
        $brands = Brand::where('status', 1)->orderBy('name', 'ASC')->get();

        return response()->json([
            'status' => 200,
            'data' => $brands
        ], 200);
    }


    public function getAllProducts(Request $request)
    {

        $products = Product::where('status', 1)->orderBy('created_at', 'DESC');

        if (!empty($request->category)) {

            $categoryArray = explode(',', $request->category);

            $products->whereIn('category_id', $categoryArray);

        }

        if (!empty($request->brand)) {

            $brandArray = explode(',', $request->brand);

            $products->whereIn('brand_id', $brandArray);

        }

        $products = $products->paginate(9);


        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }



    public function categoryProduct(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found'
            ], 404);
        }

        $products = $category->products()->paginate(8);

        return response()->json([
            'status' => 200,
            'category' => $category->name,
            'data' => $products
        ]);
    }


    public function latestProduct()
    {
        $products = Product::where('status', 1)
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
        $products = Product::where('status', 1)->where('is_featured', 'yes')
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


    public function suggestedProducts(string $id)
    {

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => "Product not found"
            ], 404);
        }

        $categoryId = $product->category_id;

        $products = Product::where('category_id', $categoryId)
            ->where('id', '!=', $id)
            ->where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->limit(4)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => "No suggested products found"
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
}
