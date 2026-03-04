<?php

namespace App\Http\Controllers\API\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\Company;
use App\Models\Branch;
use App\Notifications\CompanyOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Helpers\FileHelper;
use App\Mail\CompanyVerificationMail; // تأكد أنك أنشأت هذا الميل

class CompanyAuthController extends Controller
{
    /*-------------------------------------------------
    | 1) Sign-up (إنشاء شركة + إرسال OTP)
    --------------------------------------------------*/
public function signup(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'     => 'required|string|max:255',
        'phone'    => 'required|string|unique:companies,phone',
        'email'    => 'required|email|unique:companies,email',
        'password' => 'required|string|min:6',
        'logo'     => 'nullable|image',
        'branch_name'  => 'required|string|max:255',
        'branch_phone' => 'required|string|max:50',
        'lat'          => 'required|numeric',
        'lng'          => 'required|numeric',
        'address'      => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $validator->validated();

    $logoPath = $request->hasFile('logo')
        ? FileHelper::uploadImage($request->file('logo'), 'uploads/logos')
        : null;

    $verificationCode = app()->environment(['local', 'testing']) ? '111111' : rand(100000, 999999);

    // بدون Hash::make بفضل cast
    $company = Company::create([
        'name'              => $data['name'],
        'phone'             => $data['phone'],
        'email'             => $data['email'],
        'password'          => $data['password'], // يتم تشفيرها تلقائيًا
        'active'            => false,
        'logo'              => $logoPath,
        'email_verify_code' => $verificationCode,
    ]);

    Branch::create([
        'company_id' => $company->id,
        'name'       => $data['branch_name'],
        'phone'      => $data['branch_phone'],
        'lat'        => $data['lat'],
        'lng'        => $data['lng'],
        'address'    => $data['address'],
        'main'       => 1,
        'active'     => 1,
    ]);

    try {
        Mail::to($company->email)->send(new CompanyVerificationMail($company->name, $verificationCode));
    } catch (\Exception $e) {
        logger()->error('Email error: ' . $e->getMessage());
    }

    return response()->json([
        'message'    => 'تم التسجيل بنجاح وتم إرسال كود التحقق.',
        'company_id' => $company->id,
    ], 201);
}

    /*-------------------------------------------------
    | 2) Verify OTP (تفعيل الحساب)
    --------------------------------------------------*/
  public function verifyOtp(Request $request)
{
    // التحقق من صحة البيانات المُرسلة
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:companies,email',
        'code'  => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // جلب الشركة باستخدام البريد الإلكتروني
    $company = Company::where('email', $request->email)->firstOrFail();

    // التحقق من الكود
    if ($company->email_verify_code !== $request->code) {
         return response()->json(['رمز التحقق غير صحيح أو منتهي الصلاحية.'], 422);
    }

    // تفعيل الحساب وتحديث الكود لتجنّب استخدامه مرة أخرى
    $company->update([
        'email_verify_code' => null, // إلغاء الكود بعد التحقق
    ]);

    // إنشاء توكن للمصادقة
    $token = $company->createToken('company-token')->plainTextToken;

    return response()->json([
        'message' => 'تم التحقق من البريد الإلكتروني بنجاح.',
        'token'   => $token,
        'company' => $company->only(['id', 'name', 'email', 'phone']),
    ]);
}


    /*-------------------------------------------------
    | 3) Login (البريد + كلمة المرور)
    --------------------------------------------------*/
public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $company = Company::where('email', $request->email)->first();

    // التحقق من وجود الشركة وتطابق كلمة المرور
if (! $company || ! $company->active || !Hash::check($request->password, $company->password)) {
    return response()->json(['message' => 'بيانات الدخول غير صحيحة أو الحساب غير مفعل.'], 403);
}


    // إنشاء التوكن باستخدام Sanctum
    $token = $company->createToken('company-token')->plainTextToken;

    return response()->json([
        'message' => 'تم تسجيل الدخول بنجاح.',
        'token'   => $token,
        'company' => $company->only(['id', 'name', 'email', 'phone']),
    ]);
}

    /*-------------------------------------------------
    | 4) Logout (إلغاء التوكن الحالي)
    --------------------------------------------------*/
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Signed out.',
        ]);
    }

    /*-------------------------------------------------
    | 5) Resend OTP (إعادة إرسال الرمز)
    --------------------------------------------------*/
public function resendOtp(Request $request)
{
    // التحقق من صحة البريد الإلكتروني
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:companies,email',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // جلب الشركة من قاعدة البيانات
    $company = Company::where('email', $request->email)->firstOrFail();

    // توليد كود تحقق جديد
    $verificationCode = app()->environment(['local', 'testing']) ? '111111' : rand(100000, 999999);

    // تحديث الكود في قاعدة البيانات
    $company->update([
        'email_verify_code' => $verificationCode,
    ]);

    // إرسال كود التحقق عبر البريد الإلكتروني
    Mail::to($company->email)->send(new \App\Mail\CompanyVerificationMail($company->name, $verificationCode));

    return response()->json([
        'message' => 'تم إرسال كود تحقق جديد إلى بريدك الإلكتروني.',
    ]);
}

}
