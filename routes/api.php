<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [App\Http\Controllers\Api\ApiUserController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\ApiUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\ApiUserController::class, 'logout']);
    Route::post('/user/edit/{id}', [App\Http\Controllers\Api\ApiUserController::class, 'editProfile']);
    Route::post('/password/edit/{id}', [App\Http\Controllers\Api\ApiUserController::class, 'editPassword']);
    Route::get('/user/{id}', [App\Http\Controllers\Api\ApiUserController::class, 'getUserById']);

    Route::post('/priority-task/add', [App\Http\Controllers\Api\ApiTaskController::class, 'addPriorityTask']);
    Route::post('/priority-task/edit/{id}', [App\Http\Controllers\Api\ApiTaskController::class, 'editPriorityTask']);
    Route::post('/priority-task/delete/{id}', [App\Http\Controllers\Api\ApiTaskController::class, 'deletePriorityTask']);
    Route::post('/daily-task/add', [App\Http\Controllers\Api\ApiTaskController::class, 'addDailyTask']);
    Route::post('/daily-task/edit/{id}', [App\Http\Controllers\Api\ApiTaskController::class, 'editDailyTask']);
    Route::get('/daily-task/all', [App\Http\Controllers\Api\ApiTaskController::class, 'getAllDailyTask']);
    Route::get('/priority-task/all', [App\Http\Controllers\Api\ApiTaskController::class, 'getAllPriorityTask']);
    Route::get('/priority-task', [App\Http\Controllers\Api\ApiTaskController::class, 'getPriorityTask']);
    Route::get('/priority-task/up-coming', [App\Http\Controllers\Api\ApiTaskController::class, 'getUpComingPriorityTask']);
    Route::get('/priority-task/done', [App\Http\Controllers\Api\ApiTaskController::class, 'getDonePriorityTask']);
    Route::get('/priority-task/detail/{taskId}', [App\Http\Controllers\Api\ApiTaskController::class, 'getPriorityTaskById']);
    Route::get('/daily-task/detail/{taskId}', [App\Http\Controllers\Api\ApiTaskController::class, 'getDailyTaskById']);
    Route::post('/task/update-status/{taskId}', [App\Http\Controllers\Api\ApiTaskController::class, 'updateStatus']);
    Route::post('/daily-task/update-status/{taskId}', [App\Http\Controllers\Api\ApiTaskController::class, 'updateDailyTaskStatus']);
    Route::post('/daily-task/update-status', [App\Http\Controllers\Api\ApiTaskController::class, 'updateDailyTaskStatusToFalse']);
    Route::post('/task/end/{taskId}', [App\Http\Controllers\Api\ApiTaskController::class, 'endPriorityTask']);
    Route::get('/task/report', [App\Http\Controllers\Api\ApiTaskController::class, 'pdfReport']);
});
