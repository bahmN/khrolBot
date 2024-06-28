<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Auth\Events\Registered;

class AuthenticationController extends Controller {
    public function index() {
        return view('Auth.auth');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            return redirect(RouteServiceProvider::HOME);
        }
    }

    public function logout() {
        Auth::logout();
        return redirect(RouteServiceProvider::HOME);
    }

    public function register() {
        $user = User::create([
            'email' => 'khrol@gmail.com',
            'name' => 'khrol',
            'password' => bcrypt('ral8GFS2')
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
