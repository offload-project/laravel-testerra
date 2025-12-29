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

        Schema::table($prefix.'bugs', function (Blueprint $table) {
            $table->string('integration_type')->nullable()->after('severity');
            $table->string('external_id')->nullable()->after('integration_type');
            $table->string('external_key')->nullable()->after('external_id');
            $table->string('external_url')->nullable()->after('external_key');

            $table->index(['integration_type', 'external_id']);
        });
    }

    public function down(): void
    {
        $prefix = config('testerra.table_prefix', 'testerra_');

        Schema::table($prefix.'bugs', function (Blueprint $table) {
            $table->dropIndex([$prefix.'bugs_integration_type_external_id_index']);
            $table->dropColumn(['integration_type', 'external_id', 'external_key', 'external_url']);
        });
    }
};
