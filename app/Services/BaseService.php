<?php

namespace App\Services;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    /**
     * Primary repository injected by concrete service.
     */
    protected RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Run a callback inside a DB transaction.
     * Use for multi-step operations that must be atomic.
     */
    protected function transaction(\Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
