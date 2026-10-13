
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add an archive timestamp to properties.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable();
        });
    }

    /**
     * Remove the archive timestamp if rolled back.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
