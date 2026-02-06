<?php
use App\Http\Controllers\BuilderAuthController;
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
Route::middleware('guest')->group(function () {
    Route::get('/builder/register', [BuilderAuthController::class, 'showRegistrationForm'])->name('builder.register');
    Route::post('/builder/register', [BuilderAuthController::class, 'register'])->name('builder.register.submit');
    Route::get('/builder/thank-you', [BuilderAuthController::class, 'showThankYou'])->name('builder.thankyou');
});
Route::get('/', function () {
    return view('welcome');
});
