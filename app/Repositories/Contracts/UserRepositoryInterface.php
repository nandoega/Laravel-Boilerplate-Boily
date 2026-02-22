<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?\App\Models\User;
}
