<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing NULL values to 0 to prevent errors.
        DB::table('attendances')->whereNull('worked_minutes')->update(['worked_minutes' => 0]);

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status')->change();
            $table->integer('worked_minutes')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Reverting may depend on the original schema. 
            // This is a best guess.
            $table->enum('status', ['Present', 'Absent', 'Late', 'Half Day', 'Incomplete'])->change();
            $table->float('worked_minutes')->nullable()->change();
        });
    }
};
