<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends Controller
{
    private string $jwtSecret;
    private int    $jwtTtl;    // minutes

    public function __construct()
    {
        $this->jwtSecret = env('JWT_SECRET', 'secret');
        $this->jwtTtl    = (int) env('JWT_TTL', 1440);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'roles'    => json_encode(['customer']),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $this->formatUser($user),
            'token'   => $this->generateToken($user),
        ], 201);
    }

    /**
     * Authenticate a user and return a JWT.
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user'    => $this->formatUser($user),
            'token'   => $this->generateToken($user),
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request)
    {
        $user = $request->user;
        return response()->json(['user' => $this->formatUser($user)]);
    }

    /**
     * Get a user by ID.
     */
    public function show(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        return response()->json(['user' => $this->formatUser($user)]);
    }

    /**
     * Update user details.
     */
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
        ]);

        $user->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'User updated',
            'user'    => $this->formatUser($user),
        ]);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    private function generateToken(User $user): string
    {
        $now = time();
        $payload = [
            'iss'      => 'user-service',
            'sub'      => $user->id,
            'user_id'  => $user->id,
            'email'    => $user->email,
            'roles'    => json_decode($user->roles ?? '[]', true),
            'iat'      => $now,
            'exp'      => $now + ($this->jwtTtl * 60),
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'roles'      => json_decode($user->roles ?? '[]', true),
            'created_at' => $user->created_at,
        ];
    }
}
