<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevEmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('status', 'Email já verificado!');
        }

        $user->markEmailAsVerified();

        return redirect()->route('dashboard')->with('status', 'Email verificado com sucesso!');
    }
}