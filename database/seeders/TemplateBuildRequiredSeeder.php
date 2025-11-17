<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template\TemplateBuildRequired;
use App\Models\Template\TemplateBuild;

class TemplateBuildRequiredSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
        
        // Récupérer les IDs des technologies
        $tacticalEspionage = TemplateBuild::where('name', 'espionnage_tactique')->first();
        $reinforcedArmor = TemplateBuild::where('name', 'blindage_renforce')->first();
        $deploymentSpeed = TemplateBuild::where('name', 'vitesse_deploiement')->first();
        $advancedStorage = TemplateBuild::where('name', 'stockage_avance')->first();
        $energyEfficiency = TemplateBuild::where('name', 'efficacite_energetique')->first();
        $improvedWeapons = TemplateBuild::where('name', 'armement_ameliore')->first();
        $optimizedRange = TemplateBuild::where('name', 'portee_optimisee')->first();
        $digitalCamouflage = TemplateBuild::where('name', 'camouflage_numerique')->first();
        $productionAccelerator = TemplateBuild::where('name', 'accelerateur_production')->first();
        $strategicCommand = TemplateBuild::where('name', 'commandement_strategique')->first();
        $reactiveArmor = TemplateBuild::where('name', 'armure_reactive')->first();
        $hyperpropulsion = TemplateBuild::where('name', 'hyperpropulsion')->first();
        $advancedLogistics = TemplateBuild::where('name', 'logistique_avancee')->first();
        $appliedScience = TemplateBuild::where('name', 'science_appliquee')->first();
        $inversionField = TemplateBuild::where('name', 'champ_inversion')->first();

        $requirements = [
            [
                'build_id' => $metalStorage->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 4,
                'is_active' => true,
            ],
            [
                'build_id' => $crystalStorage->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 4,
                'is_active' => true,
            ],
            [
                'build_id' => $deuteriumTank->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 4,
                'is_active' => true,
            ],
            
            [
                'build_id' => $researchLab->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 6,
                'is_active' => true,
            ],
            
            [
                'build_id' => $barracks->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 8,
                'is_active' => true,
            ],
            [
                'build_id' => $shipyard->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 8,
                'is_active' => true,
            ],
            [
                'build_id' => $defensivePlatform->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 8,
                'is_active' => true,
            ],
            
            [
                'build_id' => $terraformer->id,
                'required_build_id' => $commandCenter->id,
                'required_level' => 12,
                'is_active' => true,
            ],

            // SHIP
            [
                'build_id' => $stratosDrone->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 2,
                'is_active' => true,
            ],
            [
                'build_id' => $solarInterceptor->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 4,
                'is_active' => true,
            ],
            [
                'build_id' => $vectorBomber->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 6,
                'is_active' => true,
            ],
            [
                'build_id' => $deltaTransport->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 8,
                'is_active' => true,
            ],
            [
                'build_id' => $defensiveFrigate->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 10,
                'is_active' => true,
            ],
            [
                'build_id' => $orionCruiser->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 12,
                'is_active' => true,
            ],
            [
                'build_id' => $commandShip->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 14,
                'is_active' => true,
            ],
            [
                'build_id' => $siegePlatform->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 16,
                'is_active' => true,
            ],
            [
                'build_id' => $quantumScout->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 18,
                'is_active' => true,
            ],
            [
                'build_id' => $novaDreadnought->id,
                'required_build_id' => $shipyard->id,
                'required_level' => 20,
                'is_active' => true,
            ],

            // DEFENSE
            [
                'build_id' => $kineticTurret->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 2,
                'is_active' => true,
            ],
            [
                'build_id' => $laserCannon->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 4,
                'is_active' => true,
            ],
            [
                'build_id' => $missileLauncher->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 6,
                'is_active' => true,
            ],
            [
                'build_id' => $plasmaBattery->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 8,
                'is_active' => true,
            ],
            [
                'build_id' => $jammingField->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 10,
                'is_active' => true,
            ],
            [
                'build_id' => $shieldGenerator->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 12,
                'is_active' => true,
            ],
            [
                'build_id' => $surveillanceTower->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 14,
                'is_active' => true,
            ],
            [
                'build_id' => $orbitalCannon->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 16,
                'is_active' => true,
            ],
            [
                'build_id' => $solarMines->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 18,
                'is_active' => true,
            ],
            [
                'build_id' => $empTurret->id,
                'required_build_id' => $defensivePlatform->id,
                'required_level' => 20,
                'is_active' => true,
            ],
            
            // TECHNOLOGIES
            [
                'build_id' => $tacticalEspionage->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 2,
                'is_active' => true,
            ],
            [
                'build_id' => $reinforcedArmor->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 4,
                'is_active' => true,
            ],
            [
                'build_id' => $deploymentSpeed->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 6,
                'is_active' => true,
            ],
            [
                'build_id' => $advancedStorage->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 8,
                'is_active' => true,
            ],
            [
                'build_id' => $improvedWeapons->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 10,
                'is_active' => true,
            ],
            [
                'build_id' => $optimizedRange->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 12,
                'is_active' => true,
            ],
            [
                'build_id' => $digitalCamouflage->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 14,
                'is_active' => true,
            ],
            [
                'build_id' => $productionAccelerator->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 16,
                'is_active' => true,
            ],
            [
                'build_id' => $strategicCommand->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 18,
                'is_active' => true,
            ],
            [
                'build_id' => $reactiveArmor->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 20,
                'is_active' => true,
            ],
            [
                'build_id' => $hyperpropulsion->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 22,
                'is_active' => true,
            ],
            [
                'build_id' => $advancedLogistics->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 24,
                'is_active' => true,
            ],
            [
                'build_id' => $appliedScience->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 26,
                'is_active' => true,
            ],
            [
                'build_id' => $inversionField->id,
                'required_build_id' => $researchLab->id,
                'required_level' => 28,
                'is_active' => true,
            ],

            // UNITS
            [
                'build_id' => $standardInfantry->id,
                'required_build_id' => $barracks->id,
                'required_level' => 2,
                'is_active' => true,
            ],
			[
                'build_id' => $tacticalCommando->id,
                'required_build_id' => $barracks->id,
                'required_level' => 4,
                'is_active' => true,
            ],
			[
                'build_id' => $reconScout->id,
                'required_build_id' => $barracks->id,
                'required_level' => 6,
                'is_active' => true,
            ],
			[
                'build_id' => $fieldEngineer->id,
                'required_build_id' => $barracks->id,
                'required_level' => 8,
                'is_active' => true,
            ],
			[
                'build_id' => $combatDrone->id,
                'required_build_id' => $barracks->id,
                'required_level' => 10,
                'is_active' => true,
            ],
			[
                'build_id' => $heavyArmoredUnit->id,
                'required_build_id' => $barracks->id,
                'required_level' => 12,
                'is_active' => true,
            ],
			[
                'build_id' => $ionicSniper->id,
                'required_build_id' => $barracks->id,
                'required_level' => 14,
                'is_active' => true,
            ],
			[
                'build_id' => $jammingTechnician->id,
                'required_build_id' => $barracks->id,
                'required_level' => 16,
                'is_active' => true,
            ],
			[
                'build_id' => $pyrosoldier->id,
                'required_build_id' => $barracks->id,
                'required_level' => 18,
                'is_active' => true,
            ],
			[
                'build_id' => $artilleryOperator->id,
                'required_build_id' => $barracks->id,
                'required_level' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($requirements as $requirement) {
            TemplateBuildRequired::create($requirement);
        }
    }
}