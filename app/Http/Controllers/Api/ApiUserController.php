<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ApiUserController extends Controller
{
    public function editPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "password" => [
                "required",
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }
        try {
            User::where('id', $request->id)->update([
                'password' => Hash::make($request->password),
            ]);

            $updatedPassword = User::find($request->id);

            return response()->json([
                'success' => true,
                'message' => 'Update Password Successfully!',
                'data' => $updatedPassword,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed To Update Password!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function getUserById(Request $request)
    {
        try {
            $getUserById = User::find($request->id);

            return response()->json([
                'success' => true,
                'message' => 'Get Data User Successfully!',
                'data' => $getUserById,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed To Get Data User!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|unique:users|email",
            "profession" => "required",
            "no_hp" => "required|min:9|max:13",
            "password" => [
                "required",
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'profession' => $request->profession,
                'no_hp' => $request->no_hp,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Register Berhasil!',
                'data' => $user,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Register Gagal',
                'error' => $error->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        $data = [
            "email" => $request->email,
            "password" => $request->password,
        ];

        Auth::attempt($data);
        if (Auth::check()) {
            $userId = Auth::user()->id;
            $user = User::where('id', $userId)->first();

            $token = $user->createToken('auth_token')->plainTextToken;
            $cookie = cookie('token', $token, 60 * 1);

            // menggunakan format json
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Login Berhasil',
                    'data' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ],
                200
            )->withCookie($cookie);
        } else {
            $user = null;
            // menggunakan format json
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Data Invalid',
                    'data' => null
                ],
                500
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            $removeToken = $request->user()->currentAccessToken()->delete();
            $cookie = cookie()->forget('token');

            if ($removeToken) {
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Logout Berhasil',
                        'data' => null
                    ],
                    200
                )->withCookie($cookie);
            }
        } catch (Exception $error) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Logout Gagal',
                    'data' => $error->getMessage()
                ],
                500
            );
        }
    }

    public function editProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email",
            "profession" => "required",
            "no_hp" => "required|min:9|max:13",
            // "gambar" => "image|mimes:jpeg,png,jpg|max:2048",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            User::where('id', $request->id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'profession' => $request->profession,
                'no_hp' => $request->no_hp,
            ]);

            $updatedUser = User::find($request->id);

            return response()->json([
                'success' => true,
                'message' => 'Profile Updated Successfully',
                'data' => $updatedUser,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to Update Profile',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
