<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User\UserLog;
use App\Models\Player\PlayerAttackLog;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Logs extends Component
{
    use WithPagination;
    
    // Onglet actif
    public string $activeTab = 'user_logs';
    
    // Filtres pour les logs utilisateurs
    public ?string $userLogActionType = null;
    public ?string $userLogCategory = null;
    public ?string $userLogSeverity = null;
    public string $userLogSearch = '';
    public int $userLogPerPage = 15;
    
    // Filtres pour les logs d'attaque
    public ?string $attackLogType = null;
    public ?bool $attackLogWon = null;
    public string $attackLogSearch = '';
    public int $attackLogPerPage = 15;
    
    // Tri
    public string $userLogSortField = 'created_at';
    public string $userLogSortDirection = 'desc';
    public string $attackLogSortField = 'attacked_at';
    public string $attackLogSortDirection = 'desc';
    
    // Sélection pour suppression en masse
    public array $selectedUserLogs = [];
    public array $selectedAttackLogs = [];
    public bool $selectAllUserLogs = false;
    public bool $selectAllAttackLogs = false;
    
    /**
     * Règles de validation
     */
    protected function rules()
    {
        return [
            'userLogActionType' => 'nullable|string',
            'userLogCategory' => 'nullable|string',
            'userLogSeverity' => 'nullable|string',
            'userLogSearch' => 'nullable|string',
            'userLogPerPage' => 'required|integer|min:5|max:100',
            'attackLogType' => 'nullable|string',
            'attackLogWon' => 'nullable|boolean',
            'attackLogSearch' => 'nullable|string',
            'attackLogPerPage' => 'required|integer|min:5|max:100',
        ];
    }
    
    /**
     * Définir l'onglet actif
     */
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }
    
    /**
     * Réinitialiser les filtres des logs utilisateurs
     */
    public function resetUserLogFilters(): void
    {
        $this->userLogActionType = null;
        $this->userLogCategory = null;
        $this->userLogSeverity = null;
        $this->userLogSearch = '';
        $this->resetPage();
    }
    
    /**
     * Réinitialiser les filtres des logs d'attaque
     */
    public function resetAttackLogFilters(): void
    {
        $this->attackLogType = null;
        $this->attackLogWon = null;
        $this->attackLogSearch = '';
        $this->resetPage();
    }
    
    /**
     * Trier les logs utilisateurs
     */
    public function sortUserLogs(string $field): void
    {
        if ($this->userLogSortField === $field) {
            $this->userLogSortDirection = $this->userLogSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->userLogSortField = $field;
            $this->userLogSortDirection = 'asc';
        }
    }
    
    /**
     * Trier les logs d'attaque
     */
    public function sortAttackLogs(string $field): void
    {
        if ($this->attackLogSortField === $field) {
            $this->attackLogSortDirection = $this->attackLogSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->attackLogSortField = $field;
            $this->attackLogSortDirection = 'asc';
        }
    }
    
    /**
     * Sélectionner/désélectionner tous les logs utilisateurs
     */
    public function toggleSelectAllUserLogs(): void
    {
        $this->selectAllUserLogs = !$this->selectAllUserLogs;
        
        if ($this->selectAllUserLogs) {
            $query = $this->getUserLogsQuery();
            $this->selectedUserLogs = $query->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedUserLogs = [];
        }
    }
    
    /**
     * Sélectionner/désélectionner tous les logs d'attaque
     */
    public function toggleSelectAllAttackLogs(): void
    {
        $this->selectAllAttackLogs = !$this->selectAllAttackLogs;
        
        if ($this->selectAllAttackLogs) {
            $query = $this->getAttackLogsQuery();
            $this->selectedAttackLogs = $query->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedAttackLogs = [];
        }
    }
    
    /**
     * Supprimer les logs utilisateurs sélectionnés
     */
    public function deleteSelectedUserLogs(): void
    {
        if (empty($this->selectedUserLogs)) {
            $this->dispatch('admin-toast', [
                'type' => 'warning',
                'message' => 'Aucun log utilisateur sélectionné pour la suppression.'
            ]);
            return;
        }
        
        $count = UserLog::whereIn('id', $this->selectedUserLogs)->delete();
        
        $this->selectedUserLogs = [];
        $this->selectAllUserLogs = false;
        
        $this->dispatch('admin-toast', [
            'type' => 'success',
            'message' => "{$count} logs utilisateur(s) supprimé(s) avec succès."
        ]);
    }
    
    /**
     * Supprimer les logs d'attaque sélectionnés
     */
    public function deleteSelectedAttackLogs(): void
    {
        if (empty($this->selectedAttackLogs)) {
            $this->dispatch('admin-toast', [
                'type' => 'warning',
                'message' => 'Aucun log d\'attaque sélectionné pour la suppression.'
            ]);
            return;
        }
        
        $count = PlayerAttackLog::whereIn('id', $this->selectedAttackLogs)->delete();
        
        $this->selectedAttackLogs = [];
        $this->selectAllAttackLogs = false;
        
        $this->dispatch('admin-toast', [
            'type' => 'success',
            'message' => "{$count} logs d'attaque supprimé(s) avec succès."
        ]);
    }
    
    /**
     * Supprimer tous les logs utilisateurs
     */
    public function deleteAllUserLogs(): void
    {
        $count = UserLog::count();
        UserLog::truncate();
        
        $this->selectedUserLogs = [];
        $this->selectAllUserLogs = false;
        
        $this->dispatch('admin-toast', [
            'type' => 'success',
            'message' => "{$count} logs utilisateur(s) supprimé(s) avec succès."
        ]);
    }
    
    /**
     * Supprimer tous les logs d'attaque
     */
    public function deleteAllAttackLogs(): void
    {
        $count = PlayerAttackLog::count();
        PlayerAttackLog::truncate();
        
        $this->selectedAttackLogs = [];
        $this->selectAllAttackLogs = false;
        
        $this->dispatch('admin-toast', [
            'type' => 'success',
            'message' => "{$count} logs d'attaque supprimé(s) avec succès."
        ]);
    }
    
    /**
     * Obtenir la requête pour les logs utilisateurs avec filtres
     */
    private function getUserLogsQuery()
    {
        $query = UserLog::query()
            ->with(['user', 'planet', 'targetUser'])
            ->orderBy($this->userLogSortField, $this->userLogSortDirection);
        
        if ($this->userLogActionType) {
            $query->where('action_type', $this->userLogActionType);
        }
        
        if ($this->userLogCategory) {
            $query->where('action_category', $this->userLogCategory);
        }
        
        if ($this->userLogSeverity) {
            $query->where('severity', $this->userLogSeverity);
        }
        
        if (!empty($this->userLogSearch)) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->userLogSearch}%")
                  ->orWhereHas('user', function ($userQuery) {
                      $userQuery->where('name', 'like', "%{$this->userLogSearch}%");
                  })
                  ->orWhereHas('targetUser', function ($targetQuery) {
                      $targetQuery->where('name', 'like', "%{$this->userLogSearch}%");
                  });
            });
        }
        
        return $query;
    }
    
    /**
     * Obtenir la requête pour les logs d'attaque avec filtres
     */
    private function getAttackLogsQuery()
    {
        $query = PlayerAttackLog::query()
            ->with(['attacker', 'defender', 'attackerPlanet', 'defenderPlanet'])
            ->orderBy($this->attackLogSortField, $this->attackLogSortDirection);
        
        if ($this->attackLogType) {
            $query->where('attack_type', $this->attackLogType);
        }
        
        if ($this->attackLogWon !== null) {
            $query->where('attacker_won', $this->attackLogWon);
        }
        
        if (!empty($this->attackLogSearch)) {
            $query->where(function ($q) {
                $q->whereHas('attacker', function ($attackerQuery) {
                      $attackerQuery->where('name', 'like', "%{$this->attackLogSearch}%");
                  })
                  ->orWhereHas('defender', function ($defenderQuery) {
                      $defenderQuery->where('name', 'like', "%{$this->attackLogSearch}%");
                  });
            });
        }
        
        return $query;
    }
    
    /**
     * Obtenir les statistiques des logs utilisateurs
     */
    public function getUserLogStats(): array
    {
        return [
            'total' => UserLog::count(),
            'by_severity' => [
                'info' => UserLog::where('severity', UserLog::SEVERITY_INFO)->count(),
                'warning' => UserLog::where('severity', UserLog::SEVERITY_WARNING)->count(),
                'error' => UserLog::where('severity', UserLog::SEVERITY_ERROR)->count(),
            ],
            'by_category' => UserLog::select('action_category', DB::raw('count(*) as count'))
                ->groupBy('action_category')
                ->pluck('count', 'action_category')
                ->toArray()
        ];
    }
    
    /**
     * Obtenir les statistiques des logs d'attaque
     */
    public function getAttackLogStats(): array
    {
        return [
            'total' => PlayerAttackLog::count(),
            'victories' => PlayerAttackLog::where('attacker_won', true)->count(),
            'defeats' => PlayerAttackLog::where('attacker_won', false)->count(),
            'by_type' => PlayerAttackLog::select('attack_type', DB::raw('count(*) as count'))
                ->groupBy('attack_type')
                ->pluck('count', 'attack_type')
                ->toArray()
        ];
    }
    
    /**
     * Rendre le composant
     */
    public function render()
    {
        $userLogs = $this->getUserLogsQuery()->paginate($this->userLogPerPage);
        $attackLogs = $this->getAttackLogsQuery()->paginate($this->attackLogPerPage);
        
        $userLogStats = $this->getUserLogStats();
        $attackLogStats = $this->getAttackLogStats();
        
        return view('livewire.admin.logs', [
            'userLogs' => $userLogs,
            'attackLogs' => $attackLogs,
            'userLogStats' => $userLogStats,
            'attackLogStats' => $attackLogStats,
            'userLogActionTypes' => UserLog::getActionTypes(),
            'userLogCategories' => UserLog::getCategories(),
            'userLogSeverities' => UserLog::getSeverities(),
            'attackLogTypes' => PlayerAttackLog::getAttackTypes(),
        ]);
    }
}