<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// controller
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PollController;

// middleware
use App\Http\Middleware\adminOnly;
use App\Http\Middleware\isUserHasVote;
use App\Http\Middleware\userOnly;

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


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'], function ($router) {

    Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware(['auth:api']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh',  [AuthController::class, 'refresh']);
    Route::post('/me',  [AuthController::class, 'me']);
    Route::post('/isDefault/password',  [AuthController::class, 'check']);
    
    Route::post('/reset_password',  [AuthController::class, 'reset_password']);
});

Route::middleware(['auth:api'])->group(function (){
    Route::post('/poll', [PollController::class, 'post'])->middleware([adminOnly::class]);
    Route::delete('/poll/{poll_id}', [PollController::class, 'delete'])->middleware([adminOnly::class]);

    Route::get('/poll', [PollController::class, 'getAll']);
    Route::get('/poll/{id}', [PollController::class, 'getPollById']);
    Route::post('/poll/{poll_id}/vote/{choice_id}', [PollController::class, 'vote'])->middleware([userOnly::class]);
});

//sementara saya luarin dlu
