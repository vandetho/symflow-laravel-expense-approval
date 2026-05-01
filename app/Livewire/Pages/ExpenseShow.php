<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\ExpenseRequest;
use App\Workflow\WorkflowReasonContext;
use Illuminate\Support\Facades\Auth;
use Laraflow\Contracts\WorkflowRegistryInterface;
use Laraflow\Data\Transition;
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
     * @return array<int, array{transition: Transition, allowed: bool, reason: ?string, intent: string}>
     */
    public function getEnabledTransitionsProperty(): array
    {
        $workflow = app(WorkflowRegistryInterface::class)->get('expense_approval');
        $rows = [];

        foreach ($workflow->definition->transitions as $transition) {
            $result = $workflow->can($this->expense, $transition->name);
            $blockerReason = $result->blockers[0]->message ?? null;

            $intent = match (true) {
                str_starts_with($transition->name, 'reject') => 'destructive',
                str_starts_with($transition->name, 'approve'), $transition->name === 'finalize' => 'primary',
                $transition->name === 'pay' => 'success',
                default => 'neutral',
            };

            $rows[] = [
                'transition' => $transition,
                'allowed' => $result->allowed,
                'reason' => $blockerReason,
                'intent' => $intent,
            ];
        }

        return $rows;
    }

    public function render()
    {
        return view('livewire.pages.expense-show', [
            'enabledTransitions' => $this->enabledTransitions,
            'activePlaces' => $this->expense->activePlaces(),
            'currentUser' => Auth::user(),
        ]);
    }
}
