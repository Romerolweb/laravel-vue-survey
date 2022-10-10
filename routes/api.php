<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyQuestionAnswerController;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/survey', \App\Http\Controllers\SurveyController::class);

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);


});
Route::get('/survey-question-answer', [\App\Http\Controllers\SurveyQuestionAnswerController::class, 'index']);
Route::get('/survey-question-answer/{survey}', [\App\Http\Controllers\SurveyQuestionAnswerController::class, 'showBySurveyId']);

Route::get('/survey-answers', [\App\Http\Controllers\SurveyController::class, 'showAnswers']);


Route::get('/survey-by-slug/{survey:slug}', [\App\Http\Controllers\SurveyController::class, 'showForGuest']);
Route::post('/survey/{survey}/answer', [\App\Http\Controllers\SurveyController::class, 'storeAnswer']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

