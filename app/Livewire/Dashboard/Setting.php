<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
class Setting extends Component
{
    use WithFileUploads;
    public $user;
    public $activeTab = 'avatar';
    
    // Form data for personal settings
    public $name;
    public $password;
    public $password_confirmation;
    
    // Appearance settings
    public $selectedBackground;
    public $backgroundOptions = [
        [
            'id' => 'space1',
            'name' => 'Espace Profond',
            'preview' => '/images/bg-space1.png'
        ],
        [
            'id' => 'space2',
            'name' => 'Nébuleuse',
            'preview' => '/images/bg-space2.png'
        ],
        [
            'id' => 'stargate1',
            'name' => 'Porte des Étoiles',
            'preview' => '/images/bg-stargate1.png'
        ],
        [
            'id' => 'stargate2',
            'name' => 'Réseau Stargate',
            'preview' => '/images/bg-stargate2.png'
        ]
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->selectedBackground = $this->user->background ?? 'space1';
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getGravatarUrlProperty()
    {
        return $this->user->getUserAvatarUrl(150);
    }

    /**
     * Upload avatar personnalisé (sans Gravatar)
     */
    public $avatarUpload;
    public function uploadAvatar()
    {
        $this->validate([
            'avatarUpload' => 'required|image|max:2048', // 2MB
        ]);

        $ext = strtolower($this->avatarUpload->getClientOriginalExtension());
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $ext = 'jpg';
        }

        // Écriture directe dans public/avatars/{user_id}/avatar.{ext}
        $destDir = public_path('avatars/' . $this->user->id);
        File::ensureDirectoryExists($destDir);
        $filename = 'avatar.' . $ext;
        $target = $destDir . DIRECTORY_SEPARATOR . $filename;

        // Copier le fichier uploadé vers le répertoire public
        $tmpPath = $this->avatarUpload->getRealPath();
        File::put($target, file_get_contents($tmpPath));

        // Nettoyer les autres formats si présents
        foreach (['jpg','jpeg','png','webp'] as $candidate) {
            $candidateTarget = $destDir . DIRECTORY_SEPARATOR . 'avatar.' . $candidate;
            if ($candidateTarget !== $target && File::exists($candidateTarget)) {
                File::delete($candidateTarget);
            }
        }

        $this->user->refresh();
        $this->dispatch('avatar-refreshed');
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Avatar personnalisé mis à jour.'
        ]);
    }

    /**
     * URL de l'avatar (custom > Gravatar)
     */
    public function getAvatarUrlProperty()
    {
        $dir = public_path('avatars/' . $this->user->id);
        foreach (['jpg','jpeg','png','webp'] as $ext) {
            $path = $dir . DIRECTORY_SEPARATOR . 'avatar.' . $ext;
            if (File::exists($path)) {
                return asset('avatars/' . $this->user->id . '/avatar.' . $ext);
            }
        }
        return $this->gravatarUrl;
    }

    public function refreshAvatar()
    {
        // Force refresh by adding timestamp
        $this->dispatch('avatar-refreshed');
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Avatar actualisé avec succès!'
        ]);
    }

    public function getCanChangeUsernameProperty()
    {
        if (!$this->user->last_username_change) {
            return true;
        }
        
        $lastChange = Carbon::parse($this->user->last_username_change);
        return $lastChange->addDays(30)->isPast();
    }

    public function getDaysUntilUsernameChangeProperty()
    {
        if (!$this->user->last_username_change) {
            return 0;
        }
        
        $lastChange = Carbon::parse($this->user->last_username_change);
        $nextAllowed = $lastChange->addDays(30);
        
        return max(0, $nextAllowed->diffInDays(now()));
    }

    public function savePersonalSettings()
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255', 'unique:users,name,' . $this->user->id],
            ];
            
            if ($this->password) {
                $rules['password'] = ['required', 'confirmed', Password::defaults()];
            }
            
            $this->validate($rules);
            
            $updateData = [];
            
            // Check if username can be changed
            if ($this->name !== $this->user->name) {
                if (!$this->canChangeUsername) {
                    $this->dispatch('toast:error', [
                        'title' => 'Erreur!',
                        'text' => 'Vous ne pouvez pas encore modifier votre pseudo.'
                    ]);
                    return;
                }
                $updateData['name'] = $this->name;
                $updateData['last_username_change'] = now();
            }
            
            if ($this->password) {
                $updateData['password'] = Hash::make($this->password);
            }
            
            if (!empty($updateData)) {
                $this->user->update($updateData);
                $this->user->refresh();
                
                // Clear password fields
                $this->password = '';
                $this->password_confirmation = '';
                
                $this->dispatch('toast:success', [
                    'title' => 'Succès!',
                    'text' => 'Informations personnelles mises à jour avec succès!'
                ]);
            }
            
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Une erreur est survenue lors de la sauvegarde.'
            ]);
        }
    }

    public function selectBackground($backgroundId)
    {
        $this->selectedBackground = $backgroundId;
    }

    public function saveAppearanceSettings()
    {
        try {
            $this->user->update([
                'background' => $this->selectedBackground
            ]);
            
            $this->user->refresh();
            
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Préférences d\'apparence sauvegardées avec succès!'
            ]);
            
            // Dispatch event to update background dynamically
            $this->dispatch('background-updated', [
                'background' => $this->selectedBackground
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Une erreur est survenue lors de la sauvegarde.'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.setting');
    }
}