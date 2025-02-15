<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingMethodController extends Controller
{
    public function index()
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


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'method' => 'required|unique:shipping_methods',
            'amount' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $shipping = new ShippingMethod();
        $shipping->method = $request->method;
        $shipping->amount = $request->amount;
        $shipping->save();

        return response()->json([
            'status' => 200,
            'message' => 'Shipping Created Successfully',
        ], 200);
    }


    public function show(string $id)
    {
        $shipping = ShippingMethod::find($id);

        if ($shipping == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Shipping Not Found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $shipping
        ], 200);
    }


    public function update(Request $request, string $id)
    {


        $shipping = ShippingMethod::find($id);

        if ($shipping == null) {

            return response()->json([
                'status' => 404,
                'message' => 'Shipping Niot Found',
                'data' => []
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'method' => "required|unique:shipping_methods,method,$id,id",
            'amount' => 'required|integer'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $shipping->method = $request->method;
        $shipping->amount = $request->amount;
        $shipping->save();

        return response()->json([
            'status' => 200,
            'message' => 'Shipping Updated Successfully',
        ], 200);
    }


    public function destroy(string $id)
    {

        $shipping = ShippingMethod::find($id);

        if ($shipping == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Shipping Not Found',
            ], 404);
        }

        $shipping->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Shipping Deleted Successfully'
        ], 200);
    }
}
