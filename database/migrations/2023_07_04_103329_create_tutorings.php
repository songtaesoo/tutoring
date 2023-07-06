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
        Schema::create('tutoring', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('student_user_id')->unsigned()->nullable()->comment('학생 회원ID');
            $table->bigInteger('tutor_user_id')->unsigned()->nullable()->comment('튜터 회원ID');
            $table->bigInteger('course_id')->unsigned()->nullable()->comment('수강과정 ID');
            $table->enum('status', ['pending', 'processing', 'completed', 'disconnected', 'cancelled'])->default('pending')->comment('수업 상태');
            $table->timestamp('started_at')->nullable()->comment('수업시작 시간');
            $table->timestamp('ended_at')->nullable()->comment('수업종료 시간');
            $table->string('description', 1000)->nullable()->comment('비고');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->foreign('student_user_id')->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('tutor_user_id')->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('course_id')->references('id')->on('courses')
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
        Schema::dropIfExists('tutoring');
    }
};
