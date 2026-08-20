<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code   = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon || !$coupon->isValid((float) $request->subtotal)) {
            return response()->json(['valid' => false, 'message' => 'This code is not valid or could not be applied.'], 422);
        }

        $discount = $coupon->discountFor((float) $request->subtotal);

        return response()->json(['valid' => true, 'discount' => $discount, 'code' => $code]);
    }
}
