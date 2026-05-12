<?php

use Illuminate\Support\Facades\Route;
use LivewireFilemanager\Filemanager\Http\Controllers\Files\FileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/file-manager', function () {
    return view('filemanager');
})->name('filemanager');

Route::get('/files/{path}', [FileController::class, 'show'])
    ->where('path', '.*')
    ->name('assets.show');