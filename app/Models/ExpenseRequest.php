<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laraflow\Eloquent\HasWorkflowTrait;

class ExpenseRequest extends Model
{
    use HasWorkflowTrait;

    protected $fillable = [
        'requester_id',
        'title',
        'description',
        'category',
        'amount',
        'currency',
        'marking',
        'submitted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'marking' => 'array',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected function getDefaultWorkflowName(): string
    {
        return 'expense_approval';
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ExpenseAuditLog::class)->orderByDesc('occurred_at');
    }

    /**
     * @return array<string>
     */
    public function activePlaces(): array
    {
        return $this->getWorkflowMarking()->getActivePlaces();
    }

    public function isInPlace(string $place): bool
    {
        return in_array($place, $this->activePlaces(), true);
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $places = $this->activePlaces();

                if (in_array('paid', $places, true)) {
                    return 'paid';
                }
                if (in_array('rejected', $places, true)) {
                    return 'rejected';
                }
                if (in_array('approved', $places, true)) {
                    return 'approved';
                }
                if (in_array('draft', $places, true)) {
                    return 'draft';
                }

                return 'in_review';
            },
        );
    }
}
