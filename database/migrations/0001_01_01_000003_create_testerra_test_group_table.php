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

        Schema::create($prefix.'test_group', function (Blueprint $table) use ($prefix) {
            $table->foreignId('test_id')->constrained($prefix.'tests')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained($prefix.'groups')->cascadeOnDelete();
            $table->primary(['test_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('testerra.table_prefix', 'testerra_').'test_group');
    }
};
