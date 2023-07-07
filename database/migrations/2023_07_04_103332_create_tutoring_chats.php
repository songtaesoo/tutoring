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
        Schema::create('tutoring_chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tutor_id')->unsigned()->nullable()->comment('강의ID');
            $table->bigInteger('sender_id')->unsigned()->nullable()->comment('발신자ID');
            $table->bigInteger('receipient_id')->unsigned()->nullable()->comment('수신자ID');
            $table->text('message')->nullable()->comment('채팅 메세지');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->foreign('tutor_id')->references('id')->on('tutorings')
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
        Schema::dropIfExists('tutoring_chats');
    }
};
