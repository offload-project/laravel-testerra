<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('testerra.table_prefix', 'testerra_');

        Schema::create($prefix.'assignments', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained($prefix.'tests')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'test_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('testerra.table_prefix', 'testerra_').'assignments');
    }
};
