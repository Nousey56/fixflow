<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->timestamp('scheduled_start_at')->nullable();
            $table->timestamp('scheduled_end_at')->nullable();
            $table->text('scheduling_notes')->nullable();

            $table->index('scheduled_start_at');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndex(['scheduled_start_at']);

            $table->dropColumn([
                'scheduled_start_at',
                'scheduled_end_at',
                'scheduling_notes',
            ]);
        });
    }
};
