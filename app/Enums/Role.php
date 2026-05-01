<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Employee = 'employee';
    case Legal = 'legal';
    case Finance = 'finance';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Employee',
            self::Legal => 'Legal',
            self::Finance => 'Finance',
            self::Manager => 'Manager',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Employee => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
            self::Legal => 'bg-violet-50 text-violet-700 ring-violet-200',
            self::Finance => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Manager => 'bg-amber-50 text-amber-700 ring-amber-200',
        };
    }
}
