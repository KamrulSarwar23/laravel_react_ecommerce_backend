<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductReviewController extends Controller
{

    public function ReviewList(string $id){

        $reviews = ProductReview::with('user')->where('product_id', $id)->orderBy('created_at', 'DESC')->get();
        $reviewCount = ProductReview::where('product_id', $id)->count();
        $ratingCount = ProductReview::where('product_id', $id)->avg('rating');

        return response()->json([
            'status' => 200,
            'data' =>  $reviews,
            'reviewCount' => $reviewCount,
            'ratingCount' => $ratingCount
        ], 200);
    }


    public function StoreReview(Request $request, string $id){

        $validator = Validator::make($request->all(), [
            'rating' => 'required',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $product = Product::find($id);

        $review = new ProductReview();
        $review->user_id = Auth::user()->id;
        $review->product_id = $product->id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();


        return response()->json([
            'status' => 200,
            'message' => 'Review Added',
        ], 200);
    }
}
