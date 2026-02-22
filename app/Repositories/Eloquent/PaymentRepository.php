<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    protected string $model = Payment::class;
    protected array $defaultColumns = ['id', 'invoice_id', 'amount', 'method', 'reference', 'paid_at', 'is_refunded', 'created_at'];
    protected array $with = ['invoice:id,invoice_number,total'];
}
