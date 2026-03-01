<?php
// app/Http/Controllers/API/User/AuthUserController.php

namespace App\Http\Controllers\API\User;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthUserController extends Controller
{
    /* ====== تسجيل جديد ====== */
    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email',
            'phone'    => 'required|string|unique:customers,phone',
            'password' => 'required|string|min:6',
            'img'      => 'nullable|image',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();

        if ($request->hasFile('img')) {
            $data['img'] = FileHelper::uploadImage($request->file('img'), 'uploads/customers');
        }

        $customer = Customer::create($data);
        $token    = $customer->createToken('user-token')->plainTextToken;

        return response()->json([
            'message'  => 'Registered successfully.',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ], 201);
    }

    /* ====== تسجيل الدخول ====== */
    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 403);
        }

        $token = $customer->createToken('user-token')->plainTextToken;

        return response()->json([
            'message'  => 'Logged in.',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    /* ====== بيانات المستخدم الحالي ====== */
    public function me(Request $request)
    {
        return new CustomerResource($request->user());
    }

    /* ====== تحديث بيانات المستخدم ====== */
    public function update(Request $request)
    {
        $customer = $request->user();   // مستخدم مصدَّق

        $v = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:customers,email,' . $customer->id,
            'phone'    => 'sometimes|string|unique:customers,phone,' . $customer->id,
            'password' => 'sometimes|string|min:6',
            'img'      => 'sometimes|image',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();

        if ($request->hasFile('img')) {
            $data['img'] = FileHelper::uploadImage($request->file('img'), 'uploads/customers');
        }

        $customer->update($data);

        return response()->json([
            'message'  => 'Profile updated.',
            'customer' => new CustomerResource($customer),
        ]);
    }

    /* ====== تسجيل الخروج ====== */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }
}
