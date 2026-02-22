<?php

namespace App\Services;

use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentService extends BaseService
{
    public function __construct(
        PaymentRepositoryInterface $repository,
        private readonly InvoiceRepositoryInterface $invoiceRepository
    ) {
        parent::__construct($repository);
    }

    public function list(int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->transaction(function () use ($data) {
            $payment = $this->repository->create($data);
            // Mark invoice paid if payment covers full amount
            $invoice = $this->invoiceRepository->findOrFail($data['invoice_id']);
            if ((float) $data['amount'] >= (float) $invoice->total) {
                $this->invoiceRepository->update($invoice->id, ['status' => 'paid', 'paid_at' => now()]);
            }
            return $payment;
        });
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function refund(int $id, string $reason): bool
    {
        return $this->repository->update($id, [
            'is_refunded'   => true,
            'refunded_at'   => now(),
            'refund_reason' => $reason,
        ]);
    }
}
