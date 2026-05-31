<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone_number');
            $table->date('date');
            $table->time('time');
            $table->string('table_id');
            $table->timestamps();
            $table->index(['date', 'time', 'table_id']); 
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};