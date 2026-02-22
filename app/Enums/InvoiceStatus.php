<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Paid      = 'paid';
    case Overdue   = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Sent      => 'Sent',
            self::Paid      => 'Paid',
            self::Overdue   => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }
}
