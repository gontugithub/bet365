<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\PrediccionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/importar', [PartidoController::class, 'importar'])->middleware(['auth:sanctum', 'ability:admin']);
Route::get('/partidos', [PartidoController::class, 'index'])->middleware(['auth:sanctum']);
Route::get('/partidos/fase-actual', [PartidoController::class, 'faseActualPartidos'])->middleware(['auth:sanctum']);

Route::post('/predicciones', [PrediccionController::class, 'store'])->middleware('auth:sanctum');
Route::put('/predicciones/{id}', [PrediccionController::class, 'update'])->middleware('auth:sanctum');