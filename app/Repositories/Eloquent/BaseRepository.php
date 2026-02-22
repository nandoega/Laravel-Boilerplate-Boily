<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use App\Traits\HasCaching;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements RepositoryInterface
{
    use HasCaching;

    /**
     * The Eloquent model class string.
     */
    protected string $model;

    /**
     * Default eager-load relationships. Override per repository.
     * @var array<string>
     */
    protected array $with = [];

    /**
     * Default columns to SELECT. Override per repository to prevent data leaks.
     * @var array<string>
     */
    protected array $defaultColumns = ['*'];

    /**
     * Columns allowed for filtering via query string.
     * @var array<string>
     */
    protected array $filterableColumns = [];

    /**
     * Columns allowed for sorting via query string.
     * @var array<string>
     */
    protected array $sortableColumns = ['created_at', 'updated_at'];

    /**
     * Boot the model instance.
     */
    protected function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return app($this->model)->newQuery()->with($this->with);
    }

    // =========================================================================
    // READ OPERATIONS (cached)
    // =========================================================================

    public function all(array $columns = ['*']): Collection
    {
        $cols = $columns === ['*'] ? $this->defaultColumns : $columns;
        $key  = $this->cacheKey('all:' . implode(',', $cols));

        return $this->remember($key, fn () => $this->newQuery()->get($cols));
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        $cols    = $columns === ['*'] ? $this->defaultColumns : $columns;
        $perPage = min($perPage, config('api.pagination.max_per_page', 100));

        return $this->newQuery()->paginate($perPage, $cols);
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        $cols = $columns === ['*'] ? $this->defaultColumns : $columns;
        $key  = $this->cacheKey("find:{$id}");

        return $this->remember($key, fn () => $this->newQuery()->find($id, $cols));
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        $model = $this->find($id, $columns);

        if (!$model) {
            throw new ModelNotFoundException("Resource not found.");
        }

        return $model;
    }

    public function exists(int|string $id): bool
    {
        return app($this->model)->newQuery()->where('id', $id)->exists();
    }

    public function count(): int
    {
        $key = $this->cacheKey('count');
        return $this->remember($key, fn () => app($this->model)->newQuery()->count());
    }

    // =========================================================================
    // WRITE OPERATIONS (invalidate cache)
    // =========================================================================

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $model = app($this->model)->newQuery()->create($data);
            $this->invalidateListCache();
            return $model->fresh($this->with);
        });
    }

    public function update(int|string $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $result = app($this->model)->newQuery()->findOrFail($id)->update($data);
            $this->forgetMany([
                $this->cacheKey("find:{$id}"),
                $this->cacheKey('all:' . implode(',', $this->defaultColumns)),
                $this->cacheKey('count'),
            ]);
            return (bool) $result;
        });
    }

    public function delete(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $result = app($this->model)->newQuery()->findOrFail($id)->delete();
            $this->forgetMany([
                $this->cacheKey("find:{$id}"),
                $this->cacheKey('all:' . implode(',', $this->defaultColumns)),
                $this->cacheKey('count'),
            ]);
            return (bool) $result;
        });
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    /**
     * Bust the collection & count caches after a write.
     */
    protected function invalidateListCache(): void
    {
        $this->forgetMany([
            $this->cacheKey('all:' . implode(',', $this->defaultColumns)),
            $this->cacheKey('count'),
        ]);
    }
}
