<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PhoneOtpVerificationController extends Controller
{
    /**
     * Send OTP to user's phone number for verification.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->phone_number) {
            return response()->json(['message' => 'User phone number not found.'], 404);
        }

        // Generate 6 digit OTP
        $otp = random_int(100000, 999999);

        // Save OTP with type 'phone'
        OtpCode::create([
            'user_id' => $user->id,
            'otp_code' => $otp,
            'type' => 'phone',
        ]);

        // TODO: Integrate SMS sending service here to send $otp to $user->phone_number
        Log::info("Sending phone OTP {$otp} to user {$user->id} phone {$user->phone_number}");

        return response()->json(['message' => 'OTP sent to your phone number.']);
    }

    /**
     * Verify the OTP code sent to user's phone.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $otp = $request->input('otp');

        $otpRecord = OtpCode::where('user_id', $user->id)
            ->where('otp_code', $otp)
            ->where('type', 'phone')
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user->phone_verified_at = now();
        $user->save();

        // $otpRecord->delete();

        return response()->json(['message' => 'Phone number verified successfully.']);
    }
}
