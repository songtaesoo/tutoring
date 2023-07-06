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
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->string('language', 20)->nullable()->comment('수업언어');
            // $table->enum('type', ['voice', 'video', 'chat'])->nullable()->comment('수업종류');
            // $table->smallinteger('period')->unsigned()->nullable()->comment('수강기간(월)');
            // $table->smallinteger('time')->unsigned()->nullable()->comment('수업시간(분)');
            // $table->smallInteger('count')->unsigned()->nullable()->comment('수업횟수');
            $table->boolean('is_sale')->nullable()->default(false)->comment('판매상태');
            $table->timestamp('sale_started_at')->nullable()->comment('판매시작기간');
            $table->timestamp('sale_ended_at')->nullable()->comment('판매종료기간');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pos_orders');
    }
}
