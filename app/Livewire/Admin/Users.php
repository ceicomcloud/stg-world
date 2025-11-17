<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Planet\Planet;
use App\Models\User\UserLog;
use App\Models\User\UserTechnology;
use App\Models\User\UserStat;
use App\Models\Player\PlayerAttackLog;
use App\Models\Faction;
use App\Models\Badge;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Users extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filterRole = '';
    public $filterActive = '';
    public $filterVacation = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour la création d'utilisateur
    public $newUser = [
        'name' => '',
        'email' => '',
        'password' => '',
        'faction_id' => '',
        'role' => 'player',
        'is_active' => true
    ];
    
    // Propriétés pour l'utilisateur sélectionné
    public $selectedUser = null;
    public $selectedUserId = null;
    
    // Propriétés pour les onglets de détails
    public $userDetailTab = 'profile';
    
    // Règles de validation pour la création d'utilisateur
    protected $rules = [
        'newUser.name' => 'required|string|min:3|max:50|unique:users,name',
        'newUser.email' => 'required|email|unique:users,email',
        'newUser.password' => 'required|min:8',
        'newUser.faction_id' => 'required|exists:factions,id',
        'newUser.role' => 'required|in:player,helper,modo,admin,owner',
        'newUser.is_active' => 'boolean'
    ];

    // Formulaires d'action sur l'utilisateur sélectionné
    public $showBanForm = false;
    public $showMessageForm = false;
    public $showEditForm = false;
    public $showGoldForm = false;

    public $banForm = [
        'reason' => '',
        'expires_at' => '', // optionnel (Y-m-d H:i)
    ];

    public $messageForm = [
        'subject' => '',
        'message' => '',
    ];

    public $editForm = [
        'name' => '',
        'email' => '',
        'faction_id' => '',
        'role' => 'player',
        'is_active' => true,
    ];

    public $goldForm = [
        'amount' => 0,
        'reason' => '',
    ];
    // La validation est réalisée au niveau de chaque méthode d'action
    
    /**
     * Réinitialiser la pagination lors de la recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Réinitialiser la pagination lors du changement de filtres
     */
    public function updatingFilterRole()
    {
        $this->resetPage();
    }
    
    public function updatingFilterActive()
    {
        $this->resetPage();
    }
    
    public function updatingFilterVacation()
    {
        $this->resetPage();
    }
    
    /**
     * Trier les résultats
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    /**
     * Changer d'onglet principal
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        
        // Réinitialiser les données si nécessaire
        if ($tab === 'create') {
            $this->resetNewUser();
        } elseif ($tab === 'list') {
            $this->selectedUser = null;
            $this->selectedUserId = null;
            $this->userDetailTab = 'profile';
            $this->resetActionForms();
        }
    }
    
    /**
     * Changer d'onglet de détails utilisateur
     */
    public function setUserDetailTab($tab)
    {
        $this->userDetailTab = $tab;
    }
    
    /**
     * Réinitialiser le formulaire de création d'utilisateur
     */
    public function resetNewUser()
    {
        $this->newUser = [
            'name' => '',
            'email' => '',
            'password' => '',
            'faction_id' => '',
            'role' => 'player',
            'is_active' => true
        ];
        $this->resetErrorBag();
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function createUser()
    {
        $this->validate();
        
        $user = new User();
        $user->name = $this->newUser['name'];
        $user->email = $this->newUser['email'];
        $user->password = Hash::make($this->newUser['password']);
        $user->faction_id = $this->newUser['faction_id'];
        $user->role = $this->newUser['role'];
        $user->is_active = $this->newUser['is_active'];
        $user->save();
        
        // Ajouter un log pour la création d'utilisateur
        $this->logAction(
            'Création d\'utilisateur',
            'admin',
            'Création de l\'utilisateur "' . $user->name . '" avec le rôle "' . $user->role . '"',
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'faction_id' => $user->faction_id,
                'is_active' => $user->is_active
            ]
        );
        
        // Rediriger vers la liste avec un message de succès
        $this->dispatch('admin:toast:success', ['message' => 'Utilisateur créé avec succès']);
        $this->setActiveTab('list');
    }
    
    /**
     * Sélectionner un utilisateur pour voir ses détails
     */
    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->selectedUser = User::with(['faction', 'planets', 'badges', 'technologies', 'userStat'])->find($userId);
        $this->userDetailTab = 'profile';
        $this->activeTab = 'detail';

        // Pré-remplir le formulaire d'édition
        if ($this->selectedUser) {
            $this->editForm = [
                'name' => $this->selectedUser->name,
                'email' => $this->selectedUser->email,
                'faction_id' => $this->selectedUser->faction_id,
                'role' => $this->selectedUser->role,
                'is_active' => (bool)$this->selectedUser->is_active,
            ];
        }
    }
    
    /**
     * Obtenir les planètes de l'utilisateur sélectionné
     */
    public function getUserPlanets()
    {
        if (!$this->selectedUserId) {
            return [];
        }
        
        return Planet::where('user_id', $this->selectedUserId)
            ->with('templatePlanet')
            ->get();
    }
    
    /**
     * Obtenir les logs de l'utilisateur sélectionné
     */
    public function getUserLogs()
    {
        if (!$this->selectedUserId) {
            return [];
        }
        
        return UserLog::where('user_id', $this->selectedUserId)
            ->orWhere('target_user_id', $this->selectedUserId)
            ->with(['user', 'targetUser', 'planet'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    
    /**
     * Obtenir les badges de l'utilisateur sélectionné
     */
    public function getUserBadges()
    {
        if (!$this->selectedUserId || !$this->selectedUser) {
            return [];
        }
        
        return $this->selectedUser->badges;
    }
    
    /**
     * Obtenir les technologies de l'utilisateur sélectionné
     */
    public function getUserTechnologies()
    {
        if (!$this->selectedUserId) {
            return [];
        }
        
        return UserTechnology::where('user_id', $this->selectedUserId)
            ->with('technology')
            ->get();
    }
    
    /**
     * Obtenir les statistiques de l'utilisateur sélectionné
     */
    public function getUserStats()
    {
        if (!$this->selectedUserId || !$this->selectedUser) {
            return null;
        }
        
        return $this->selectedUser->userStat;
    }
    
    /**
     * Obtenir les logs d'attaque de l'utilisateur sélectionné
     */
    public function getPlayerAttackLogs()
    {
        if (!$this->selectedUserId) {
            return [];
        }
        
        return PlayerAttackLog::where('attacker_user_id', $this->selectedUserId)
            ->orWhere('defender_user_id', $this->selectedUserId)
            ->with(['attackerUser', 'defenderUser', 'attackerPlanet', 'defenderPlanet'])
            ->orderBy('attacked_at', 'desc')
            ->paginate(10);
    }
    
    /**
     * Obtenir la liste des factions pour le formulaire
     */
    public function getFactions()
    {
        return Faction::all();
    }
    
    /**
     * Obtenir la liste des utilisateurs filtrée
     */
    public function getUsers()
    {
        $query = User::query()
            ->with('faction')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterRole, function ($query) {
                $query->where('role', $this->filterRole);
            })
            ->when($this->filterActive !== '', function ($query) {
                $query->where('is_active', $this->filterActive === 'active');
            })
            ->when($this->filterVacation !== '', function ($query) {
                $query->where('vacation_mode', $this->filterVacation === 'vacation');
            });
        
        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.users', [
            'users' => $this->getUsers(),
            'factions' => $this->getFactions(),
            'userPlanets' => $this->getUserPlanets(),
            'userLogs' => $this->getUserLogs(),
            'userBadges' => $this->getUserBadges(),
            'userTechnologies' => $this->getUserTechnologies(),
            'userStats' => $this->getUserStats(),
            'playerAttackLogs' => $this->getPlayerAttackLogs(),
        ])->title('Gestion des utilisateurs');
    }

    /**
     * ====== Actions Admin sur l'utilisateur sélectionné ======
     */

    public function toggleActionForm(string $form): void
    {
        if (!$this->selectedUserId) return;

        $toggles = [
            'ban' => 'showBanForm',
            'message' => 'showMessageForm',
            'edit' => 'showEditForm',
            'gold' => 'showGoldForm',
        ];
        if (!isset($toggles[$form])) return;

        // Fermer les autres formulaires
        foreach ($toggles as $key => $prop) {
            $this->$prop = ($key === $form) ? !$this->$prop : false;
        }
    }

    public function resetActionForms(): void
    {
        $this->showBanForm = false;
        $this->showMessageForm = false;
        $this->showEditForm = false;
        $this->showGoldForm = false;

        $this->banForm = ['reason' => '', 'expires_at' => ''];
        $this->messageForm = ['subject' => '', 'message' => ''];
        $this->goldForm = ['amount' => 0, 'reason' => ''];
    }

    public function banSelectedUser(): void
    {
        if (!$this->selectedUserId || !auth()->user()?->hasModeratorRights()) {
            $this->dispatch('admin:toast:error', ['message' => 'Action non autorisée']);
            return;
        }

        $this->validate([
            'banForm.reason' => 'required|string|min:3|max:255',
            'banForm.expires_at' => 'nullable|date',
        ]);

        $user = User::find($this->selectedUserId);
        if (!$user) {
            $this->dispatch('admin:toast:error', ['message' => 'Utilisateur introuvable']);
            return;
        }

        $expires = null;
        if (!empty($this->banForm['expires_at'])) {
            try {
                $expires = Carbon::parse($this->banForm['expires_at']);
            } catch (\Exception $e) {
                $this->dispatch('admin:toast:error', ['message' => 'Date d\'expiration invalide']);
                return;
            }
        }

        \App\Models\User\UserSanction::create([
            'user_id' => $user->id,
            'sanctioned_by' => auth()->id(),
            'type' => 'ban',
            'reason' => $this->banForm['reason'],
            'expires_at' => $expires,
            'is_active' => true,
        ]);

        $this->logAction(
            'Ban utilisateur',
            'admin',
            'Ban de l\'utilisateur "' . $user->name . '"',
            [
                'user_id' => $user->id,
                'reason' => $this->banForm['reason'],
                'expires_at' => $expires?->toDateTimeString(),
            ]
        );

        // Notification système
        (new \App\Services\PrivateMessageService())->createSystemNotification(
            $user,
            'Sanction: Bannissement',
            "Votre compte a été banni. Raison: {$this->banForm['reason']}" . ($expires ? " (jusqu'au {$expires->format('d/m/Y H:i')})" : '')
        );

        $this->dispatch('admin:toast:success', ['message' => 'Utilisateur banni']);
        $this->resetActionForms();
    }

    public function toggleSelectedUserActive(bool $active): void
    {
        if (!$this->selectedUserId || !auth()->user()?->hasModeratorRights()) {
            $this->dispatch('admin:toast:error', ['message' => 'Action non autorisée']);
            return;
        }
        $user = User::find($this->selectedUserId);
        if (!$user) {
            $this->dispatch('admin:toast:error', ['message' => 'Utilisateur introuvable']);
            return;
        }
        $user->is_active = $active;
        $user->save();

        $this->logAction(
            $active ? 'Activation utilisateur' : 'Désactivation utilisateur',
            'admin',
            ($active ? 'Activation' : 'Désactivation') . ' de l\'utilisateur "' . $user->name . '"',
            [
                'user_id' => $user->id,
                'is_active' => $active,
            ]
        );

        $this->dispatch('admin:toast:success', ['message' => $active ? 'Utilisateur activé' : 'Utilisateur désactivé']);
        $this->selectedUser = $user->fresh(['faction', 'planets', 'badges', 'technologies', 'userStat']);
    }

    public function sendMessageToSelectedUser(): void
    {
        if (!$this->selectedUserId || !auth()->user()?->hasModeratorRights()) {
            $this->dispatch('admin:toast:error', ['message' => 'Action non autorisée']);
            return;
        }
        $this->validate([
            'messageForm.subject' => 'required|string|min:3|max:100',
            'messageForm.message' => 'required|string|min:3',
        ]);

        $user = User::find($this->selectedUserId);
        if (!$user) {
            $this->dispatch('admin:toast:error', ['message' => 'Utilisateur introuvable']);
            return;
        }

        // Créer une conversation admin -> utilisateur et envoyer le message
        $conv = \App\Models\Messaging\PrivateConversation::create([
            'title' => $this->messageForm['subject'],
            'type' => 'admin',
            'created_by' => auth()->id(),
            'last_message_at' => Carbon::now(),
            'is_active' => true,
        ]);
        $conv->addParticipant(auth()->user());
        $conv->addParticipant($user);

        $msg = \App\Models\Messaging\PrivateMessage::create([
            'conversation_id' => $conv->id,
            'user_id' => auth()->id(),
            'message' => $this->messageForm['message'],
            'is_system_message' => false,
        ]);
        $conv->updateLastMessageTime();

        $this->logAction(
            'Message admin envoyé',
            'message',
            'Message envoyé à "' . $user->name . '" : {subject}',
            [
                'recipient_id' => $user->id,
                'subject' => $this->messageForm['subject'],
                'message_id' => $msg->id,
            ],
            null,
            $user->id,
            \App\Models\User\UserLog::SEVERITY_INFO
        );

        $this->dispatch('admin:toast:success', ['message' => 'Message envoyé']);
        $this->resetActionForms();
    }

    public function updateSelectedUser(): void
    {
        if (!$this->selectedUserId || !auth()->user()?->hasModeratorRights()) {
            $this->dispatch('admin:toast:error', ['message' => 'Action non autorisée']);
            return;
        }
        $user = User::find($this->selectedUserId);
        if (!$user) {
            $this->dispatch('admin:toast:error', ['message' => 'Utilisateur introuvable']);
            return;
        }

        // Valider l'ensemble du formulaire d'édition (avec unicité)
        $this->validate([
            'editForm.name' => 'required|string|min:3|max:50|unique:users,name,' . $user->id,
            'editForm.email' => 'required|email|unique:users,email,' . $user->id,
            'editForm.faction_id' => 'required|exists:factions,id',
            'editForm.role' => 'required|in:player,helper,modo,admin,owner',
            'editForm.is_active' => 'boolean',
        ]);

        $user->name = $this->editForm['name'];
        $user->email = $this->editForm['email'];
        $user->faction_id = $this->editForm['faction_id'];
        $user->role = $this->editForm['role'];
        $user->is_active = (bool)$this->editForm['is_active'];
        $user->save();

        $this->logAction(
            'Modification utilisateur',
            'admin',
            'Mise à jour du profil de "' . $user->name . '"',
            [
                'user_id' => $user->id,
                'role' => $user->role,
                'faction_id' => $user->faction_id,
                'is_active' => $user->is_active,
            ]
        );

        $this->dispatch('admin:toast:success', ['message' => 'Profil mis à jour']);
        $this->selectedUser = $user->fresh(['faction', 'planets', 'badges', 'technologies', 'userStat']);
        $this->resetActionForms();
    }

    public function addGoldToSelectedUser(): void
    {
        if (!$this->selectedUserId || !auth()->user()?->hasModeratorRights()) {
            $this->dispatch('admin:toast:error', ['message' => 'Action non autorisée']);
            return;
        }

        $this->validate([
            'goldForm.amount' => 'required|integer|min:1',
            'goldForm.reason' => 'required|string|min:3',
        ]);

        $user = User::find($this->selectedUserId);
        if (!$user) {
            $this->dispatch('admin:toast:error', ['message' => 'Utilisateur introuvable']);
            return;
        }

        DB::transaction(function () use ($user) {
            $amount = (int)$this->goldForm['amount'];
            $reason = $this->goldForm['reason'];

            $user->gold_balance = (int)$user->gold_balance + $amount;
            $user->save();

            $title = "Crédit d'or";
            $message = "<div class='system-message-content'>";
            $message .= "<p>💰 <strong>Crédit d'or</strong></p>";
            $message .= "<p>Montant crédité: <strong>" . number_format($amount) . "</strong></p>";
            $message .= "<p>Motif: " . e($reason) . "</p>";
            $message .= "<p>Solde actuel: <strong>" . number_format((int)$user->gold_balance) . "</strong></p>";
            $message .= "</div>";

            (new \App\Services\PrivateMessageService())->createSystemNotification($user, $title, $message);

            $this->logAction(
                'Crédit or',
                'resource',
                'Crédit d\'or de {amount} à {user_name}',
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'amount' => $amount,
                    'reason' => $reason,
                    'new_balance' => (int)$user->gold_balance,
                ]
            );
        });

        $this->dispatch('admin:toast:success', ['message' => "Or crédité avec notification"]);
        $this->selectedUser = $user->fresh(['faction', 'planets', 'badges', 'technologies', 'userStat']);
        $this->resetActionForms();
    }

    public function incrementTechnology(int $userTechId): void
    {
        if (!$this->selectedUserId || !auth()->user()?->hasModeratorRights()) {
            $this->dispatch('admin:toast:error', ['message' => 'Action non autorisée']);
            return;
        }
        $tech = \App\Models\User\UserTechnology::find($userTechId);
        if (!$tech || $tech->user_id !== $this->selectedUserId) {
            $this->dispatch('admin:toast:error', ['message' => 'Technologie introuvable']);
            return;
        }
        $tech->level = (int)$tech->level + 1;
        $tech->save();

        $this->logAction(
            'Tech +1',
            'admin',
            'Augmentation niveau technologie {tech_id} pour {user_name}',
            [
                'user_id' => $this->selectedUserId,
                'user_name' => $this->selectedUser?->name,
                'tech_id' => $tech->technology_id,
                'new_level' => $tech->level,
            ]
        );

        $this->dispatch('admin:toast:success', ['message' => 'Technologie augmentée']);
        $this->selectedUser = User::with(['faction', 'planets', 'badges', 'technologies', 'userStat'])->find($this->selectedUserId);
    }

    public function decrementTechnology(int $userTechId): void
    {
        if (!$this->selectedUserId || !auth()->user()?->hasModeratorRights()) {
            $this->dispatch('admin:toast:error', ['message' => 'Action non autorisée']);
            return;
        }
        $tech = \App\Models\User\UserTechnology::find($userTechId);
        if (!$tech || $tech->user_id !== $this->selectedUserId) {
            $this->dispatch('admin:toast:error', ['message' => 'Technologie introuvable']);
            return;
        }
        if ($tech->is_researching) {
            $this->dispatch('admin:toast:error', ['message' => 'Recherche en cours, impossible de diminuer']);
            return;
        }
        $newLevel = max(0, (int)$tech->level - 1);
        $tech->level = $newLevel;
        $tech->save();

        $this->logAction(
            'Tech -1',
            'admin',
            'Diminution niveau technologie {tech_id} pour {user_name}',
            [
                'user_id' => $this->selectedUserId,
                'user_name' => $this->selectedUser?->name,
                'tech_id' => $tech->technology_id,
                'new_level' => $tech->level,
            ]
        );

        $this->dispatch('admin:toast:success', ['message' => 'Technologie diminuée']);
        $this->selectedUser = User::with(['faction', 'planets', 'badges', 'technologies', 'userStat'])->find($this->selectedUserId);
    }
}