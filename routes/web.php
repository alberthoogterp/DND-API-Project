<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\mainController;

Route::get('/', function () {
    return redirect()->route("mainpage");
});
Route::get('/main', [mainController::class, "main"])->name("mainpage");
Route::get('/newResult', [mainController::class, "newResultRequest"])->name("newResult");