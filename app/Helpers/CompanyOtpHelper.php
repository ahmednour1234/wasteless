<?php
// app/Helpers/CompanyOtpHelper.php
namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyOtpHelper
{
function storeCompanyOtp(int $companyId, string $code, int $ttl = 300): void
{
    Cache::put("company_otp_{$companyId}", $code, $ttl);
}

/**
 * استرجاع رمز التحقق من الكاش (أو null إذا انتهت صلاحيته)
 */
function getCompanyOtp(int $companyId): ?string
{
    return Cache::get("company_otp_{$companyId}");
}
}