<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileManagerController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/file-manager', function () {
    return view('filemanager');
})->name('filemanager');

Route::get('/files/{path}', [FileManagerController::class, 'show'])
    ->where('path', '.*')
    ->name('assets.show');

// ============================================
// API ROUTES
// ============================================
Route::prefix('api')->group(function () {
    // Files
    Route::get('/files', [FileManagerController::class, 'getFiles']);
    Route::delete('/files/{id}', [FileManagerController::class, 'deleteFile']);
    Route::post('/files/{id}/restore', [FileManagerController::class, 'restoreFile']);
    Route::get('/files/{id}/download', [FileManagerController::class, 'downloadFile']);
    Route::get('/files/bulk-download', [FileManagerController::class, 'bulkDownload']);
    Route::post('/files/{id}/share', [FileManagerController::class, 'shareFile']);
    Route::get('/files/{id}/preview', [FileManagerController::class, 'previewFile']);
    Route::post('/files/bulk-delete', [FileManagerController::class, 'bulkDelete']);

    // Folders
    Route::get('/folders', [FileManagerController::class, 'getFolders']);
    Route::post('/folders', [FileManagerController::class, 'createFolder']);
    Route::get('/folders/{id}/download', [FileManagerController::class, 'downloadFolder']);

    // Other
    Route::get('/stats', [FileManagerController::class, 'getStats']);
    Route::post('/upload', [FileManagerController::class, 'upload']);
    Route::post('/move', [FileManagerController::class, 'moveItem']);

    // Share
    Route::get('/share/{token}', [FileManagerController::class, 'viewShared'])->name('share.view');
});