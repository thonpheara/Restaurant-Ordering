<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'reservation_name')) {
                $table->string('reservation_name')->nullable()->after('table_number');
            }

            if (!Schema::hasColumn('orders', 'prep_time_min')) {
                $table->unsignedInteger('prep_time_min')->nullable()->after('status');
            }

            if (!Schema::hasColumn('orders', 'prep_time_max')) {
                $table->unsignedInteger('prep_time_max')->nullable()->after('prep_time_min');
            }

            if (!Schema::hasColumn('orders', 'estimated_ready_at')) {
                $table->timestamp('estimated_ready_at')->nullable()->after('prep_time_max');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('orders', 'reservation_name') ? 'reservation_name' : null,
                Schema::hasColumn('orders', 'prep_time_min') ? 'prep_time_min' : null,
                Schema::hasColumn('orders', 'prep_time_max') ? 'prep_time_max' : null,
                Schema::hasColumn('orders', 'estimated_ready_at') ? 'estimated_ready_at' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
