<?php
use App\Http\Controllers\CategoryController;

Route::get('/categories/list', [CategoryController::class, 'list']);
