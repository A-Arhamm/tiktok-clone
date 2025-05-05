<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use App\Models\OtpCode;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'image' => '/user-placeholder.png',
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $otp = rand(100000, 999999);
        OtpCode::create([
            'user_id' => $user->id,
            'otp_code' => $otp,
        ]);
        Log::info("OTP stored in database", ['user_id' => $user->id, 'otp' => $otp]);

        Mail::raw("Your OTP code is: {$otp}", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Email Verification OTP');
        });

        return response()->json(['user_id' => $user->id]);
    }
}
