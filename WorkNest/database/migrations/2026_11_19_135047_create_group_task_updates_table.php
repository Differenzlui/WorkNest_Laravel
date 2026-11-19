<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_task_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // who sent it
            $table->unsignedTinyInteger('progress');
            $table->text('note')->nullable();
            $table->string('status')->default('pending');                     // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['group_task_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_task_updates');
    }
};