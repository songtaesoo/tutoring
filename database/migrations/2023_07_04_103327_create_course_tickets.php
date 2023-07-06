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
        Schema::create('course_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->string('ticket_no', 100)->nullable()->comment('수강권 고유번호');
            $table->string('name', 100)->nullable()->comment('수강권명');
            $table->decimal('price', 10, 2)->default(0)->comment('수강권금액');
            $table->boolean('is_sale')->nullable()->default(false)->comment('판매상태');
            $table->timestamp('started_at')->nullable()->comment('수강시작기간');
            $table->timestamp('ended_at')->nullable()->comment('수강종료기간');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->foreign('course_id')->references('id')->on('courses')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('student_id')->references('id')->on('students')
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
        Schema::dropIfExists('course_tickets');
    }
};
