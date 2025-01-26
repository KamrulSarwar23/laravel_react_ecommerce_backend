<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{

    // This method will return all the brand
    public function index()
    {
        $brands = Brand::orderBy('created_at', 'DESC')->get();


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

    // This method will store a brand in db
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:brands'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->status = $request->status;
        $brand->save();

        return response()->json([
            'status' => 200,
            'message' => 'Brand Created Successfully',
            'data' => $brand
        ], 200);
    }

    // This method will return a single Brand
    public function show(string $id)
    {
        $brand = Brand::find($id);

        if ($brand == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Brand Not Found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $brand
        ], 200);
    }


    // This method will update a single brand
    public function update(Request $request, string $id)
    {


        $brand = Brand::find($id);

        if ($brand == null) {

            return response()->json([
                'status' => 404,
                'message' => 'Brand Niot Found',
                'data' => []
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => "required|unique:brands,name,$id"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $brand->name = $request->name;
        $brand->status = $request->status;
        $brand->save();

        return response()->json([
            'status' => 200,
            'message' => 'Brand Updated Successfully',
            'data' => $brand
        ], 200);
    }

    // This method will delete a single category
    public function destroy(string $id)
    {

        $brand = Brand::find($id);

        if ($brand == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Brand Not Found',
            ], 404);
        }

        if (count($brand->products) > 0) {
            return response()->json([
                'status' => 400,
                'message' => "Can't Delete! This Brand Has Products",
            ], 400);
        }

        $brand->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Brand Deleted Successfully'
        ], 200);
    }
}
