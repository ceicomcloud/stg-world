<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template\TemplateBuildDisadvantage;
use App\Models\Template\TemplateBuild;

class TemplateBuildDisadvantageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        // Récupérer les IDs des bâtiments
        $metalMine = TemplateBuild::where('name', 'mine_fer')->first();
        $crystalMine = TemplateBuild::where('name', 'extracteur_cristal')->first();
        $deuteriumSynth = TemplateBuild::where('name', 'raffinerie_deuterium')->first();
        $solarPlant = TemplateBuild::where('name', 'centrale_solaire')->first();
        // $fusionReactor = TemplateBuild::where('name', 'reacteur_fusion')->first();
        // $robotFactory = TemplateBuild::where('name', 'usine_robots')->first();
        // $naniteFactory = TemplateBuild::where('name', 'usine_nanites')->first();
        $shipyard = TemplateBuild::where('name', 'chantier_spatial')->first();
        $researchLab = TemplateBuild::where('name', 'centre_recherche')->first();
        $terraformer = TemplateBuild::where('name', 'terraformeur_planetaire')->first();

        $disadvantages = [
            // BÂTIMENTS DE RESSOURCES - Consommation d'énergie
            [
                'build_id' => $metalMine->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 10.0,
                'value_per_level' => 12.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            [
                'build_id' => $crystalMine->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 15.0,
                'value_per_level' => 15.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            [
                'build_id' => $deuteriumSynth->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 20.0,
                'value_per_level' => 18.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Réacteur de fusion - consomme du deutérium
            /*[
                'build_id' => $fusionReactor->id,
                'disadvantage_type' => 'resource_consumption',
                'target_type' => 'resource',
                'base_value' => 10,
                'value_per_level' => 10,
                'calculation_type' => 'additive',
                'is_active' => true,
            ],*/
            
            // BÂTIMENTS DE FACILITÉS - Consommation d'énergie
            /*[
                'build_id' => $robotFactory->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'base_value' => 50,
                'value_per_level' => 25,
                'calculation_type' => 'additive',
                'is_active' => true,
            ],
            [
                'build_id' => $naniteFactory->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'base_value' => 100,
                'value_per_level' => 50,
                'calculation_type' => 'additive',
                'is_active' => true,
            ],*/
            [
                'build_id' => $shipyard->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 50.0,
                'value_per_level' => 35.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            [
                'build_id' => $researchLab->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 45.0,
                'value_per_level' => 30.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            // Terraformeur - consomme beaucoup d'énergie et de deutérium
            [
                'build_id' => $terraformer->id,
                'disadvantage_type' => 'energy_consumption',
                'target_type' => 'global',
                'resource_id' => null,
                'base_value' => 2000.0,
                'value_per_level' => 2500.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
            [
                'build_id' => $terraformer->id,
                'disadvantage_type' => 'maintenance_cost',
                'target_type' => 'resource',
                'resource_id' => 3, // deuterium
                'base_value' => 2000.0,
                'value_per_level' => 2500.0,
                'calculation_type' => 'multiplicative',
                'is_active' => true,
            ],
        ];

        foreach ($disadvantages as $disadvantage) {
            TemplateBuildDisadvantage::create($disadvantage);
        }
    }
}