<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    protected string $model = Invoice::class;
    protected array $defaultColumns = ['id', 'client_id', 'project_id', 'invoice_number', 'status', 'amount', 'tax', 'total', 'due_date', 'paid_at', 'created_at'];
    protected array $with = ['client:id,name', 'project:id,name'];
}
