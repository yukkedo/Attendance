<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkBreakChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_break_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_break_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_change_id')->constrained()->cascadeOnDelete();
            $table->time('new_break_start');
            $table->time('new_break_end')->nullable();
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_break_change');
    }
}
