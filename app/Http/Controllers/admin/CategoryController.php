<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    // This method will return all the category
    public function index()
    {
        $categories = Category::orderBy('created_at', 'DESC')->get();


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

    // This method will store a category in db
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
           'name' => 'required|unique:categories'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $category = new Category();
        $category->name = $request->name;
        $category->status = $request->status;
        $category->save();

        return response()->json([
            'status' => 200,
            'message' => 'Category Created Successfully',
            'data' => $category
        ], 200);
    }

    // This method will return a single category
    public function show(string $id)
    {
        $category = Category::find($id);

        if ($category == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Category Not Found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $category
        ], 200);
    }


    // This method will update a single category
    public function update(Request $request, string $id) {


        $category = Category::find($id);

        if ( $category == null) {

            return response()->json([
                'status' => 404,
                'message' => 'Category Niot Found',
                'data' => []
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => "required|unique:categories,name,$id"
         ]);

         if ($validator->fails()) {
             return response()->json([
                 'status' => 400,
                 'errors' => $validator->errors()
             ], 400);
         }

         $category->name = $request->name;
         $category->status = $request->status;
         $category->save();

         return response()->json([
             'status' => 200,
             'message' => 'Category Updated Successfully',
             'data' => $category
         ], 200);
    }

    // This method will delete a single category
    public function destroy(string $id) {

        $category = Category::find($id);

        if ($category == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Category Not Found',
            ], 404);
        }

        if (count($category->products) > 0) {
            return response()->json([
                'status' => 400,
                'message' => "Can't Delete! This Category Has Products",
            ], 400);
        }

        $category->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Category Deleted Successfully'
        ], 200);

    }
}
