<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Password;
use App\Services\RateLimitService;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.app')]

class ForgotPassword extends Component
{
    public $email = '';
    public $loading = false;
    public $emailSent = false;

    protected $rules = [
        'email' => 'required|email',
    ];

    protected $messages = [
        'email.required' => 'L\'adresse email est requise.',
        'email.email' => 'L\'adresse email doit être valide.',
    ];

    public function sendResetLink()
    {
        // Limiter les envois par email + IP
        $key = 'password_reset:' . strtolower(trim($this->email)) . '|' . request()->ip();
        app(RateLimitService::class)->ensureAllowed($key, 3, 300, 'email');

        $this->loading = true;
        
        $this->validate();

        $status = Password::sendResetLink(
            ['email' => $this->email]
        );

        $this->loading = false;

        if ($status === Password::RESET_LINK_SENT) {
            // Clear limiter on success
            app(RateLimitService::class)->clear($key);
            $this->emailSent = true;
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
            ]);
        } else {
            // Hit limiter on failure
            app(RateLimitService::class)->hit($key, 300);
            throw ValidationException::withMessages([
                'email' => 'Aucun compte trouvé avec cette adresse email.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
