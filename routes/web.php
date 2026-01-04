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

Route::get('/perfil/reservas', function () {
    return view('profile.reservas');
})->middleware(['auth'])->name('profile.reservas');

// Rota para API de trip específica (para detalhes)
Route::get('/api/trips/{id}', function ($id) {
    $trips = json_decode(file_get_contents(resource_path('data/trips_com_lotacao.json')), true);
    $trip = collect($trips)->firstWhere('id', $id);
    
    if (!$trip) {
        return response()->json(['error' => 'Viagem não encontrada'], 404);
    }
    
    return response()->json($trip);
});

// Checkout e reservas
Route::post('/checkout/process', function () {
    // Em produção, aqui processaria pagamento real
    return response()->json([
        'success' => true,
        'message' => 'Compra processada com sucesso',
        'ticket_code' => 'SHQ-' . strtoupper(uniqid())
    ]);
})->middleware('auth')->name('checkout.process');

Route::get('/api/user/cart', function () {
    // Retornar carrinho do usuário (do localStorage ou banco)
    $cart = json_decode(request()->cookie('shopping_cart'), true) ?? [];
    return response()->json($cart);
})->middleware('auth');

Route::post('/api/user/cart/sync', function () {
    // Sincronizar carrinho do localStorage com sessão
    $cart = request()->json('cart', []);
    
    // Armazenar na sessão (em produção, seria no banco)
    session(['user_cart' => $cart]);
    
    return response()->json(['success' => true]);
})->middleware('auth');

require __DIR__.'/auth.php';
