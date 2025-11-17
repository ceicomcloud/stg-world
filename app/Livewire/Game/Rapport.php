<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Player\PlayerAttackLog;

#[Layout('components.layouts.game')]
class Rapport extends Component
{
    public ?PlayerAttackLog $log = null;
    public string $key;

    public function mount(string $key): void
    {
        $this->key = $key;
        $this->log = PlayerAttackLog::with(['attackerUser', 'defenderUser', 'attackerPlanet', 'defenderPlanet'])
            ->where('access_key', $key)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.game.rapport', [
            'log' => $this->log,
            'report' => $this->log?->report_data ?? [],
            'combatResult' => $this->log?->combat_result ?? [],
            'resourcesPillaged' => $this->log?->resources_pillaged ?? [],
        ]);
    }
}