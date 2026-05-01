<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\ExpenseRequest;
use App\Workflow\WorkflowReasonContext;
use Illuminate\Support\Facades\Auth;
use Laraflow\Contracts\WorkflowRegistryInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Expense — Symflow Demo')]
class ExpenseShow extends Component
{
    public ExpenseRequest $expense;

    public string $reason = '';

    public function mount(ExpenseRequest $expense): void
    {
        $this->expense = $expense->load(['requester', 'auditLogs.actor']);
    }

    public function fire(string $transition): void
    {
        $workflow = app(WorkflowRegistryInterface::class)->get('expense_approval');
        $result = $workflow->can($this->expense, $transition);

        if (! $result->allowed) {
            $messages = collect($result->blockers)->map(fn ($b) => $b->message)->implode(' / ');
            session()->flash('flash.error', "Can't fire \"{$transition}\": {$messages}");

            return;
        }

        WorkflowReasonContext::set($this->reason !== '' ? $this->reason : null);

        try {
            $workflow->apply($this->expense, $transition);
        } catch (\Throwable $e) {
            session()->flash('flash.error', $e->getMessage());

            return;
        }

        if ($transition === 'submit') {
            $this->expense->submitted_at = now();
        }
        if (in_array($transition, ['pay', 'reject_legal', 'reject_finance', 'reject_manager'], true)) {
            $this->expense->completed_at = now();
        }
        $this->expense->save();
        $this->reason = '';

        $this->expense->refresh()->load(['auditLogs.actor']);
        session()->flash('flash.success', "Fired transition \"{$transition}\".");
    }

    #[On('user-changed')]
    public function onUserChanged(): void
    {
        $this->expense->refresh();
    }

    /**
     * @return array{available: array<int, mixed>, awaiting: array<int, mixed>, inactive: array<int, mixed>}
     */
    public function getGroupedTransitionsProperty(): array
    {
        $workflow = app(WorkflowRegistryInterface::class)->get('expense_approval');

        $groups = ['available' => [], 'awaiting' => [], 'inactive' => []];

        foreach ($workflow->definition->transitions as $transition) {
            $result = $workflow->can($this->expense, $transition->name);
            $blocker = $result->blockers[0] ?? null;

            $intent = match (true) {
                str_starts_with($transition->name, 'reject') => 'destructive',
                str_starts_with($transition->name, 'approve'), $transition->name === 'finalize' => 'primary',
                $transition->name === 'pay' => 'success',
                default => 'neutral',
            };

            $row = [
                'transition' => $transition,
                'allowed' => $result->allowed,
                'reason' => $blocker?->message,
                'code' => $blocker?->code,
                'intent' => $intent,
            ];

            if ($result->allowed) {
                $groups['available'][] = $row;
            } elseif (in_array($blocker?->code, ['not_authenticated', 'wrong_role', 'guard_blocked', 'unknown_guard'], true)) {
                $groups['awaiting'][] = $row;
            } else {
                $groups['inactive'][] = $row;
            }
        }

        return $groups;
    }

    public function render()
    {
        return view('livewire.pages.expense-show', [
            'grouped' => $this->groupedTransitions,
            'activePlaces' => $this->expense->activePlaces(),
            'currentUser' => Auth::user(),
        ]);
    }
}
