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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('name', 50)->comment('아이디')->unique();;
			$table->string('email', 100)->comment('이메일')->unique();
			$table->string('password', 200)->comment('비밀번호');
			$table->enum('role', ['student', 'tutor', 'admin'])->comment('역할');
			$table->timestamp('email_verified_at')->nullable()->comment('이메일 인증여부');
			$table->enum('status', array('active', 'deactive'))->comment('계정상태');
			$table->string('memo', 500)->nullable()->comment('비고');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
