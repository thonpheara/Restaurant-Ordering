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
        Schema::table('logins', function (Blueprint $table) {
            if (!Schema::hasColumn('logins', 'last_seen')) {
                $table->timestamp('last_seen')->nullable()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logins', function (Blueprint $table) {
            if (Schema::hasColumn('logins', 'last_seen')) {
                $table->dropColumn('last_seen');
            }
        });
    }
};
