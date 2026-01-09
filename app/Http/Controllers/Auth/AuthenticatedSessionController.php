<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('login');
    }

    

    /**
     * Handle an incoming authentication request.
     */
  // app/Http/Controllers/Auth/AuthenticatedSessionController.php

public function store(LoginRequest $request)
{
    $request->authenticate(); //
    $request->session()->regenerate(); //

    // Criamos o token logo aqui. 
    // Se o pedido for AJAX/JSON, enviamos o token de volta.
    if ($request->wantsJson()) {
        $token = $request->user()->createToken('login_token')->plainTextToken;
        return response()->json(['token' => $token]);
    }

    return redirect()->intended(route('dashboard')); //
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
{
    // Revogar o token atual do utilizador (se existir)
    if ($request->user()) {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }
    }

    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
}
}
