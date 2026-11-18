<?php

use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ChannelMemberController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
    Route::get('/channels/create', [ChannelController::class, 'create'])->name('channels.create');
    Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');
    Route::get('/channels/{channel}/edit', [ChannelController::class, 'edit'])->name('channels.edit');
    Route::put('/channels/{channel}', [ChannelController::class, 'update'])->name('channels.update');
    Route::delete('/channels/{channel}', [ChannelController::class, 'destroy'])->name('channels.destroy');
    Route::get('/channels/{channel}/members', [ChannelMemberController::class, 'index'])->name('channels.members.index');
    Route::post('/channels/{channel}/members', [ChannelMemberController::class, 'store'])->name('channels.members.store');
    Route::delete('/channels/{channel}/members/{user}', [ChannelMemberController::class, 'destroy'])->name('channels.members.destroy');
    Route::post('/channels/{channel}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}/thread', [MessageController::class, 'thread'])->name('messages.thread');
    Route::post('/messages/{message}/replies', [MessageController::class, 'storeReply'])->name('messages.replies.store');
    Route::get('/messages/{message}/delete', [MessageController::class, 'confirmDestroy'])->name('messages.delete.confirm');
    Route::put('/messages/{message}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('/channels/{channel}', [ChannelController::class, 'show'])->name('channels.show');
});

Route::get('/register', [UserController::class, 'create'])->name('register');
Route::post('/register', [UserController::class, 'store'])->name('register.store');

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->name('login.store');
Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
