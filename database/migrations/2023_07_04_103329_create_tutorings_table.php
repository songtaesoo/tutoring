<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutorings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('student_id')->unsigned()->nullable()->comment('학생ID');
            $table->bigInteger('tutor_id')->unsigned()->nullable()->comment('튜터ID');
            $table->bigInteger('ticket_id')->unsigned()->nullable()->comment('수강권ID');
            $table->enum('status', ['pending', 'processing', 'completed', 'disconnected', 'cancelled', 'reserved'])->default('pending')->comment('수업 상태');
            $table->timestamp('started_at')->nullable()->comment('수업시작 시간');
            $table->timestamp('ended_at')->nullable()->comment('수업종료 시간');
            $table->string('description', 1000)->nullable()->comment('비고');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('tutor_id')->references('id')->on('tutors')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('ticket_id')->references('id')->on('course_tickets')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutorings');
    }
};
