<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected string $model = User::class;

    protected array $defaultColumns = [
        'id', 'name', 'email', 'phone', 'avatar', 'is_active', 'created_at', 'updated_at'
    ];

    public function findByEmail(string $email): ?User
    {
        $key = $this->cacheKey("email:{$email}");

        return $this->remember($key, fn () =>
            User::where('email', $email)->first($this->defaultColumns)
        );
    }
}
