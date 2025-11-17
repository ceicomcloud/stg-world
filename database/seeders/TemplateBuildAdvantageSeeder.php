<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template\TemplateBuildAdvantage;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateResource;

class TemplateBuildAdvantageSeeder extends Seeder
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
        $deltaTransport = TemplateBuild::where('name', 'transport_delta')->first();
        $defensiveFrigate = TemplateBuild::where('name', 'fregate_defensive')->first();
        $orionCruiser = TemplateBuild::where('name', 'croiseur_orion')->first();
        $commandShip = TemplateBuild::where('name', 'vaisseau_commandement')->first();
        $siegePlatform = TemplateBuild::where('name', 'plateforme_siege')->first();
        $quantumScout = TemplateBuild::where('name', 'scout_quantique')->first();
        $novaDreadnought = TemplateBuild::where('name', 'dreadnought_nova')->first();
        
        // Récupérer les IDs des défenses
        $kineticTurret = TemplateBuild::where('name', 'tourelle_cinetique')->first();
        $laserCannon = TemplateBuild::where('name', 'canon_laser')->first();
        $missileLauncher = TemplateBuild::where('name', 'lanceur_missiles')->first();
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

        $advantages = [
            // BÂTIMENTS DE RESSOURCES
            // Mine de métal - production de métal
            [
                'build_id' => $metalMine->id,
                'advantage_type' => 'production_boost',
                'target_type' => 'resource',
                'resource_id' => $metal->id,
                'base_value' => 50.0,
                'value_per_level' => 20.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Mine de cristal - production de cristal
            [
                'build_id' => $crystalMine->id,
                'advantage_type' => 'production_boost',
                'target_type' => 'resource',
                'resource_id' => $crystal->id,
                'base_value' => 40.0,
                'value_per_level' => 15.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Synthétiseur de deutérium - production de deutérium
            [
                'build_id' => $deuteriumSynth->id,
                'advantage_type' => 'production_boost',
                'target_type' => 'resource',
                'resource_id' => $deuterium->id,
                'base_value' => 30.0,
                'value_per_level' => 12.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Centrale solaire - production d'énergie
            [
                'build_id' => $solarPlant->id,
                'advantage_type' => 'energy_production',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 100.0,
                'value_per_level' => 50.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Centre de commandement - efficacité globale
            [
                'build_id' => $commandCenter->id,
                'advantage_type' => 'production_boost',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 45.0,
                'value_per_level' => 45.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Centre de commandement - capacité du bunker (progression triangulaire, calcul côté modèle)
            [
                'build_id' => $commandCenter->id,
                'advantage_type' => 'bunker_boost',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 0.0,
                'value_per_level' => 50000.0, // 50k * somme(1..niveau) (voir modèle)
                'calculation_type' => 'additive',
                'is_active' => true,
            ],
            // Centre de commandement - capacité max de flottes en vol (0.5 par niveau)
            [
                'build_id' => $commandCenter->id,
                'advantage_type' => 'fleet_capacity',
                'target_type' => 'fleet',
                'resource_id' => null,
                'base_value' => 0.0,
                'value_per_level' => 0.5,
                'calculation_type' => 'additive',
                'is_active' => true,
            ],
            // Centre de commandement - vitesse de construction des bâtiments
            [
                'build_id' => $commandCenter->id,
                'advantage_type' => 'build_speed',
                'target_type' => 'building',
                'resource_id' => null,
                'base_value' => 5.0,
                'value_per_level' => 10.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            
            // Centre de recherche - vitesse de recherche
            [
                'build_id' => $researchLab->id,
                'advantage_type' => 'research_speed',
                'target_type' => 'technology',
                'resource_id' => null,
                'base_value' => 10.0,
                'value_per_level' => 10.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Caserne - formation d'unités terrestres
            [
                'build_id' => $barracks->id,
                'advantage_type' => 'build_speed',
                'target_type' => 'unit',
                'resource_id' => null,
                'base_value' => 30.0,
                'value_per_level' => 30.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Chantier spatial - construction de vaisseaux
            [
                'build_id' => $shipyard->id,
                'advantage_type' => 'build_speed',
                'target_type' => 'ship',
                'resource_id' => null,
                'base_value' => 30.0,
                'value_per_level' => 30.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Plateforme défensive - défense planétaire (bonus de défense)
            [
                'build_id' => $defensivePlatform->id,
                'advantage_type' => 'defense_boost',
                'target_type' => 'defense',
                'resource_id' => null,
                'base_value' => 25.0,
                'value_per_level' => 25.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Plateforme défensive - vitesse de construction des défenses
            [
                'build_id' => $defensivePlatform->id,
                'advantage_type' => 'build_speed',
                'target_type' => 'defense',
                'resource_id' => null,
                'base_value' => 30.0,
                'value_per_level' => 30.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Terraformeur planétaire - expansion territoriale
            [
                'build_id' => $terraformer->id,
                'advantage_type' => 'territory_expansion',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 10.0,
                'value_per_level' => 10.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Stockage de métal - capacité de stockage
            [
                'build_id' => $metalStorage->id,
                'advantage_type' => 'storage_bonus',
                'target_type' => 'resource',
                'resource_id' => $metal->id,
                'base_value' => 120000.0,
                'value_per_level' => 60000.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Stockage de cristal - capacité de stockage
            [
                'build_id' => $crystalStorage->id,
                'advantage_type' => 'storage_bonus',
                'target_type' => 'resource',
                'resource_id' => $crystal->id,
                'base_value' => 60000.0,
                'value_per_level' => 30000.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Stockage de deutérium - capacité de stockage
            [
                'build_id' => $deuteriumTank->id,
                'advantage_type' => 'storage_bonus',
                'target_type' => 'resource',
                'resource_id' => $deuterium->id,
                'base_value' => 30000.0,
                'value_per_level' => 15000.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            
            // TECHNOLOGIES
            // Efficacité énergétique - réduction consommation énergétique
            [
                'build_id' => $energyEfficiency->id,
                'advantage_type' => 'energy_production',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 15.0,
                'value_per_level' => 15.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Armure renforcée - résistance aux dégâts
            [
                'build_id' => $reinforcedArmor->id,
                'advantage_type' => 'armor_boost',
                'target_type' => 'unit',
                'resource_id' => null,
                'base_value' => 10.0,
                'value_per_level' => 10.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Espionnage tactique - efficacité des missions d'espionnage
            [
                'build_id' => $tacticalEspionage->id,
                'advantage_type' => 'espionage_efficiency',
                'target_type' => 'technology',
                'resource_id' => null,
                'base_value' => 0.05,
                'value_per_level' => 0.02,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Vitesse de déploiement - rapidité de mouvement des unités
            [
                'build_id' => $deploymentSpeed->id,
                'advantage_type' => 'movement_speed',
                'target_type' => 'ship',
                'resource_id' => null,
                'base_value' => 0.10,
                'value_per_level' => 0.05,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Stockage avancé - capacité de stockage améliorée
            [
                'build_id' => $advancedStorage->id,
                'advantage_type' => 'storage_bonus',
                'target_type' => 'resource',
                'resource_id' => null,
                'base_value' => 20.0,
                'value_per_level' => 20.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Armes améliorées - puissance d'attaque
            [
                'build_id' => $improvedWeapons->id,
                'advantage_type' => 'weapon_power',
                'target_type' => 'unit',
                'resource_id' => null,
                'base_value' => 12.0,
                'value_per_level' => 12.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Portée optimisée - portée d'attaque améliorée
            [
                'build_id' => $optimizedRange->id,
                'advantage_type' => 'attack_range',
                'target_type' => 'unit',
                'resource_id' => null,
                'base_value' => 10.0,
                'value_per_level' => 10.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Camouflage numérique - furtivité améliorée
            [
                'build_id' => $digitalCamouflage->id,
                'advantage_type' => 'stealth_boost',
                'target_type' => 'unit',
                'resource_id' => null,
                'base_value' => 18.0,
                'value_per_level' => 18.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Accélérateur de production - vitesse de production
            [
                'build_id' => $productionAccelerator->id,
                'advantage_type' => 'production_speed',
                'target_type' => 'resource',
                'resource_id' => null,
                'base_value' => 15.0,
                'value_per_level' => 15.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Commandement stratégique - efficacité de commandement (missions)
            [
                'build_id' => $strategicCommand->id,
                'advantage_type' => 'command_efficiency',
                'target_type' => 'fleet',
                'resource_id' => null,
                'base_value' => 5.0,
                'value_per_level' => 5.0,
                'calculation_type' => 'additive',
                'is_active' => true,
            ],
            // Commandement stratégique - efficacité des ressources en mission (extraction/exploration)
            [
                'build_id' => $strategicCommand->id,
                'advantage_type' => 'resource_efficiency',
                'target_type' => 'mission',
                'resource_id' => null,
                'base_value' => 0.10,
                'value_per_level' => 0.05,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Armure réactive - protection avancée
            [
                'build_id' => $reactiveArmor->id,
                'advantage_type' => 'armor_boost',
                'target_type' => 'unit',
                'resource_id' => null,
                'base_value' => 25.0,
                'value_per_level' => 25.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Hyperpropulsion - vitesse de déplacement maximale
            [
                'build_id' => $hyperpropulsion->id,
                'advantage_type' => 'movement_speed',
                'target_type' => 'ship',
                'resource_id' => null,
                'base_value' => 30.0,
                'value_per_level' => 30.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Logistique avancée - efficacité des ressources
            [
                'build_id' => $advancedLogistics->id,
                'advantage_type' => 'resource_efficiency',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 12.0,
                'value_per_level' => 12.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Science appliquée - bonus de recherche
            [
                'build_id' => $appliedScience->id,
                'advantage_type' => 'research_speed',
                'target_type' => 'technology',
                'resource_id' => null,
                'base_value' => 0.15,
                'value_per_level' => 0.10,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Champ d'inversion - technologie ultime
            [
                'build_id' => $inversionField->id,
                'advantage_type' => 'ultimate_boost',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 50.0,
                'value_per_level' => 50.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
        ];

        foreach ($advantages as $advantage) {
            TemplateBuildAdvantage::create($advantage);
        }
    }
}