<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Support\Str;

class InvoiceService extends BaseService
{
    public function __construct(
        InvoiceRepositoryInterface $repository,
        private readonly ProjectRepositoryInterface $projectRepository
    ) {
        parent::__construct($repository);
    }

    public function list(int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id): \App\Models\Invoice
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): \App\Models\Invoice
    {
        $data['invoice_number'] = $this->generateInvoiceNumber();
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function generateFromProject(int $projectId): \App\Models\Invoice
    {
        $project = $this->projectRepository->findOrFail($projectId);

        return $this->repository->create([
            'client_id'      => $project->client_id,
            'project_id'     => $project->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'status'         => InvoiceStatus::Draft->value,
            'amount'         => 0,
            'tax'            => 0,
            'total'          => 0,
        ]);
    }

    public function markPaid(int $id): bool
    {
        return $this->repository->update($id, [
            'status'  => InvoiceStatus::Paid->value,
            'paid_at' => now(),
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Ym') . '-' . strtoupper(Str::random(6));
    }
}
