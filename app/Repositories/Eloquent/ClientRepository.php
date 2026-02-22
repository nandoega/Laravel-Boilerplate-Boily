<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    protected string $model = Client::class;
    protected array $defaultColumns = ['id', 'name', 'email', 'phone', 'company', 'is_active', 'created_at', 'updated_at'];
    protected array $with = [];

    public function paginateWithFilter(int $perPage, ?string $search = null, ?bool $isActive = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $cacheKey = $this->cacheKey("paginate:{$perPage}:{$search}:{$isActive}");

        return $this->remember($cacheKey, fn () =>
            Client::query()
                ->when($search, fn ($q) => $q->where(fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%")
                ))
                ->when(!is_null($isActive), fn ($q) => $q->where('is_active', $isActive))
                ->select($this->defaultColumns)
                ->latest()
                ->paginate($perPage)
        );
    }
}
