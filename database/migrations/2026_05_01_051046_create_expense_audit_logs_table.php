<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('transition')->nullable();
            $table->json('marking_before')->nullable();
            $table->json('marking_after')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['expense_request_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_audit_logs');
    }
};
