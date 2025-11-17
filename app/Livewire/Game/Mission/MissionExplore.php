<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetMission;
use App\Models\Template\TemplatePlanet;
use App\Services\DailyQuestService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use App\Services\UserCustomizationService;

#[Layout('components.layouts.game')]
class MissionExplore extends Component
{
    public $templateId;
    public $planet;
    public $availableShips = [];
    public $selectedShips = [];
    public $showMissionSummary = false;
    public $totalShipsSelected = 0;
    public $targetCoordinates = '';
    public $travelDurationSeconds = 0;
    public $explorationDurationSeconds = 1800; // sera ré-initialisé aléatoirement au montage
    public $totalDurationSeconds = 0;
    public $fuelCost = 0;

    public function mount($templateId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->templateId = $templateId;

        $template = TemplatePlanet::find($templateId);
        if (!$template) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => "Aucune planète n'existe à ces coordonnées."
            ]);
            return redirect()->route('game.mission.index');
        }

        $this->targetCoordinates = $template->galaxy . ':' . $template->system . ':' . $template->position;
        // Durée d'exploration aléatoire entre 1h et 6h
        $this->explorationDurationSeconds = $this->getRandomExplorationDurationSeconds();
        $this->loadAvailableShips();
        $this->recalculateStats();
    }

    private function loadAvailableShips(): void
    {
        $ships = PlanetShip::where('planet_id', $this->planet->id)
            ->with('ship')
            ->get()
            ->filter(function ($ps) {
                // Exploration: Drone Stratos ou Scout Quantique
                return in_array($ps->ship->name, ['drone_stratos', 'scout_quantique']);
            });

        $svc = new UserCustomizationService();
        $user = FacadesAuth::user();

        $this->availableShips = $ships->map(function ($ps) use ($svc, $user) {
            $resolved = $svc->resolveBuild($user, $ps->ship);
            return [
                'id' => $ps->id,
                'key' => $ps->ship->name,
                'name' => $resolved['name'],
                'quantity' => $ps->quantity,
                'speed' => $ps->ship->speed,
                'fuel_consumption' => $ps->ship->fuel_consumption,
                'image' => $ps->ship->icon,
                'icon_url' => $resolved['icon_url'],
            ];
        })->values()->toArray();
    }

    public function updatedSelectedShips(): void
    {
        $this->recalculateStats();
        $this->recalculateTotals();
    }

    private function recalculateStats(): void
    {
        if (empty($this->selectedShips)) {
            $this->travelDurationSeconds = 0;
            $this->totalDurationSeconds = 0;
            $this->fuelCost = 0;
            return;
        }
        // Calculate travel duration and fuel using central PlanetMission helpers
        $template = TemplatePlanet::find($this->templateId);
        if (!$template) {
            $this->travelDurationSeconds = 0;
            $this->totalDurationSeconds = 0;
            $this->fuelCost = 0;
            return;
        }

        // Slowest speed among selected ships
        $slowestSpeed = PlanetMission::calculateSpeed($this->selectedShips, $this->availableShips);

        // Build a mapping by template ship id for fuel calculation
        $selectedByTemplate = [];
        foreach ($this->selectedShips as $planetShipId => $quantity) {
            if ($quantity > 0) {
                $planetShip = PlanetShip::find($planetShipId);
                if ($planetShip) {
                    $tplId = $planetShip->ship_id;
                    $selectedByTemplate[$tplId] = ($selectedByTemplate[$tplId] ?? 0) + (int) $quantity;
                }
            }
        }

        // Duration returned in minutes by calculateMissionDuration
        $travelDurationMinutes = PlanetMission::calculateMissionDuration(
            $this->planet->templatePlanet->system,
            $template->system,
            $slowestSpeed,
            auth()->id()
        );
        $this->travelDurationSeconds = $travelDurationMinutes * 60;
        $this->totalDurationSeconds = ($this->travelDurationSeconds * 2) + $this->explorationDurationSeconds;

        // Fuel consumption (round trip)
        $this->fuelCost = PlanetMission::calculateFuelConsumption(
            $selectedByTemplate,
            $this->planet->templatePlanet->system,
            $template->system,
            true
        );
    }

    private function getRandomExplorationDurationSeconds(): int
    {
        $hours = random_int(1, 6);
        return $hours * 3600;
    }

    private function recalculateTotals(): void
    {
        $this->totalShipsSelected = array_sum(array_map('intval', $this->selectedShips));
    }

    public function updateShipSelection(): void
    {
        $this->recalculateStats();
        $this->recalculateTotals();
    }

    public function setClearShips(int $planetShipId): void
    {
        $this->selectedShips[$planetShipId] = 0;
        $this->recalculateStats();
        $this->recalculateTotals();
    }

    public function setMaxShips(int $planetShipId): void
    {
        $ship = collect($this->availableShips)->firstWhere('id', $planetShipId);
        if ($ship) {
            $this->selectedShips[$planetShipId] = (int) $ship['quantity'];
            $this->recalculateStats();
            $this->recalculateTotals();
        }
    }

    public function showSummary(): void
    {
        // Valider la cible avant d'afficher le résumé
        if (!$this->validateExploreTarget()) {
            return;
        }

        if (empty($this->selectedShips) || $this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Sélection vide',
                'text' => 'Veuillez sélectionner des vaisseaux à envoyer.'
            ]);
            return;
        }
        $this->showMissionSummary = true;
    }

    public function backToSelection(): void
    {
        $this->showMissionSummary = false;
    }

    public function launchMission()
    {
        // Valider la cible exploration selon les règles
        if (!$this->validateExploreTarget()) {
            return;
        }

        // Validate selection
        if (empty($this->selectedShips)) {
            $this->dispatch('swal:error', [
                'title' => 'Sélection vide',
                'text' => 'Veuillez sélectionner des vaisseaux à envoyer.'
            ]);
            return;
        }

        // Vérifier le plafond des flottes en vol selon le niveau du Centre de Commandement
        $allowedFlying = \App\Models\Planet\PlanetMission::getAllowedFlyingFleetsForPlanet($this->planet->id);
        $currentFlying = \App\Models\Planet\PlanetMission::countUserFlyingMissions(auth()->id());
        if ($currentFlying >= $allowedFlying) {
            $ccLevel = \App\Models\Planet\PlanetMission::getCommandCenterLevelForPlanet($this->planet->id);
            $this->dispatch('swal:error', [
                'title' => 'Trop de flottes en vol',
                'text' => "Limite actuelle: {$allowedFlying} flottes en vol (Centre de Commandement niveau {$ccLevel})."
            ]);
            return;
        }

        $template = TemplatePlanet::find($this->templateId);
        if (!$template) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => "Aucune planète n'existe à ces coordonnées."
            ]);
            return;
        }

        // Check deuterium via PlanetResource relation
        $deuteriumResource = $this->planet->resources()
            ->whereHas('resource', function($query) {
                $query->where('name', 'deuterium');
            })
            ->first();

        if (!$deuteriumResource || $deuteriumResource->current_amount < (int) $this->fuelCost) {
            $this->dispatch('swal:error', [
                'title' => 'Carburant insuffisant',
                'text' => 'Vous n\'avez pas assez de deutérium pour cette mission.'
            ]);
            return;
        }

        // Prepare ship payload and remove from planet
        $shipsPayload = [];
        foreach ($this->selectedShips as $planetShipId => $quantity) {
            if ($quantity <= 0) continue;
            $planetShip = PlanetShip::find($planetShipId);
            if (!$planetShip || $planetShip->planet_id !== $this->planet->id || $planetShip->quantity < $quantity) {
                $this->dispatch('swal:error', [
                    'title' => 'Sélection invalide',
                    'text' => 'Quantité de vaisseaux invalide ou indisponible.'
                ]);
                return;
            }
            // Aggregate by template ship id for mission payload
            $shipsPayload[$planetShip->ship_id] = ($shipsPayload[$planetShip->ship_id] ?? 0) + (int) $quantity;
            $planetShip->decrement('quantity', (int) $quantity);
        }

        // Consume fuel from PlanetResource
        $deuteriumResource->decrement('current_amount', (int) $this->fuelCost);

        // Create mission using centralized duration
        $slowestSpeed = PlanetMission::calculateSpeed($this->selectedShips, $this->availableShips);
        $travelDurationMinutes = PlanetMission::calculateMissionDuration(
            $this->planet->templatePlanet->system,
            $template->system,
            $slowestSpeed,
            auth()->id()
        );
        $arrival = Carbon::now()->addMinutes($travelDurationMinutes);
        $return = (clone $arrival)->addSeconds($this->explorationDurationSeconds)
                                  ->addMinutes($travelDurationMinutes);

        PlanetMission::create([
            'user_id' => auth()->id(),
            'from_planet_id' => $this->planet->id,
            'to_planet_id' => null,
            'to_galaxy' => $template->galaxy,
            'to_system' => $template->system,
            'to_position' => $template->position,
            'mission_type' => 'explore',
            'ships' => $shipsPayload,
            'status' => 'traveling',
            'departure_time' => Carbon::now(),
            'arrival_time' => $arrival,
            'return_time' => $return,
        ]);

        // Incrémenter la quête quotidienne pour mission d'exploration
        $user = Auth::user();
        if ($user) {
            app(DailyQuestService::class)->incrementProgress($user, 'mission_explore');
        }

        $this->dispatch('swal:success', [
            'title' => 'Mission lancée',
            'text' => 'Vos éclaireurs partent explorer cette planète.'
        ]);

        return redirect()->route('game.mission.index');
    }

    public function render()
    {
        return view('livewire.game.mission.mission-explore');
    }

    /**
     * Valider les règles d'exploration: non colonisée et distance ≤10 systèmes dans la même galaxie
     */
    protected function validateExploreTarget(): bool
    {
        $template = TemplatePlanet::find($this->templateId);
        if (!$template) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => "Aucune planète n'existe à ces coordonnées."
            ]);
            return false;
        }

        // Non colonisée
        $isColonized = Planet::where('template_planet_id', $this->templateId)->exists();
        if ($isColonized) {
            $this->dispatch('swal:error', [
                'title' => 'Planète colonisée',
                'text' => "L'exploration n'est possible que sur des planètes non colonisées."
            ]);
            return false;
        }

        // Distance: même galaxie et différence de systèmes ≤ 10
        $sourceGalaxy = $this->planet->templatePlanet->galaxy ?? null;
        $sourceSystem = $this->planet->templatePlanet->system ?? null;
        if ($sourceGalaxy === null || $sourceSystem === null) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => "Impossible de déterminer votre planète d'origine."
            ]);
            return false;
        }
        if ($template->galaxy !== $sourceGalaxy) {
            $this->dispatch('swal:error', [
                'title' => 'Distance trop grande',
                'text' => "L'exploration doit se faire dans la même galaxie et à ≤ 10 systèmes."
            ]);
            return false;
        }
        $systemDiff = abs($template->system - $sourceSystem);
        if ($systemDiff > 10) {
            $this->dispatch('swal:error', [
                'title' => 'Distance trop grande',
                'text' => "L'exploration doit se faire à une distance maximale de 10 systèmes."
            ]);
            return false;
        }

        return true;
    }
}