<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;


// ------------------- RESERVAS -------------------
use App\Http\Controllers\ReservationController;
Route::middleware('auth')->group(function () {
    Route::get('/api/reservas', [ReservationController::class, 'index'])->name('reservas.index');
    Route::post('/reservar', [ReservationController::class, 'store'])->name('reservar.store');
    Route::post('/reservar-multiplas', [ReservationController::class, 'storeMultiple'])->name('reservar.multiple');
});

// ------------------- ROTAS PÚBLICAS -------------------
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// ------------------- ROTAS DE AUTENTICAÇÃO -------------------
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ------------------- ROTAS PROTEGIDAS (APÓS LOGIN) -------------------
Route::middleware(['auth'])->group(function () {
    // Páginas do Utilizador
    Route::get('/viagens', function () { return view('viagens'); })->name('viagens');
    Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');
    Route::get('/carrinho', function () { return view('carrinho'); })->name('carrinho');
    Route::get('/detalhes/{id}', function ($id) { return view('detalhes', ['tripId' => $id]); })->name('detalhes');
    Route::get('/perfil/reservas', function () { return view('profile.reservas'); })->name('profile.reservas');

    // Gestão de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // APIs de Dados (Protegidas)
    Route::prefix('api')->group(function () {
        Route::get('/trips', function () {
            return response()->json(json_decode(file_get_contents(resource_path('data/trips_com_lotacao.json')), true));
        });
        Route::get('/providers', function () {
            return response()->json(json_decode(file_get_contents(resource_path('data/providers.json')), true));
        });
        Route::get('/trips/{id}', function ($id) {
            $trips = json_decode(file_get_contents(resource_path('data/trips_com_lotacao.json')), true);
            $trip = collect($trips)->firstWhere('id', $id);
            return $trip ? response()->json($trip) : response()->json(['error' => 'Viagem não encontrada'], 404);
        });
        Route::get('/user/cart', function () {
            $cart = json_decode(request()->cookie('shopping_cart'), true) ?? [];
            return response()->json($cart);
        });
    });

    // Processos de Checkout e Sincronização
    Route::post('/checkout/process', function () {
        return response()->json([
            'success' => true,
            'message' => 'Compra processada com sucesso',
            'ticket_code' => 'SHQ-' . strtoupper(uniqid())
        ]);
    })->name('checkout.process');

    // Sincronização de carrinho (Usando Sanctum para suporte a Token)
    Route::post('/api/user/cart/sync', function () {
        $cart = request()->json('cart', []);
        session(['user_cart' => $cart]);
        return response()->json(['success' => true]);
    })->middleware('auth:sanctum');
});

// ------------------- ROTAS API EXTERNAS (SANCTUM) -------------------
Route::middleware('auth:sanctum')->get('/api/user/cart', function () {
    $cart = json_decode(request()->cookie('shopping_cart'), true) ?? [];
    return response()->json($cart);
});

// ------------------- COMANDOS CONSOLE -------------------
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');