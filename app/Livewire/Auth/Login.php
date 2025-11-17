<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\LogService;
use App\Models\User\UserLog;
use App\Models\User;
use App\Traits\LogsUserActions;
use App\Services\RateLimitService;
use App\Services\AccountGuardService;

#[Layout('components.layouts.app')]

class Login extends Component
{
    use LogsUserActions;

    public $email = '';
    public $password = '';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected $messages = [
        'email.required' => 'L\'adresse email est requise.',
        'email.email' => 'L\'adresse email doit être valide.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
    ];

    public function login()
    {
        // Rate limiting key based on email + IP
        $key = 'login:' . strtolower(trim($this->email)) . '|' . request()->ip();
        app(RateLimitService::class)->ensureAllowed($key, 5, 60, 'email');

        // Anti‑VPN sur connexion
        app(AccountGuardService::class)->ensureLoginAllowed(request()->ip());

        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();

            auth()->user()->update([
                'last_login_at' => now(),
            ]);
            
            // Log successful login
            $this->logLogin();
            // Clear limiter on success
            app(RateLimitService::class)->clear($key);
            
            return redirect()->intended('/dashboard');
        }
        
        // Log failed login attempt (associate to user if email exists)
        $existingUser = User::where('email', $this->email)->first();
        $req = request();
        if ($existingUser) {
            app(LogService::class)->log(
                $existingUser->id,
                'login_failed',
                UserLog::CATEGORY_AUTH,
                'Tentative de connexion échouée',
                ['email' => $this->email],
                null,
                null,
                UserLog::SEVERITY_WARNING,
                $req
            );
        } else {
            // Utiliser le log applicatif pour les tentatives anonymes (email inconnu)
            Log::warning('Tentative de connexion échouée (email inconnu)', [
                'email' => $this->email,
                'ip' => $req->ip(),
                'user_agent' => $req->userAgent(),
            ]);
        }

        // Hit limiter on failure
        app(RateLimitService::class)->hit($key, 60);
        
        throw ValidationException::withMessages([
            'email' => 'Ces identifiants ne correspondent à aucun compte.',
        ]);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
