<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\User\UserInventory;
use App\Models\Planet\Planet;
use App\Services\InventoryService;
use App\Support\Device;

#[Layout('components.layouts.game')]
class Inventory extends Component
{
    public $user;
    public $planets = [];
    public $selectedPlanetId = null;
    public $inventories = [];

    public function mount()
    {
        $this->user = Auth::user();
        $this->planets = Planet::byUser($this->user->id)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'is_main' => (bool) $p->is_main_planet,
            ];
        })->toArray();

        // Pré-sélectionner la planète actuelle si disponible
        $this->selectedPlanetId = $this->user->actual_planet_id ?? ($this->planets[0]['id'] ?? null);

        $this->loadInventories();
    }

    public function loadInventories(): void
    {
        $this->inventories = UserInventory::where('user_id', $this->user->id)
            ->with('template')
            ->orderByDesc('acquired_at')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'template_key' => $inv->template->key,
                    'name' => $inv->template->name,
                    'description' => $inv->template->description,
                    'icon' => $inv->template->icon,
                    'rarity' => $inv->template->rarity,
                    'effect_type' => $inv->template->effect_type,
                    'effect_value' => $inv->template->effect_value,
                    'effect_meta' => $inv->template->effect_meta,
                    'duration_seconds' => $inv->template->duration_seconds,
                    'usable' => (bool) $inv->template->usable,
                    'stackable' => (bool) $inv->template->stackable,
                    'quantity' => (int) $inv->quantity,
                    'acquired_at' => $inv->acquired_at,
                ];
            })->toArray();
    }

    public function setSelectedPlanet($planetId): void
    {
        $this->selectedPlanetId = (int) $planetId;
    }

    public function useItem(int $userInventoryId): void
    {
        $inventory = UserInventory::where('user_id', $this->user->id)
            ->where('id', $userInventoryId)
            ->with('template')
            ->first();

        if (!$inventory || $inventory->quantity <= 0) {
            $this->dispatch('toast:error', [
                'title' => 'Article indisponible',
                'text' => "Vous n'avez plus d'exemplaire de cet article."
            ]);
            return;
        }

        if (!$inventory->template->usable) {
            $this->dispatch('toast:error', [
                'title' => 'Utilisation impossible',
                'text' => "Cet article n'est pas utilisable."
            ]);
            return;
        }

        // Déterminer la planète cible si nécessaire
        $planet = null;
        if (in_array($inventory->template->effect_type, ['add_resources', 'add_units', 'add_defenses', 'add_ships', 'production_boost', 'storage_boost', 'energy_boost'])) {
            if (!$this->selectedPlanetId) {
                $this->dispatch('toast:error', [
                    'title' => 'Planète requise',
                    'text' => 'Veuillez sélectionner une planète cible.'
                ]);
                return;
            }
            $planet = Planet::find($this->selectedPlanetId);
        }

        $service = app(InventoryService::class);
        $result = $service->consumeItem($this->user, $inventory, $planet);

        if ($result['success'] ?? false) {
            $this->dispatch('toast:success', [
                'title' => 'Article utilisé',
                'text' => $result['message'] ?? 'Effet appliqué avec succès.'
            ]);
            $this->loadInventories();
            $this->dispatch('inventoriesUpdated');
            $this->dispatch('resourcesUpdated');
        } else {
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => $result['message'] ?? "Impossible d'appliquer cet article."
            ]);
        }
    }

    public function render()
    {
        return view('livewire.game.inventory');
    }
}