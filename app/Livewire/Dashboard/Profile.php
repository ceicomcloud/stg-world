<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Template\TemplateBadge;
use App\Models\Server\ServerConfig;

#[Layout('components.layouts.app')]
class Profile extends Component
{
    public $user;
    public $vacationDays = 7; // Valeur par défaut
    public $minVacationDays;
    public $maxVacationDays;

    public function mount()
    {
        // Eager load des badges pour éviter des requêtes supplémentaires
        $this->user = Auth::user()->load('badges');
        $this->minVacationDays = ServerConfig::getVacationModeMinDays();
        $this->maxVacationDays = ServerConfig::getVacationModeMaxDays();
        
        // Définir la valeur par défaut entre min et max
        $this->vacationDays = max($this->minVacationDays, min($this->maxVacationDays, $this->vacationDays));
    }

    public function getExperienceProgressProperty()
    {
        // Utiliser la méthode du modèle User
        return $this->user->getLevelProgress();
    }

    public function getExperienceToNextProperty()
    {
        // Retourner l'expérience actuelle dans le niveau
        return $this->user->getCurrentExperience();
    }

    public function getExperienceForNextProperty()
    {
        // Utiliser la méthode du modèle User pour obtenir l'XP requis pour le niveau actuel
        return $this->user->getRequiredExperienceForLevel($this->user->getLevel());
    }

    public function getAchievementsProperty()
    {
        // Récupérer les badges actifs avec cache pour réduire les accès DB
        $allBadges = Cache::remember('template_badges:active', 600, function () {
            return TemplateBadge::where('is_active', true)->get();
        });
        
        // Récupérer les badges débloqués par l'utilisateur
        $earnedBadges = $this->user->badges->keyBy('id');
        
        return $allBadges->map(function ($badge) use ($earnedBadges) {
            $isUnlocked = $earnedBadges->has($badge->id);
            
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon ?? 'fas fa-trophy',
                'type' => $badge->type,
                'rarity' => $badge->rarity,
                'points_reward' => $badge->points_reward,
                'unlocked' => $isUnlocked,
                'unlocked_at' => $isUnlocked ? $earnedBadges[$badge->id]->pivot->earned_at : null,
                'progress' => $this->getBadgeProgress($badge)
            ];
        })->sortByDesc('unlocked')->values();
    }
    
    /**
     * Calculer le progrès d'un badge pour l'utilisateur
     */
    private function getBadgeProgress($badge)
    {
        if ($this->user->badges->contains('id', $badge->id)) {
            return 100; // Badge déjà débloqué
        }
        
        $currentValue = $this->getCurrentValueForBadge($badge);
        $requiredValue = $badge->requirement_value;
        
        if ($requiredValue <= 0) {
            return 0;
        }
        
        return min(100, ($currentValue / $requiredValue) * 100);
    }
    
    /**
     * Obtenir la valeur actuelle pour un badge donné
     */
    private function getCurrentValueForBadge($badge)
    {
        switch ($badge->requirement_type) {
            case TemplateBadge::REQUIREMENT_REACH_LEVEL:
                return $this->user->getLevel();
                
            case TemplateBadge::REQUIREMENT_TOTAL_EXPERIENCE:
                return $this->user->getTotalExperienceForLevel($this->user->getLevel()) + $this->user->getCurrentExperience();
                
            case TemplateBadge::REQUIREMENT_RESEARCH_POINTS:
                return $this->user->getResearchPoints();
                
            case TemplateBadge::REQUIREMENT_CUSTOM:
                return 0; // Pour les badges personnalisés
                
            default:
                return 0;
        }
    }

    public function getGravatarUrlProperty()
    {
        // Utilise l'avatar personnalisé s'il existe, sinon Gravatar
        return $this->user->getUserAvatarUrl(150);
    }

    /**
     * URL de l'avatar (custom > Gravatar)
     */
    public function getAvatarUrlProperty()
    {
        return $this->user->getUserAvatarUrl(150);
    }

    /**
     * Obtenir l'expérience restante pour le prochain niveau
     */
    public function getExperienceToNextLevelProperty()
    {
        return $this->user->getExperienceToNextLevel();
    }
    
    /**
     * Obtenir le nombre total de badges
     */
    public function getBadgeCountProperty()
    {
        return $this->user->getBadgeCount();
    }
    
    /**
     * Obtenir les badges par rareté
     */
    public function getBadgesByRarityProperty()
    {
        return $this->user->getBadgeCountByRarity();
    }
    
    /**
     * Obtenir les points de recherche
     */
    public function getResearchPointsProperty()
    {
        return $this->user->getResearchPoints();
    }

    public function formatDate($date)
    {
        return \Carbon\Carbon::parse($date)->format('d/m/Y');
    }

    public function formatNumber($number)
    {
        return number_format($number, 0, ',', ' ');
    }

    /**
     * Activer le mode vacances
     */
    public function enableVacationMode()
    {
        // Vérifier si le mode vacances est activé sur le serveur
        if (!ServerConfig::isVacationModeEnabled()) {
            $this->dispatch('toast:error', [
                'title' => 'Vacance',
                'text' => "Le mode vacances est désactivé sur ce serveur."
            ]);
            return;
        }
        
        if ($this->user->enableVacationMode($this->vacationDays)) {
            $this->dispatch('toast:success', [
                'title' => 'Vacance',
                'text' => "Mode vacances activé pour {$this->vacationDays} jours."
            ]);
        } else {
            $this->dispatch('toast:error', [
                'title' => 'Vacance',
                'text' => "Impossible d'activer le mode vacances. Veuillez vérifier la durée."
            ]);
        }
    }
    
    /**
     * Désactiver le mode vacances
     */
    public function disableVacationMode()
    {
        if ($this->user->disableVacationMode()) {
            $this->dispatch('toast:success', [
                'title' => 'Vacance',
                'text' => "Mode vacances désactivé avec succès."
            ]);
        } else {
            $this->dispatch('toast:error', [
                'title' => 'Vacance',
                'text' => "Impossible de désactiver le mode vacances."
            ]);
        }
    }
    
    /**
     * Vérifier si l'utilisateur est en mode vacances
     */
    public function getIsInVacationModeProperty()
    {
        return $this->user->isInVacationMode();
    }
    
    /**
     * Obtenir la date de fin du mode vacances
     */
    public function getVacationModeEndDateProperty()
    {
        return $this->user->getVacationModeEndDate();
    }
    
    /**
     * Obtenir le nombre de jours restants en mode vacances
     */
    public function getRemainingVacationDaysProperty()
    {
        return $this->user->getRemainingVacationModeDays();
    }
    
    public function render()
    {
        return view('livewire.dashboard.profile');
    }
}