<?php

use App\Http\Controllers\GameController;
use App\Models\Fixture;
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
    return view('app');
});

Route::get('app', [GameController::class, 'index']);
Route::get('teams', [GameController::class, 'teams']);
Route::get('generate-fixtures', [GameController::class, 'generateFixtures']);
Route::get('fixtures', [GameController::class, 'fixtures']);
Route::get('simulation', [GameController::class, 'simulation']);
Route::post('simulate-week/{week}', [GameController::class, 'simulateWeek']);
Route::post('simulate-season', [GameController::class, 'simulateSeason']);
Route::delete('reset-data', [GameController::class, 'resetData']);
