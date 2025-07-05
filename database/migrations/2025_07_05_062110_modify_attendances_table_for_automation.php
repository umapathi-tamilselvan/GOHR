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
        Schema::table('attendances', function (Blueprint $table) {
            $table->renameColumn('check_in_time', 'check_in');
            $table->renameColumn('check_out_time', 'check_out');
            $table->integer('worked_minutes')->nullable()->after('check_out');
            $table->enum('status', ['Full Day', 'Half Day', 'Absent', 'Incomplete'])->default('Incomplete')->after('worked_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->renameColumn('check_in', 'check_in_time');
            $table->renameColumn('check_out', 'check_out_time');
            $table->dropColumn('worked_minutes');
            $table->dropColumn('status');
        });
    }
};
