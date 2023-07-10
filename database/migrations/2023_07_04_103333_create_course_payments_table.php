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
        Schema::create('course_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_id')->unsigned()->nullable()->comment('수강ID');
            $table->bigInteger('student_id')->unsigned()->nullable()->comment('학생ID');
            $table->bigInteger('origin_payment_id')->unsigned()->nullable()->comment('원결제번호');
            $table->string('payment_no', 100)->nullable()->comment('결제고유번호');
            $table->decimal('amount', 10, 2)->default(0)->comment('결제금액');
            $table->string('provider', 10)->nullable()->comment('결제수단');
            $table->string('auth_no', 100)->nullable()->comment('결제승인번호');
            $table->timestamp('payment_at')->nullable()->comment('승인시간');
            $table->string('description', 100)->nullable()->comment('비고');
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
        Schema::dropIfExists('course_payments');
    }
};
