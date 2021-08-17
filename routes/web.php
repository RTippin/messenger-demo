<?php

use App\Http\Controllers\ApiExplorerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'splash')->middleware('guest');
Route::get('demo-logins', [HomeController::class, 'getDemoAccounts'])->middleware('guest');
Route::view('config', 'config')->name('config');
Route::post('heartbeat', [HomeController::class, 'csrfHeartbeat'])->middleware('auth');
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);
Route::prefix('api-explorer')->name('api-explorer.')->group(function () {
    Route::view('/', 'explorer.index')->name('index');
    Route::get('routes', [ApiExplorerController::class, 'getRoutes'])->name('routes');
    Route::get('routes/{route}', [ApiExplorerController::class, 'getRouteResponses'])->name('routes.show');
});
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [DocumentationController::class, 'index'])->name('index');
    Route::get('Installation.md', [DocumentationController::class, 'install'])->name('install');
    Route::get('Configuration.md', [DocumentationController::class, 'config'])->name('config');
    Route::get('Commands.md', [DocumentationController::class, 'commands'])->name('commands');
    Route::get('Broadcasting.md', [DocumentationController::class, 'broadcasting'])->name('broadcasting');
    Route::get('ChatBots.md', [DocumentationController::class, 'bots'])->name('bots');
    Route::get('Calling.md', [DocumentationController::class, 'calling'])->name('calling');
    Route::get('Composer.md', [DocumentationController::class, 'composer'])->name('composer');
    Route::get('Helpers.md', [DocumentationController::class, 'helpers'])->name('helpers');
});
