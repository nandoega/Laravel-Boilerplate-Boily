<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Register a new user and return a token.
     */
    public function register(array $data): array
    {
        $user = $this->transaction(function () use ($data) {
            $user = $this->repository->create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],  // cast hashes it
                'phone'    => $data['phone'] ?? null,
            ]);

            // Assign default role
            $user->assignRole('user');

            return $user;
        });

        $token = $user->createToken('auth-token', ['read', 'write'])->plainTextToken;

        return [
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('api.token_expiry', 1440),
        ];
    }

    /**
     * Login and return token.
     */
    public function login(array $credentials): array
    {
        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account has been disabled.'],
            ]);
        }

        // Revoke all previous tokens on login (single-session by default)
        $user->tokens()->delete();

        // Grant token abilities based on role
        $abilities = $this->abilitiesForUser($user);
        $token     = $user->createToken('auth-token', $abilities)->plainTextToken;

        return [
            'user'       => $user->load('roles'),
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('api.token_expiry', 1440),
        ];
    }

    /**
     * Logout: revoke current token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Refresh: revoke current token, issue a new one.
     */
    public function refresh(User $user): array
    {
        $user->currentAccessToken()->delete();
        $abilities = $this->abilitiesForUser($user);
        $token     = $user->createToken('auth-token', $abilities)->plainTextToken;

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('api.token_expiry', 1440),
        ];
    }

    /**
     * Map roles → token abilities.
     */
    private function abilitiesForUser(User $user): array
    {
        if ($user->hasRole(['super-admin', 'admin'])) {
            return ['read', 'write', 'delete'];
        }

        if ($user->hasRole('manager')) {
            return ['read', 'write'];
        }

        return ['read'];
    }
}
