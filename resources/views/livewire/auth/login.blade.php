<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth-custom')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('pos.terminal', absolute: false), navigate: false);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos. Intenta de nuevo en $seconds segundos.",
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <div class="login-brand-icon">SC</div>
            <div>
                <h1>Sneakers Colima</h1>
                <span>Sistema POS v2.0</span>
            </div>
        </div>

        <div class="login-title">
            <h2>Iniciar Sesi&oacute;n</h2>
            <p>Ingresa tus credenciales para acceder al sistema</p>
        </div>

        <form wire:submit="login" class="login-form">
            <div class="form-group">
                <label for="email">Correo electr&oacute;nico</label>
                <input wire:model="email" type="email" id="email" placeholder="correo@sneakerscolima.com" required autofocus>
                @error('email')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contrase&ntilde;a</label>
                <input wire:model="password" type="password" id="password" placeholder="********" required>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <label class="login-remember">
                <input type="checkbox" wire:model="remember">
                Recordar sesi&oacute;n
            </label>

            <button type="submit" class="login-submit">Ingresar</button>
        </form>

        <div class="login-security">
            <span class="login-security-dot"></span>
            Conexi&oacute;n segura &middot; Sistema protegido
        </div>
    </div>
</div>
