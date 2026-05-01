<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\ExpenseRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Expenses — Symflow Demo')]
class Dashboard extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'view')]
    public string $view = 'kanban';

    #[On('user-changed')]
    public function onUserChanged(): void
    {
        // Re-render so transitions appropriate to the new actor surface.
    }

    public function render()
    {
        $query = ExpenseRequest::query()->with('requester')->latest();

        if ($this->search !== '') {
            $needle = '%' . $this->search . '%';
            $query->where(fn ($q) => $q
                ->where('title', 'like', $needle)
                ->orWhere('category', 'like', $needle));
        }

        $expenses = $query->get();

        $columns = [
            ['key' => 'draft',     'label' => 'Draft',      'tone' => 'zinc',    'items' => collect()],
            ['key' => 'in_review', 'label' => 'In review',  'tone' => 'amber',   'items' => collect()],
            ['key' => 'approved',  'label' => 'Approved',   'tone' => 'sky',     'items' => collect()],
            ['key' => 'paid',      'label' => 'Paid',       'tone' => 'emerald', 'items' => collect()],
            ['key' => 'rejected',  'label' => 'Rejected',   'tone' => 'rose',    'items' => collect()],
        ];

        foreach ($expenses as $expense) {
            foreach ($columns as &$col) {
                if ($col['key'] === $expense->status) {
                    $col['items']->push($expense);
                }
            }
        }
        unset($col);

        return view('livewire.pages.dashboard', [
            'columns' => $columns,
            'expenses' => $expenses,
            'totals' => [
                'count' => $expenses->count(),
                'amount' => $expenses->sum('amount'),
                'pending' => $expenses->where('status', 'in_review')->sum('amount'),
                'paid' => $expenses->where('status', 'paid')->sum('amount'),
            ],
        ]);
    }
}
