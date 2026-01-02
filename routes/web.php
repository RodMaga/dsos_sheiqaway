<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/carrinho', function () {
    return view('carrinho');
})->name('carrinho');

Route::get('/detalhes/{id}', function ($id) {
    return view('detalhes', ['tripId' => $id]);
})->name('detalhes');

Route::get('/api/trips', function () {
    return response()->json(
        json_decode(
            file_get_contents(resource_path('data/trips_com_lotacao.json')),
            true
        )
    );
});

Route::get('/api/providers', function () {
    return response()->json(
        json_decode(
            file_get_contents(resource_path('data/providers.json')),
            true
        )
    );
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
