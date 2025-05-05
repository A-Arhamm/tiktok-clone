<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\OtpCode;

class OTPVerificationController extends Controller
{
    /**
     * Verify the OTP code sent to user's email.
     */
    public function verify(Request $request): JsonResponse
    {
        Log::info('OTP Verification method called');

        Log::info('OTP Verification attempt', ['request' => $request->all()]);

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'otp' => ['required', 'digits:6'],
        ]);

        $userId = $request->input('user_id');
        $otp = $request->input('otp');

        Log::info('OTP received from request', ['otp' => $otp]);

        Log::info('All OTPs for user', ['otps' => OtpCode::where('user_id', $userId)->get()]);

        $otpRecord = OtpCode::where('user_id', $userId)
            ->where('otp_code', $otp)
            ->latest()
            ->first();

        Log::info('OTP record found with direct query', ['otpRecord' => $otpRecord]);

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user = User::findOrFail($userId);

        $user->email_verified_at = now();
        $user->save();

        // $otpRecord->delete();

        Auth::login($user);

        return response()->json(['message' => 'Email verified successfully.']);
    }
}
