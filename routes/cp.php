<?php

use Foorintodev\FormImport\Http\Controllers\ImportController;
use Foorintodev\FormImport\Http\Controllers\ManualController;
use Illuminate\Support\Facades\Route;

Route::prefix('form-import')->name('form-import.')->group(function () {
    Route::get('/', [ImportController::class, 'index'])->name('index');
    Route::post('upload', [ImportController::class, 'upload'])->name('upload');
    Route::post('import', [ImportController::class, 'import'])->name('import');

    Route::get('manual', [ManualController::class, 'create'])->name('manual.create');
    Route::post('manual', [ManualController::class, 'store'])->name('manual.store');
});
