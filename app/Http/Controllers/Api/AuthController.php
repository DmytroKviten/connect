<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * POST /api/register  -> 201 + { ok, token, token_type, user }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email','max:255','unique:users,email'],
            // Мінімум 8 символів; можна посилити правила за бажанням
            'password'              => ['required','confirmed', Password::min(8)],
        ]);

        // нормалізуємо email (щоб уникати дублювань John@/john@)
        $email = strtolower($data['email']);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $email,
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken($this->tokenName($request))->plainTextToken;

        return response()->json([
            'ok'         => true,
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $user,
        ], 201);
    }

    /**
     * POST /api/login  -> 200 + { ok, token, token_type, user }
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email','max:255'],
            'password' => ['required','string','min:6'],
        ]);

        $email = strtolower($data['email']);
        $user  = User::where('email', $email)->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Уніфікована відповідь помилки
            return response()->json([
                'ok'      => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken($this->tokenName($request))->plainTextToken;

        return response()->json([
            'ok'         => true,
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $user,
        ]);
    }

    /**
     * GET /api/user (auth:sanctum)
     */
    public function user(Request $request)
    {
        return response()->json([
            'ok'   => true,
            'user' => $request->user(),
        ]);
    }

    /**
     * POST /api/logout (auth:sanctum)
     * Опціонально: {all: true} — відкликати всі токени.
     */
    public function logout(Request $request)
    {
        $u = $request->user();

        if ($request->boolean('all')) {
            $u->tokens()->delete();
        } else {
            $u->currentAccessToken()?->delete();
        }

        return response()->json(['ok' => true, 'message' => 'Logged out']);
    }

    /**
     * Формуємо зручну назву токена (для аудиту в БД)
     */
    private function tokenName(Request $request): string
    {
        $ua = (string) $request->userAgent();
        return trim('api_token '.($ua ? "($ua)" : ''));
    }
}
