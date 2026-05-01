<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('New expense — Symflow Demo')]
class ExpenseCreate extends Component
{
    #[Validate('required|string|max:120')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|string|max:60')]
    public string $category = 'Travel';

    #[Validate('required|numeric|min:0.01')]
    public string $amount = '';

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        if (! $user instanceof User) {
            $user = User::query()->where('role', 'employee')->first() ?? User::query()->first();
            Auth::login($user);
        }

        $expense = ExpenseRequest::query()->create([
            'requester_id' => $user->id,
            'title' => $this->title,
            'description' => $this->description !== '' ? $this->description : null,
            'category' => $this->category,
            'amount' => (float) $this->amount,
            'currency' => 'USD',
            'marking' => 'draft',
        ]);

        session()->flash('flash.success', 'Expense draft created. Submit it from its detail page to kick off review.');

        return $this->redirectRoute('expenses.show', $expense, navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.expense-create', [
            'categories' => ['Travel', 'Software', 'Equipment', 'Team', 'Sales', 'Other'],
        ]);
    }
}
