<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseAuditLog extends Model
{
    protected $fillable = [
        'expense_request_id',
        'actor_id',
        'event',
        'transition',
        'marking_before',
        'marking_after',
        'reason',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'marking_before' => 'array',
            'marking_after' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
