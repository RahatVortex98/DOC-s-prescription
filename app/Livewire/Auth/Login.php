<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sign In - Bikrify Prescription Studio')]
#[Layout('layouts.app')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'These credentials do not match our records.');

            return;
        }

        $user = Auth::user();

        if ($user->tenant && $user->tenant->isSuspended()) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            $this->addError('email', 'Your clinic account is suspended. Please contact Bikrify support.');

            return;
        }

        session()->regenerate();

        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('admin.tenants'));
        }

        return redirect()->intended(route('prescription.builder'));
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
