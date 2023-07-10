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
        Schema::create('tutoring_calculations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tutoring_id')->unsigned()->nullable()->comment('수강ID');
            $table->decimal('amount', 10, 2)->default(0)->comment('금액');
            $table->boolean('is_payment')->default(false)->comment('지급여부');
            $table->boolean('payment_at')->nullable()->comment('지급일');
            $table->string('description', 100)->nullable()->comment('비고');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->foreign('tutoring_id')->references('id')->on('tutorings')
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
        Schema::dropIfExists('tutoring_calculations');
    }
};
