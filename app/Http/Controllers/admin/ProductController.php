<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ProductController extends Controller
{

    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => 200,
            'data' => $products
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



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku',
            'status' => 'required',
            'is_featured' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $product = new Product();
        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->sku = $request->sku;
        $product->qty = $request->qty;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->save();


        if (!empty($request->gallery)) {

            foreach ($request->gallery as $key => $tempImageId) {

                $tempImage = TempImage::find($tempImageId);

                $extArray = explode('.', $tempImage->name);
                $ext = end($extArray);

                $imageName = $product->id . '-' . time() . '-'. rand() . '.'. $ext;

                // Large Thumbnai
                $manager = new ImageManager(Driver::class);
                $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                $img->scaleDown(1200);
                $img->save(public_path('uploads/products/large/') . $imageName);

                // Small Thumbnai
                $manager = new ImageManager(Driver::class);
                $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                $img->coverDown(400, 460);
                $img->save(public_path('uploads/products/small/') . $imageName);


                $product_image = new ProductImage();
                $product_image->image = $imageName;
                $product_image->product_id = $product->id;
                $product_image->save();

                if ($key == 0) {
                    $product->image = $imageName;
                    $product->save();
                }
            }
        }

        return response()->json([
            'status' => 200,
            'data' => $product,
            'message' => "Product Created Successfully"
        ], 200);
    }


    public function show(string $id)
    {

        $product = Product::find($id);

        if ($product == null) {
            return response()->json([
                'status' => 400,
                'message' => 'Product Not Found'
            ], 400);
        }


        return response()->json([
            'status' => 200,
            'data' => $product
        ], 200);
    }

    public function update(Request $request, string $id)
    {

        $product = Product::find($id);

        if ($product == null) {
            return response()->json([
                'status' => 400,
                'message' => 'Product Not Found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku,' . $id . ',id',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->sku = $request->sku;
        $product->qty = $request->qty;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->save();

        return response()->json([
            'status' => 200,
            'message' => "Product Updated Successfully"
        ], 200);
    }

    public function destroy(string $id)
    {

        $product = Product::find($id);

        if ($product == null) {
            return response()->json([
                'status' => 400,
                'message' => 'Product Not Found'
            ], 400);
        }

        $product->delete();

        return response()->json([
            'status' => 200,
            'data' => 'Product Deleted Successfully'
        ], 200);
    }
}
