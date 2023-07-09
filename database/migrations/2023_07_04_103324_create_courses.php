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
            $table->bigInteger('type_id')->unsigned()->nullable();
            $table->bigInteger('language_id')->unsigned()->nullable();
            $table->string('name', 100)->nullable()->comment('강의명');
            $table->smallinteger('period')->unsigned()->nullable()->comment('수강 기간(월)');
            $table->smallinteger('time')->unsigned()->nullable()->comment('수업 시간(분)');
            $table->smallInteger('count')->unsigned()->nullable()->comment('수업 횟수');
            $table->decimal('price', 10, 2)->default(0)->comment('금액');
            $table->boolean('is_sale')->nullable()->default(false)->comment('판매 상태');
            $table->timestamp('sale_started_at')->nullable()->comment('판매시작 기간');
            $table->timestamp('sale_ended_at')->nullable()->comment('판매종료 기간');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->foreign('type_id')->references('id')->on('support_types')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('language_id')->references('id')->on('support_languages')
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
        Schema::dropIfExists('courses');
    }
};
