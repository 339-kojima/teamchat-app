<?php

use App\Http\Controllers\Api\V1\ChannelController as PublicChannelController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/channels', [PublicChannelController::class, 'index']);
    Route::get('/channels/{channel}/messages', [PublicChannelController::class, 'messages']);
    Route::options('/channels', [PublicChannelController::class, 'methodNotAllowed']);
    Route::options('/channels/{channel}/messages', [PublicChannelController::class, 'methodNotAllowed']);
});
