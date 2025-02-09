<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function authenticate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = User::find(Auth::user()->id);

            if ($user->role == 'admin') {
                $token = $user->createToken('token')->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role
                ]);
            } else {

                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized'
                ]);
            }
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Either password or email is incorrect'
            ]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => "Logged out",
        ], 200);
    }

     public function getUser()
     {
         $user = Auth::user();
         return response()->json([
            'status' => 200,
            'data' => $user
        ], 200);
     }

     public function updateProfile(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        $user->name = $request->name;
        $user->save();


        return response()->json([
            'status' => 200,
            'message' => "Profile Updated Successfully"
        ], 200);

     }

     public function updatePassword(Request $request)
     {
         $request->validate([
             'currentPassword' => 'required',
             'newPassword' => 'required|min:5|confirmed',
         ]);

         $user = Auth::user();

         // Check if current password matches
         if (!Hash::check($request->currentPassword, $user->password)) {
             throw ValidationException::withMessages([
                 'currentPassword' => ['The current password is incorrect.'],
             ]);
         }

         // Update password
         $user->update([
             'password' => Hash::make($request->newPassword),
         ]);

         return response()->json([
             'status' => 200,
             'message' => 'Password updated successfully.',
         ]);
     }

}
