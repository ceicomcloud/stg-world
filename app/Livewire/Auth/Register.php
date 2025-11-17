<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Faction;
use Illuminate\Support\Facades\Cache;
use App\Services\RateLimitService;
use App\Services\AccountGuardService;
use App\Services\LogService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.app')]

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $faction_id = null;
    public $factions = [];
    public $loading = false;

    // Captcha & Honeypot
    public $captchaA = 0;
    public $captchaB = 0;
    public $captchaAnswer = '';
    public $hp = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:8',
        'faction_id' => 'required|exists:factions,id',
    ];

    protected $messages = [
        'name.required' => 'Le pseudo est requis.',
        'name.max' => 'Le pseudo ne peut pas dépasser 255 caractères.',
        'email.required' => 'L\'adresse email est requise.',
        'email.email' => 'L\'adresse email doit être valide.',
        'email.unique' => 'Cette adresse email est déjà utilisée.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        'faction_id.required' => 'Vous devez choisir une faction.',
        'faction_id.exists' => 'La faction sélectionnée n\'existe pas.',
        'captchaAnswer.required' => 'Veuillez résoudre le captcha.',
    ];

    protected function rules()
    {
        return array_merge($this->rules, [
            'captchaAnswer' => ['required', function ($attribute, $value, $fail) {
                if ((int)$value !== ($this->captchaA + $this->captchaB)) {
                    $fail('Captcha incorrect.');
                }
            }],
            'hp' => [function ($attribute, $value, $fail) {
                if (!empty(trim((string)$value))) {
                    $fail('Action non autorisée.');
                }
            }],
        ]);
    }

    /**
     * Calcule la force du mot de passe sur une échelle de 0 à 100
     */
    public function getPasswordStrength()
    {
        if (empty($this->password)) {
            return 0;
        }
        
        $strength = 0;
        
        // Longueur du mot de passe (jusqu'à 40%)
        $length = strlen($this->password);
        if ($length >= 12) {
            $strength += 40;
        } elseif ($length >= 8) {
            $strength += 25;
        } elseif ($length >= 6) {
            $strength += 10;
        } else {
            $strength += 5;
        }
        
        // Présence de lettres majuscules (15%)
        if (preg_match('/[A-Z]/', $this->password)) {
            $strength += 15;
        }
        
        // Présence de lettres minuscules (15%)
        if (preg_match('/[a-z]/', $this->password)) {
            $strength += 15;
        }
        
        // Présence de chiffres (15%)
        if (preg_match('/[0-9]/', $this->password)) {
            $strength += 15;
        }
        
        // Présence de caractères spéciaux (15%)
        if (preg_match('/[^A-Za-z0-9]/', $this->password)) {
            $strength += 15;
        }
        
        return min($strength, 100);
    }
    
    /**
     * Retourne la classe CSS correspondant à la force du mot de passe
     */
    public function getPasswordStrengthClass()
    {
        $strength = $this->getPasswordStrength();
        
        if ($strength >= 80) {
            return 'very-strong';
        } elseif ($strength >= 60) {
            return 'strong';
        } elseif ($strength >= 40) {
            return 'medium';
        } else {
            return 'weak';
        }
    }
    
    /**
     * Retourne le texte correspondant à la force du mot de passe
     */
    public function getPasswordStrengthText()
    {
        $strength = $this->getPasswordStrength();
        
        if ($strength >= 80) {
            return 'Très fort';
        } elseif ($strength >= 60) {
            return 'Fort';
        } elseif ($strength >= 40) {
            return 'Moyen';
        } else {
            return 'Faible';
        }
    }

    public function mount()
    {
        // Charger les factions actives
        $this->factions = Faction::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
            
        // Sélectionner la première faction par défaut s'il y en a
        if ($this->factions->isNotEmpty() && !$this->faction_id) {
            $this->faction_id = $this->factions->first()->id;
        }

        // Initialiser le captcha
        $this->generateCaptcha();
    }
    
    public function register()
    {
        // Limiter les créations de comptes par IP
        $key = 'register:' . request()->ip();
        app(RateLimitService::class)->ensureAllowed($key, 3, 300, 'email');

        // Anti‑VPN & anti‑multicompte
        $ip = request()->ip();
        $ua = request()->header('User-Agent');
        app(AccountGuardService::class)->ensureRegisterAllowed($this->email, $ip, $ua);

        $this->loading = true;
        
        try {
            $this->validate();
        } catch (ValidationException $e) {
            // Rafraîchir le captcha après une erreur de validation
            $this->generateCaptcha();
            throw $e;
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'faction_id' => $this->faction_id,
        ]);

        Auth::login($user);
        // Log de l'inscription avec IP pour détection multi‑compte
        app(LogService::class)->logRegister($user->id, request());
        // Clear limiter on success
        app(RateLimitService::class)->clear($key);
        
        $this->loading = false;
        // Rafraîchir le captcha après succès
        $this->generateCaptcha();
        
        return redirect()->intended('/dashboard');
    }

    public function generateCaptcha()
    {
        $this->captchaA = random_int(1, 9);
        $this->captchaB = random_int(1, 9);
        $this->captchaAnswer = '';
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
