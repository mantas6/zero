<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('ext_id');
            $table->boolean('billable')->default(false)->after('active');
            $table->string('color')->nullable()->after('billable');
            $table->string('client_name')->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['active', 'billable', 'color', 'client_name']);
        });
    }
};
