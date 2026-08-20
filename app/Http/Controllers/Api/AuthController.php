<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\GoogleTokenRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\TwoFactorVerifyRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\LoginActivity;
use App\Models\User;
use App\Services\TotpService;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct(private TotpService $totp) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('mobile', ['*']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $success = Auth::attempt(['email' => $request->email, 'password' => $request->password]);

        LoginActivity::create([
            'user_id'    => $success ? Auth::id() : null,
            'email'      => $request->email,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $success,
        ]);

        if (! $success) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->two_factor_secret && $user->two_factor_enabled_at) {
            $token = $user->createToken('2fa-challenge', ['totp-challenge'], now()->addMinutes(10));

            return response()->json([
                'requires_2fa' => true,
                'temp_token'   => $token->plainTextToken,
            ]);
        }

        $token = $user->createToken('mobile', ['*']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function googleTokenLogin(GoogleTokenRequest $request): JsonResponse
    {
        $client  = new GoogleClient(['client_id' => config('services.google.client_id')]);
        $payload = $client->verifyIdToken($request->input('id_token'));

        if (! $payload || ! ($payload['email_verified'] ?? false)) {
            return response()->json(['message' => 'Invalid Google token.'], 401);
        }

        $user = User::where('google_id', $payload['sub'])->first()
            ?? User::where('email', $payload['email'])->first();

        if ($user) {
            $dirty = [];
            if (! $user->google_id) $dirty['google_id'] = $payload['sub'];
            if (! $user->email_verified_at) $dirty['email_verified_at'] = now();
            if ($dirty) {
                $user->forceFill($dirty)->save();
                $user = $user->fresh();
            }
        } else {
            $user = User::forceCreate([
                'name'              => strip_tags($payload['name'] ?? $payload['email']),
                'email'             => $payload['email'],
                'google_id'         => $payload['sub'],
                'email_verified_at' => now(),
                'password'          => null,
            ]);
        }

        if ($user->two_factor_secret && $user->two_factor_enabled_at) {
            $token = $user->createToken('2fa-challenge', ['totp-challenge'], now()->addMinutes(10));

            return response()->json([
                'requires_2fa' => true,
                'temp_token'   => $token->plainTextToken,
            ]);
        }

        $token = $user->createToken('mobile', ['*']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => new UserResource($user),
        ]);
    }

    public function twoFactorVerify(TwoFactorVerifyRequest $request): JsonResponse
    {
        if (! $request->user()->tokenCan('totp-challenge')) {
            return response()->json(['message' => 'Invalid token type.'], 403);
        }

        $secret = $request->user()->getAttributes()['two_factor_secret'] ?? null;

        if (! $this->totp->verify($secret, $request->input('code'))) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        $request->user()->currentAccessToken()->delete();

        $user  = $request->user();
        $token = $user->createToken('mobile', ['*']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => new UserResource($user),
        ]);
    }

    public function twoFactorSetup(Request $request): JsonResponse
    {
        $user   = $request->user();
        $secret = $this->totp->generateSecret();

        return response()->json([
            'secret' => $secret,
            'qr_url' => $this->totp->qrCodeUrl($secret, $user->email, 'Fenroy'),
        ]);
    }

    public function twoFactorConfirm(Request $request): JsonResponse
    {
        $request->validate([
            'code'   => ['required', 'digits:6'],
            'secret' => ['required', 'string'],
        ]);

        if (! $this->totp->verify($request->secret, $request->input('code'))) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        $request->user()->forceFill([
            'two_factor_secret'     => $request->secret,
            'two_factor_enabled_at' => now(),
        ])->save();

        return response()->json(['message' => '2FA enabled.']);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->fill($request->only('name', 'phone'));

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json(new UserResource($user));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Password updated.']);
    }
}
