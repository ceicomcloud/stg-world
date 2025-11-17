<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template\TemplateBuildCost;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateResource;

class TemplateBuildCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Récupérer les IDs des ressources
        $metal = TemplateResource::where('name', 'metal')->first();
        $crystal = TemplateResource::where('name', 'crystal')->first();
        $deuterium = TemplateResource::where('name', 'deuterium')->first();

        // Récupérer les IDs des bâtiments
        $commandCenter = TemplateBuild::where('name', 'centre_commandement')->first();
        $metalMine = TemplateBuild::where('name', 'mine_fer')->first();
        $crystalMine = TemplateBuild::where('name', 'extracteur_cristal')->first();
        $deuteriumSynth = TemplateBuild::where('name', 'raffinerie_deuterium')->first();
        $solarPlant = TemplateBuild::where('name', 'centrale_solaire')->first();
        $researchLab = TemplateBuild::where('name', 'centre_recherche')->first();
        $barracks = TemplateBuild::where('name', 'caserne')->first();
        $shipyard = TemplateBuild::where('name', 'chantier_spatial')->first();
        $defensivePlatform = TemplateBuild::where('name', 'plateforme_defensive')->first();
        $terraformer = TemplateBuild::where('name', 'terraformeur_planetaire')->first();
        $metalStorage = TemplateBuild::where('name', 'stockage_fer')->first();
        $crystalStorage = TemplateBuild::where('name', 'stockage_cristal')->first();
        $deuteriumTank = TemplateBuild::where('name', 'stockage_deuterium')->first();
        
        // Récupérer les IDs des vaisseaux spatiaux
        $stratosDrone = TemplateBuild::where('name', 'drone_stratos')->first();
        $solarInterceptor = TemplateBuild::where('name', 'intercepteur_solaire')->first();
        $vectorBomber = TemplateBuild::where('name', 'bombardier_vector')->first();
        $deltaTransport = TemplateBuild::where('name', 'transporteur_delta')->first();
        $defensiveFrigate = TemplateBuild::where('name', 'fregate_defensive')->first();
        $orionCruiser = TemplateBuild::where('name', 'croiseur_orion')->first();
        $commandShip = TemplateBuild::where('name', 'vaisseau_commandement')->first();
        $siegePlatform = TemplateBuild::where('name', 'plateforme_siege')->first();
        $quantumScout = TemplateBuild::where('name', 'scout_quantique')->first();
        $novaDreadnought = TemplateBuild::where('name', 'dreadnought_nova')->first();
        
        // Récupérer les IDs des unités terrestres
        $standardInfantry = TemplateBuild::where('name', 'fantassin_standard')->first();
        $tacticalCommando = TemplateBuild::where('name', 'commando_tactique')->first();
        $reconScout = TemplateBuild::where('name', 'eclaireur_recon')->first();
        $fieldEngineer = TemplateBuild::where('name', 'ingenieur_terrain')->first();
        $combatDrone = TemplateBuild::where('name', 'drone_combat')->first();
        $heavyArmoredUnit = TemplateBuild::where('name', 'unite_lourdement_blindee')->first();
        $ionicSniper = TemplateBuild::where('name', 'sniper_ionique')->first();
        $jammingTechnician = TemplateBuild::where('name', 'technicien_brouillage')->first();
        $pyrosoldier = TemplateBuild::where('name', 'pyrosoldat')->first();
        $artilleryOperator = TemplateBuild::where('name', 'operateur_artillerie')->first();
        
        // Récupérer les IDs des défenses
        $kineticTurret = TemplateBuild::where('name', 'tourelle_cinetique')->first();
        $laserCannon = TemplateBuild::where('name', 'canon_laser')->first();
        $missileLauncher = TemplateBuild::where('name', 'lance_missiles')->first();
        $plasmaBattery = TemplateBuild::where('name', 'batterie_plasma')->first();
        $jammingField = TemplateBuild::where('name', 'champ_brouillage')->first();
        $shieldGenerator = TemplateBuild::where('name', 'generateur_bouclier')->first();
        $surveillanceTower = TemplateBuild::where('name', 'tour_surveillance')->first();
        $orbitalCannon = TemplateBuild::where('name', 'canon_orbital')->first();
        $solarMines = TemplateBuild::where('name', 'mines_solaires')->first();
        $empTurret = TemplateBuild::where('name', 'tourelle_emp')->first();

        $costs = [
            // BÂTIMENTS
            ['build_id' => $commandCenter->id, 'resource_id' => $metal->id, 'base_cost' => 4000, 'cost_multiplier' => 1.6, 'level' => 1],
            ['build_id' => $commandCenter->id, 'resource_id' => $crystal->id, 'base_cost' => 2000, 'cost_multiplier' => 1.6, 'level' => 1],
            ['build_id' => $metalMine->id, 'resource_id' => $metal->id, 'base_cost' => 120, 'cost_multiplier' => 1.5, 'level' => 1],
            ['build_id' => $metalMine->id, 'resource_id' => $crystal->id, 'base_cost' => 60, 'cost_multiplier' => 1.5, 'level' => 1],
            ['build_id' => $crystalMine->id, 'resource_id' => $metal->id, 'base_cost' => 220, 'cost_multiplier' => 1.55, 'level' => 1],
            ['build_id' => $crystalMine->id, 'resource_id' => $crystal->id, 'base_cost' => 110, 'cost_multiplier' => 1.55, 'level' => 1],
            ['build_id' => $deuteriumSynth->id, 'resource_id' => $metal->id, 'base_cost' => 320, 'cost_multiplier' => 1.58, 'level' => 1],
            ['build_id' => $deuteriumSynth->id, 'resource_id' => $crystal->id, 'base_cost' => 220, 'cost_multiplier' => 1.58, 'level' => 1],
            ['build_id' => $solarPlant->id, 'resource_id' => $metal->id, 'base_cost' => 90, 'cost_multiplier' => 1.5, 'level' => 1],
            ['build_id' => $solarPlant->id, 'resource_id' => $crystal->id, 'base_cost' => 40, 'cost_multiplier' => 1.5, 'level' => 1],
            ['build_id' => $researchLab->id, 'resource_id' => $metal->id, 'base_cost' => 600, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $researchLab->id, 'resource_id' => $crystal->id, 'base_cost' => 360, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $barracks->id, 'resource_id' => $metal->id, 'base_cost' => 600, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $barracks->id, 'resource_id' => $crystal->id, 'base_cost' => 360, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $shipyard->id, 'resource_id' => $metal->id, 'base_cost' => 1200, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $shipyard->id, 'resource_id' => $crystal->id, 'base_cost' => 600, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $defensivePlatform->id, 'resource_id' => $metal->id, 'base_cost' => 1200, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $defensivePlatform->id, 'resource_id' => $crystal->id, 'base_cost' => 600, 'cost_multiplier' => 1.7, 'level' => 1],
            ['build_id' => $terraformer->id, 'resource_id' => $metal->id, 'base_cost' => 3500, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $terraformer->id, 'resource_id' => $crystal->id, 'base_cost' => 2000, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $terraformer->id, 'resource_id' => $deuterium->id, 'base_cost' => 2000, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $metalStorage->id, 'resource_id' => $metal->id, 'base_cost' => 60000, 'cost_multiplier' => 1.35, 'level' => 1],
            ['build_id' => $crystalStorage->id, 'resource_id' => $metal->id, 'base_cost' => 18000, 'cost_multiplier' => 1.35, 'level' => 1],
            ['build_id' => $crystalStorage->id, 'resource_id' => $crystal->id, 'base_cost' => 12000, 'cost_multiplier' => 1.35, 'level' => 1],
            ['build_id' => $deuteriumTank->id, 'resource_id' => $metal->id, 'base_cost' => 15000, 'cost_multiplier' => 1.35, 'level' => 1],
            
            // UNITÉS TERRESTRES
            ['build_id' => $standardInfantry->id, 'resource_id' => $metal->id, 'base_cost' => 70, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $standardInfantry->id, 'resource_id' => $crystal->id, 'base_cost' => 10, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $tacticalCommando->id, 'resource_id' => $metal->id, 'base_cost' => 140, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $tacticalCommando->id, 'resource_id' => $crystal->id, 'base_cost' => 70, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $reconScout->id, 'resource_id' => $metal->id, 'base_cost' => 190, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $reconScout->id, 'resource_id' => $crystal->id, 'base_cost' => 70, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $fieldEngineer->id, 'resource_id' => $metal->id, 'base_cost' => 190, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $fieldEngineer->id, 'resource_id' => $crystal->id, 'base_cost' => 140, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $combatDrone->id, 'resource_id' => $metal->id, 'base_cost' => 250, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $combatDrone->id, 'resource_id' => $crystal->id, 'base_cost' => 140, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $heavyArmoredUnit->id, 'resource_id' => $metal->id, 'base_cost' => 600, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $heavyArmoredUnit->id, 'resource_id' => $crystal->id, 'base_cost' => 190, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $ionicSniper->id, 'resource_id' => $metal->id, 'base_cost' => 320, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $ionicSniper->id, 'resource_id' => $crystal->id, 'base_cost' => 320, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $jammingTechnician->id, 'resource_id' => $metal->id, 'base_cost' => 190, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $jammingTechnician->id, 'resource_id' => $crystal->id, 'base_cost' => 190, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $pyrosoldier->id, 'resource_id' => $metal->id, 'base_cost' => 380, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $pyrosoldier->id, 'resource_id' => $crystal->id, 'base_cost' => 190, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $pyrosoldier->id, 'resource_id' => $deuterium->id, 'base_cost' => 70, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $artilleryOperator->id, 'resource_id' => $metal->id, 'base_cost' => 500, 'cost_multiplier' => 1.12, 'level' => 1],
            ['build_id' => $artilleryOperator->id, 'resource_id' => $crystal->id, 'base_cost' => 250, 'cost_multiplier' => 1.12, 'level' => 1],
            
            // VAISSEAUX SPATIAUX
            ['build_id' => $stratosDrone->id, 'resource_id' => $metal->id, 'base_cost' => 3800, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $stratosDrone->id, 'resource_id' => $crystal->id, 'base_cost' => 1300, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $solarInterceptor->id, 'resource_id' => $metal->id, 'base_cost' => 7500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $solarInterceptor->id, 'resource_id' => $crystal->id, 'base_cost' => 5000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $vectorBomber->id, 'resource_id' => $metal->id, 'base_cost' => 25000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $vectorBomber->id, 'resource_id' => $crystal->id, 'base_cost' => 18500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $vectorBomber->id, 'resource_id' => $deuterium->id, 'base_cost' => 7500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $deltaTransport->id, 'resource_id' => $metal->id, 'base_cost' => 2500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $deltaTransport->id, 'resource_id' => $crystal->id, 'base_cost' => 2500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $defensiveFrigate->id, 'resource_id' => $metal->id, 'base_cost' => 12500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $defensiveFrigate->id, 'resource_id' => $crystal->id, 'base_cost' => 8500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $defensiveFrigate->id, 'resource_id' => $deuterium->id, 'base_cost' => 2500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $orionCruiser->id, 'resource_id' => $metal->id, 'base_cost' => 30000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $orionCruiser->id, 'resource_id' => $crystal->id, 'base_cost' => 18500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $commandShip->id, 'resource_id' => $metal->id, 'base_cost' => 37500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $commandShip->id, 'resource_id' => $crystal->id, 'base_cost' => 50000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $commandShip->id, 'resource_id' => $deuterium->id, 'base_cost' => 18500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $siegePlatform->id, 'resource_id' => $metal->id, 'base_cost' => 62500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $siegePlatform->id, 'resource_id' => $crystal->id, 'base_cost' => 50000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $siegePlatform->id, 'resource_id' => $deuterium->id, 'base_cost' => 18500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $quantumScout->id, 'resource_id' => $metal->id, 'base_cost' => 10000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $quantumScout->id, 'resource_id' => $crystal->id, 'base_cost' => 10000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $novaDreadnought->id, 'resource_id' => $metal->id, 'base_cost' => 250000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $novaDreadnought->id, 'resource_id' => $crystal->id, 'base_cost' => 185000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $novaDreadnought->id, 'resource_id' => $deuterium->id, 'base_cost' => 100000, 'cost_multiplier' => 1.3, 'level' => 1],
            
            // DÉFENSES
            ['build_id' => $laserCannon->id, 'resource_id' => $metal->id, 'base_cost' => 2500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $laserCannon->id, 'resource_id' => $crystal->id, 'base_cost' => 1300, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $missileLauncher->id, 'resource_id' => $metal->id, 'base_cost' => 5000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $missileLauncher->id, 'resource_id' => $crystal->id, 'base_cost' => 2500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $plasmaBattery->id, 'resource_id' => $metal->id, 'base_cost' => 12500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $plasmaBattery->id, 'resource_id' => $crystal->id, 'base_cost' => 6250, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $plasmaBattery->id, 'resource_id' => $deuterium->id, 'base_cost' => 2500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $kineticTurret->id, 'resource_id' => $metal->id, 'base_cost' => 25000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $kineticTurret->id, 'resource_id' => $crystal->id, 'base_cost' => 12500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $kineticTurret->id, 'resource_id' => $deuterium->id, 'base_cost' => 5000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $jammingField->id, 'resource_id' => $metal->id, 'base_cost' => 37500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $jammingField->id, 'resource_id' => $crystal->id, 'base_cost' => 25000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $jammingField->id, 'resource_id' => $deuterium->id, 'base_cost' => 12500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $shieldGenerator->id, 'resource_id' => $metal->id, 'base_cost' => 75000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $shieldGenerator->id, 'resource_id' => $crystal->id, 'base_cost' => 50000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $shieldGenerator->id, 'resource_id' => $deuterium->id, 'base_cost' => 25000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $orbitalCannon->id, 'resource_id' => $metal->id, 'base_cost' => 125000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $orbitalCannon->id, 'resource_id' => $crystal->id, 'base_cost' => 75000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $orbitalCannon->id, 'resource_id' => $deuterium->id, 'base_cost' => 37500, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $empTurret->id, 'resource_id' => $metal->id, 'base_cost' => 25000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $empTurret->id, 'resource_id' => $crystal->id, 'base_cost' => 50000, 'cost_multiplier' => 1.3, 'level' => 1],
            ['build_id' => $empTurret->id, 'resource_id' => $deuterium->id, 'base_cost' => 12500, 'cost_multiplier' => 1.3, 'level' => 1],
        ];

        foreach ($costs as $cost) {
            TemplateBuildCost::create($cost);
        }
    }
}