<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\TutoringController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:api', 'prefix' => ''], function (){
    //수강과정
    Route::get('/courses', [CourseController::class, 'index']); //구매가능 수강과정 목록

    //수업
    Route::post('/tutoring/start', [TutoringController::class, 'tutoringStart']); //수업시작
    Route::post('/tutoring/end', [TutoringController::class, 'tutoringEnd']);     //수업종료
});
