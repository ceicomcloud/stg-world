<?php

namespace App\Livewire\Game\Modal;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Auth;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplateResource;
use App\Services\DailyQuestService;

class DailyQuests extends ModalComponent
{
    public $quests = [];
    public $planet;

    protected $listeners = [
        'dailyQuests:refresh' => 'loadQuests',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->planet = $user?->getActualPlanet();
        $this->loadQuests();
    }

    public function loadQuests(): void
    {
        $user = Auth::user();
        if (!$user) { return; }
        $this->quests = app(DailyQuestService::class)->getOrCreateTodayQuests($user);
    }

    public function claimReward(string $questKey): void
    {
        $user = Auth::user();
        if (!$user || !$this->planet) { return; }

        $quest = collect($this->quests)->firstWhere('key', $questKey);
        if (!$quest) { return; }

        // Conditions: progress >= target and not claimed
        if (($quest['progress'] ?? 0) < ($quest['target'] ?? 1)) {
            $this->dispatch('toast:error', [
                'title' => 'Quête non terminée',
                'text' => "Vous n'avez pas encore atteint l'objectif."
            ]);
            return;
        }
        if (!empty($quest['claimed_at'])) {
            $this->dispatch('toast:warning', [
                'title' => 'Déjà réclamée',
                'text' => 'La récompense de cette quête a déjà été récupérée.'
            ]);
            return;
        }

        // Récompenses (actuellement supporte type resource: metal/crystal/deuterium)
        $reward = $quest['reward'] ?? [];
        if (($reward['type'] ?? '') === 'resource') {
            $resourceKey = $reward['resource'] ?? null; // 'metal' | 'crystal' | 'deuterium'
            $amount = (int) ($reward['amount'] ?? 0);
            if ($resourceKey && $amount > 0) {
                $templateResource = TemplateResource::where('name', $resourceKey)->first();
                if ($templateResource) {
                    $planetResource = PlanetResource::where('planet_id', $this->planet->id)
                        ->where('resource_id', $templateResource->id)
                        ->first();
                    if ($planetResource) {
                        $added = $planetResource->addResources($amount);
                        app(DailyQuestService::class)->markClaimed($user, $questKey);
                        $this->loadQuests();
                        $this->dispatch('resourcesUpdated');
                        $this->dispatch('toast:success', [
                            'title' => 'Récompense récupérée',
                            'text' => 'Ajout de ' . number_format($added, 0, '.', ' ') . ' ' . ($templateResource->display_name ?? $templateResource->name)
                        ]);
                        return;
                    }
                }
            }
        }

        $this->dispatch('toast:error', [
            'title' => 'Récompense indisponible',
            'text' => 'Type de récompense non pris en charge ou invalide.'
        ]);
    }

    public function render()
    {
        return view('livewire.game.modal.daily-quests');
    }
}