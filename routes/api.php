<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReturnLoanController;
use Illuminate\Support\Facades\Route;

Route::post('v1/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{book}', [BookController::class, 'show']); // Detalle libro
    Route::post('books', [BookController::class, 'store'])->middleware('can:create,App\Models\Book'); // Crear libro
    Route::put('books/{book}', [BookController::class, 'update'])->middleware('can:update,book'); // Actualizar libro
    Route::delete('books/{book}', [BookController::class, 'destroy'])->middleware('can:delete,book'); // Eliminar libro
   
    Route::get('loans', [LoanController::class, 'index']);
    Route::post('loans', [LoanController::class, 'store']);
    Route::post('loans/{loan}/return', ReturnLoanController::class);
});