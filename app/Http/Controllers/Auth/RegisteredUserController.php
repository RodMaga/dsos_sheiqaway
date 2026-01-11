<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        try {
            // Call the stored procedure to insert user
            DB::statement('CALL InsertUser(?, ?, ?, ?)', [
                $request->name,
                $request->email,
                Hash::make($request->password),
                $request->phone
            ]);

            // Get the ID of the newly inserted user
            $userId = DB::select('SELECT LAST_INSERT_ID() as id')[0]->id;

            // Retrieve the user model
            $user = User::find($userId);

            event(new Registered($user));
            
            return redirect(route('login'))->with('status', 'Conta criada! Verifique seu email.');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->withErrors(['email' => 'Este email já está registrado.'])->withInput();
            }
            
            return back()->withErrors(['error' => 'Erro ao criar conta. Tente novamente.'])->withInput();
        }
    }
}
