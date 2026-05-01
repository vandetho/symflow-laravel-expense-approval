<?php

declare(strict_types=1);

return [

    'workflows' => [

        'expense_approval' => [
            'type' => 'workflow',
            'marking_store' => [
                'type' => 'property',
                'property' => 'marking',
            ],
            'supports' => App\Models\ExpenseRequest::class,
            'initial_marking' => ['draft'],

            'places' => [
                'draft' => [
                    'metadata' => ['description' => 'Being drafted'],
                ],
                'legal_review' => [
                    'metadata' => ['description' => 'Awaiting legal'],
                ],
                'finance_review' => [
                    'metadata' => ['description' => 'Awaiting finance'],
                ],
                'manager_review' => [
                    'metadata' => ['description' => 'Awaiting manager'],
                ],
                'legal_approved' => [
                    'metadata' => ['description' => 'Legal approved'],
                ],
                'finance_approved' => [
                    'metadata' => ['description' => 'Finance approved'],
                ],
                'manager_approved' => [
                    'metadata' => ['description' => 'Manager approved'],
                ],
                'approved' => [
                    'metadata' => ['description' => 'All approvals collected'],
                ],
                'rejected' => [
                    'metadata' => ['description' => 'Rejected'],
                ],
                'paid' => [
                    'metadata' => ['description' => 'Reimbursed'],
                ],
            ],

            'transitions' => [
                'submit' => [
                    'from' => ['draft'],
                    'to' => ['legal_review', 'finance_review', 'manager_review'],
                ],
                'approve_legal' => [
                    'from' => ['legal_review'],
                    'to' => ['legal_approved'],
                    'guard' => 'role:legal',
                ],
                'approve_finance' => [
                    'from' => ['finance_review'],
                    'to' => ['finance_approved'],
                    'guard' => 'role:finance',
                ],
                'approve_manager' => [
                    'from' => ['manager_review'],
                    'to' => ['manager_approved'],
                    'guard' => 'role:manager',
                ],
                'reject_legal' => [
                    'from' => ['legal_review'],
                    'to' => ['rejected'],
                    'guard' => 'role:legal',
                ],
                'reject_finance' => [
                    'from' => ['finance_review'],
                    'to' => ['rejected'],
                    'guard' => 'role:finance',
                ],
                'reject_manager' => [
                    'from' => ['manager_review'],
                    'to' => ['rejected'],
                    'guard' => 'role:manager',
                ],
                'finalize' => [
                    'from' => ['legal_approved', 'finance_approved', 'manager_approved'],
                    'to' => ['approved'],
                ],
                'pay' => [
                    'from' => ['approved'],
                    'to' => ['paid'],
                    'guard' => 'role:finance',
                ],
            ],
        ],

    ],

];
