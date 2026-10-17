<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

// route khusus harus di atas apiResource agar tidak dibaca sebagai {book}
Route::get('books/statistics', [BookController::class, 'statistics']);
Route::get('book/low-stock', [BookController::class, 'lowStock']);

Route::apiResource('authors', AuthorController::class);
Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'destory']);
Route::apiResource('books', BookController::class);