<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\DevEmailVerificationController;

// ------------------- DESENVOLVIMENTO - VERIFICAÇÃO MANUAL -------------------
Route::middleware('auth')->group(function () {
    Route::get('/dev/verify-email', [DevEmailVerificationController::class, 'verify'])->name('dev.verify');
});

// ------------------- ADMIN BACKOFFICE -------------------
Route::middleware(['auth', 'verified', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/reservations', [AdminController::class, 'reservations'])->name('admin.reservations');
    Route::post('/users/{id}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('admin.users.toggle');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/reservations/{id}/edit', [AdminController::class, 'editReservation'])->name('admin.reservations.edit');
    Route::put('/reservations/{id}', [AdminController::class, 'updateReservation'])->name('admin.reservations.update');
    Route::delete('/reservations/{id}', [AdminController::class, 'deleteReservation'])->name('admin.reservations.delete');
});

// ------------------- PAGAMENTOS -------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/payment/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
});

// ------------------- RESERVAS (API) -------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/reservas', [ReservationController::class, 'index'])->name('reservas.index');
    Route::get('/api/reservas/{id}', [ReservationController::class, 'show'])->name('reservas.show');
    Route::post('/reservar', [ReservationController::class, 'store'])->name('reservar.store');
    Route::post('/reservar-multiplas', [ReservationController::class, 'storeMultiple'])->name('reservar.multiple');
    Route::put('/api/reservas/{id}', [ReservationController::class, 'update'])->name('reservas.update');
    Route::post('/api/reservas/{id}/cancelar', [ReservationController::class, 'cancel'])->name('reservas.cancel');
    Route::delete('/api/reservas/{id}', [ReservationController::class, 'destroy'])->name('reservas.destroy');
    Route::get('/api/viagens/{tripId}/lugares-disponiveis', [ReservationController::class, 'lugaresDisponiveis'])->name('viagens.lugares');
    Route::get('/api/viagens/lugares-disponiveis/bulk', [ReservationController::class, 'lugaresDisponiveisBulk'])->name('viagens.lugares.bulk');
    Route::get('/api/user-points', function () {
        return response()->json(['points' => auth()->user()->points ?? 0]);
    });
});

// ------------------- ROTAS PÚBLICAS -------------------
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// ------------------- AUTENTICAÇÃO -------------------
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', function () { return view('forgot-password'); })->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', function ($token, \Illuminate\Http\Request $request) {
        return view('reset-password', ['request' => $request, 'token' => $token]);
    })->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', function () { return view('verify-email'); })->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ------------------- ROTAS PROTEGIDAS -------------------
Route::middleware(['auth', 'verified'])->group(function () {
    // Páginas
    Route::get('/viagens', function () { return view('viagens'); })->name('viagens');
    Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');
    Route::get('/carrinho', function () { return view('carrinho'); })->name('carrinho');
    Route::get('/detalhes/{id}', function ($id) { return view('detalhes', ['tripId' => $id]); })->name('detalhes');
    
    // Perfil e Reservas do Utilizador
    Route::get('/perfil/reservas', [ProfileController::class, 'reservas'])->name('profile.reservas');
    Route::get('/perfil/reservas/{id}/editar', [ProfileController::class, 'editReservation'])->name('profile.reservations.edit');
    Route::put('/perfil/reservas/{id}', [ProfileController::class, 'updateReservation'])->name('profile.reservations.update');
    Route::delete('/reservas/{id}/cancelar', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    
    // Gestão de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // APIs de Dados
    Route::prefix('api')->group(function () {
        Route::get('/trips', function () {
            try {
                $response = \Http::withoutVerifying()->timeout(10)->get('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens');
                return response()->json($response->json());
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erro ao carregar viagens'], 500);
            }
        });
        Route::get('/providers', function () {
            return response()->json(json_decode(file_get_contents(resource_path('data/providers.json')), true));
        });
        Route::get('/trips/{id}', function ($id) {
            try {
                $response = \Http::withoutVerifying()->timeout(10)->get('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens');
                $trips = collect($response->json());
                $trip = $trips->firstWhere('id', (int)$id);
                return $trip ? response()->json($trip) : response()->json(['error' => 'Viagem não encontrada'], 404);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erro ao carregar viagem'], 500);
            }
        });
    });
});

// ------------------- COMANDOS CONSOLE -------------------
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');