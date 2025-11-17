<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Support\Device;

#[Layout('components.layouts.game')]
class Empire extends Component
{
    public $user;
    public $planets = [];
    public $availablePlanets = [];
    public $totals = [
        'resources' => [
            'metal' => ['amount' => 0, 'prod24h' => 0],
            'crystal' => ['amount' => 0, 'prod24h' => 0],
            'deuterium' => ['amount' => 0, 'prod24h' => 0],
        ],
        'energy' => ['production' => 0, 'consumption' => 0, 'net' => 0],
    ];

    public $targetPlanetId = null;

    public function mount()
    {
        $this->user = Auth::user();
        $this->loadUserPlanets();
        $this->loadEmpireData();
    }

    public function loadUserPlanets(): void
    {
        $this->availablePlanets = $this->user->planets()
            ->select('id', 'name', 'is_main_planet')
            ->with(['templatePlanet:id,galaxy,system,position,type'])
            ->get();
        $this->targetPlanetId = $this->availablePlanets->first()?->id;
    }

    public function loadEmpireData(): void
    {
        $this->planets = $this->user->planets()
            ->with([
                'templatePlanet',
                'resources.resource',
                'buildings.build',
                'units.unit',
                'defenses.defense',
                'ships.ship',
            ])
            ->get();

        $this->computeTotals();
    }

    protected function computeTotals(): void
    {
        $totMetal = 0; $totCrystal = 0; $totDeut = 0;
        $prodMetal24 = 0; $prodCrystal24 = 0; $prodDeut24 = 0;
        $energyProduction = 0; $energyConsumption = 0; $netEnergy = 0;

        foreach ($this->planets as $planet) {
            // Resources amounts and production per planet
            foreach ($planet->resources as $pr) {
                $name = strtolower($pr->resource->name);
                if ($name === 'metal') {
                    $totMetal += (int)$pr->current_amount;
                    $prodMetal24 += $pr->getCurrentProductionPerHour() * 24;
                } elseif ($name === 'crystal') {
                    $totCrystal += (int)$pr->current_amount;
                    $prodCrystal24 += $pr->getCurrentProductionPerHour() * 24;
                } elseif ($name === 'deuterium') {
                    $totDeut += (int)$pr->current_amount;
                    $prodDeut24 += $pr->getCurrentProductionPerHour() * 24;
                }
            }

            // Energy stats per planet
            $energyProduction += $planet->getEnergyProduction();
            $energyConsumption += $planet->getEnergyConsumption();
            $netEnergy += $planet->getNetEnergy();
        }

        $this->totals = [
            'resources' => [
                'metal' => ['amount' => $totMetal, 'prod24h' => $prodMetal24],
                'crystal' => ['amount' => $totCrystal, 'prod24h' => $prodCrystal24],
                'deuterium' => ['amount' => $totDeut, 'prod24h' => $prodDeut24],
            ],
            'energy' => [
                'production' => $energyProduction,
                'consumption' => $energyConsumption,
                'net' => $netEnergy,
            ],
        ];
    }

    public function consolidateResources()
    {
        $user = $this->user;
        if (!$user || !$user->vip_active || ($user->vip_until && now()->isAfter($user->vip_until))) {
            $this->dispatch('toast:error', [
                'title' => 'Fonction VIP requise',
                'text' => 'Activez le VIP pour regrouper vos ressources.',
            ]);
            return;
        }

        if (!$this->targetPlanetId) {
            $this->dispatch('toast:error', [
                'title' => 'Planète cible manquante',
                'text' => 'Veuillez choisir une planète cible.',
            ]);
            return;
        }

        $target = Planet::where('user_id', $user->id)->find($this->targetPlanetId);
        if (!$target) {
            $this->dispatch('toast:error', [
                'title' => 'Planète invalide',
                'text' => 'La planète cible n’appartient pas à votre empire.',
            ]);
            return;
        }

        // Consolidation for primary resources only
        DB::transaction(function () use ($user, $target) {
            $resourceNames = ['metal', 'crystal', 'deuterium'];

            // Préparer un index en mémoire: [planet_id][resource_name] => PlanetResource
            $index = [];
            foreach ($this->planets as $p) {
                $index[$p->id] = [];
                foreach ($p->resources as $pr) {
                    $name = strtolower($pr->resource->name);
                    if (in_array($name, $resourceNames, true)) {
                        $index[$p->id][$name] = $pr;
                    }
                }
            }

            foreach ($this->planets as $planet) {
                if ($planet->id === $target->id) continue;

                foreach ($resourceNames as $resName) {
                    $sourcePR = $index[$planet->id][$resName] ?? null;
                    $targetPR = $index[$target->id][$resName] ?? null;

                    if (!$sourcePR || !$targetPR) continue;

                    $amount = (int) $sourcePR->current_amount;
                    if ($amount <= 0) continue;

                    // Add to target respecting storage capacity
                    $added = $targetPR->addResources($amount);
                    if ($added > 0) {
                        $sourcePR->removeResources($added);
                    }
                }
            }
        });

        $this->loadEmpireData();

        $this->dispatch('toast:success', [
            'title' => 'Ressources regroupées',
            'text' => 'Toutes les ressources ont été transférées vers la planète sélectionnée (capacité de stockage respectée).',
        ]);
    }

    public function render()
    {
        return view('livewire.game.empire');
    }
}