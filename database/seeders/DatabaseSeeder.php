<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\ExpenseAuditLog;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Workflow\WorkflowReasonContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laraflow\Contracts\WorkflowRegistryInterface;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedUsers();
        $this->seedExpenses($users);
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $roster = [
            ['ada',     'Ada Lovelace',   'ada@acme.test',   Role::Employee],
            ['grace',   'Grace Hopper',   'grace@acme.test', Role::Employee],
            ['manager', 'Linus Torvalds', 'linus@acme.test', Role::Manager],
            ['finance', 'Marie Curie',    'marie@acme.test', Role::Finance],
            ['legal',   'Hedy Lamarr',    'hedy@acme.test',  Role::Legal],
        ];

        $users = [];

        foreach ($roster as [$key, $name, $email, $role]) {
            $users[$key] = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'email_verified_at' => now(),
                ],
            );
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedExpenses(array $users): void
    {
        $registry = app(WorkflowRegistryInterface::class);
        $workflow = $registry->get('expense_approval');

        $samples = [
            [
                'requester' => 'ada',
                'title' => 'Conference travel — re:Invent 2026',
                'description' => 'Round-trip flights, hotel (3 nights), conference pass.',
                'category' => 'Travel',
                'amount' => 4250.00,
                'steps' => [],
            ],
            [
                'requester' => 'grace',
                'title' => 'Team offsite — venue deposit',
                'description' => 'Deposit for the Q3 engineering offsite (12 attendees).',
                'category' => 'Team',
                'amount' => 7800.00,
                'steps' => [
                    ['transition' => 'submit', 'actor' => 'grace'],
                ],
            ],
            [
                'requester' => 'ada',
                'title' => 'New analytics SaaS — annual',
                'description' => 'Annual subscription for product analytics tool.',
                'category' => 'Software',
                'amount' => 12500.00,
                'steps' => [
                    ['transition' => 'submit', 'actor' => 'ada'],
                    ['transition' => 'approve_legal', 'actor' => 'legal'],
                ],
            ],
            [
                'requester' => 'grace',
                'title' => 'Standing desk replacement',
                'description' => 'Replacement for a broken sit-stand desk.',
                'category' => 'Equipment',
                'amount' => 980.00,
                'steps' => [
                    ['transition' => 'submit', 'actor' => 'grace'],
                    ['transition' => 'approve_legal', 'actor' => 'legal'],
                    ['transition' => 'approve_finance', 'actor' => 'finance'],
                    ['transition' => 'approve_manager', 'actor' => 'manager'],
                ],
            ],
            [
                'requester' => 'ada',
                'title' => 'Customer dinner — closed deal',
                'description' => 'Dinner with the Acme Corp procurement team after contract signing.',
                'category' => 'Sales',
                'amount' => 480.00,
                'steps' => [
                    ['transition' => 'submit', 'actor' => 'ada'],
                    ['transition' => 'approve_legal', 'actor' => 'legal'],
                    ['transition' => 'approve_finance', 'actor' => 'finance'],
                    ['transition' => 'approve_manager', 'actor' => 'manager'],
                    ['transition' => 'finalize', 'actor' => 'finance'],
                    ['transition' => 'pay', 'actor' => 'finance', 'reason' => 'Reimbursed via expense card.'],
                ],
            ],
            [
                'requester' => 'grace',
                'title' => 'Custom mechanical keyboard',
                'description' => 'Premium split keyboard.',
                'category' => 'Equipment',
                'amount' => 650.00,
                'steps' => [
                    ['transition' => 'submit', 'actor' => 'grace'],
                    ['transition' => 'approve_finance', 'actor' => 'finance'],
                    ['transition' => 'reject_manager', 'actor' => 'manager', 'reason' => 'Not in this quarter\'s equipment budget.'],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $requester = $users[$sample['requester']];

            $expense = ExpenseRequest::query()->create([
                'requester_id' => $requester->id,
                'title' => $sample['title'],
                'description' => $sample['description'],
                'category' => $sample['category'],
                'amount' => $sample['amount'],
                'currency' => 'USD',
                'marking' => 'draft',
            ]);

            foreach ($sample['steps'] as $step) {
                Auth::login($users[$step['actor']]);
                WorkflowReasonContext::set($step['reason'] ?? null);

                $workflow->apply($expense, $step['transition']);

                if ($step['transition'] === 'submit') {
                    $expense->submitted_at = now();
                }
                if (in_array($step['transition'], ['pay', 'reject_legal', 'reject_finance', 'reject_manager'], true)) {
                    $expense->completed_at = now();
                }

                $expense->save();
            }

            Auth::logout();
        }

        // Backdate audit log entries so they look like a real history.
        ExpenseAuditLog::query()->orderBy('id')->get()->each(function (ExpenseAuditLog $log, int $i) {
            $log->occurred_at = now()->subMinutes((30 - $i) * 17);
            $log->save();
        });
    }
}
