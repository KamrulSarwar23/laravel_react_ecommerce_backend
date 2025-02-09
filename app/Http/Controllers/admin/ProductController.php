<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with(['ProductImages', 'ProductSizes', 'category', 'brand'])->orderBy('created_at', 'DESC')->paginate(10);
        return response()->json([
            'status' => 200,
            'data' => $products
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
        $product->colors = $request->colors;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->save();


        if (!empty($request->sizes)) {

            foreach ($request->sizes as $size) {
                $productSize = new ProductSize();
                $productSize->size_id = $size;
                $productSize->product_id = $product->id;
                $productSize->save();
            }
        }


        if (!empty($request->gallery)) {

            foreach ($request->gallery as $key => $tempImageId) {

                $tempImage = TempImage::find($tempImageId);

                $extArray = explode('.', $tempImage->name);
                $ext = end($extArray);

                $imageName = $product->id . '-' . time() . '-' . rand() . '.' . $ext;

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

        $product = Product::with(['ProductImages', 'ProductSizes'])->find($id);

        if ($product == null) {
            return response()->json([
                'status' => 400,
                'message' => 'Product Not Found'
            ], 400);
        }

        $productSizes = $product->ProductSizes()->pluck('size_id');


        return response()->json([
            'status' => 200,
            'data' => $product,
            'productSizes' => $productSizes
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
        $product->colors = $request->colors;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->save();


        if (!empty($request->sizes)) {

            ProductSize::where('product_id', $product->id)->delete();

            foreach ($request->sizes as $size) {
                $productSize = new ProductSize();
                $productSize->size_id = $size;
                $productSize->product_id = $product->id;
                $productSize->save();
            }
        } elseif (empty($request->sizes)) {
            ProductSize::where('product_id', $product->id)->delete();
        }

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

        if ($product->ProductImages) {

            foreach ($product->ProductImages as $productImage) {

                File::delete(public_path('uploads/products/small/' . $productImage->image));
                File::delete(public_path('uploads/products/large/' . $productImage->image));
            }
        }



        $product->delete();

        return response()->json([
            'status' => 200,
            'data' => 'Product Deleted Successfully'
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


    public function saveProductImage(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $image = $request->file('image');
        $imageName = $request->product_id . '-' . time() . '-' . rand() . '.' . $image->extension();

        // Large Thumbnai
        $manager = new ImageManager(Driver::class);
        $img = $manager->read($image->getPathName());
        $img->scaleDown(1200);
        $img->save(public_path('uploads/products/large/') . $imageName);

        // Small Thumbnai
        $manager = new ImageManager(Driver::class);
        $img = $manager->read($image->getPathName());
        $img->coverDown(400, 460);
        $img->save(public_path('uploads/products/small/') . $imageName);


        $productImage = new ProductImage();
        $productImage->image = $imageName;
        $productImage->product_id = $request->product_id;
        $productImage->save();


        return response()->json([
            'status' => 200,
            'message' => 'Image Upload Successfully',
            'data' => $productImage
        ], 200);
    }


    public function updateDefaultImage(Request $request)
    {
        $product = Product::find($request->product_id);
        $product->image = $request->image;
        $product->save();

        return response()->json([
            'status' => 200,
            'message' => 'Product default image has been changed'
        ]);
    }


    public function removeImageWhileUpdate(string $id)
    {
        $productImage = ProductImage::find($id);

        if ($productImage == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Image Not Found'
            ]);
        }

        File::delete(public_path('uploads/products/small/' . $productImage->image));

        File::delete(public_path('uploads/products/large/' . $productImage->image));

        $productImage->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Image Deleted Successfully'
        ]);
    }

    public function changeProductStatus(string $id, Request $request)
    {

        $product = Product::find($id);
        $product->status = $request->status == true ? 1 : 0;
        $product->save();

        return response()->json([
            'status' => 200,
            'message' => 'Status Updated Successfully'
        ]);
    }


    public function changeProductIsFeatured(string $id, Request $request)
    {
        $product = Product::find($id);
        $product->is_featured = $request->is_featured == true ? 'yes' : 'no';
        $product->save();

        return response()->json([
            'status' => 200,
            'message' => 'Status Updated Successfully'
        ]);
    }
}
