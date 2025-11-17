<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template\TemplateBuildCost;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateResource;

class TechnologyResearchCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer la ressource research_points
        $researchPoints = TemplateResource::where('name', 'research_points')->first();
        
        if (!$researchPoints) {
            echo "Erreur: La ressource research_points n'existe pas. Veuillez d'abord exécuter TemplateResourceSeeder.\n";
            return;
        }

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

        // Coûts en research_points pour les technologies
        $technologyCosts = [
            // Technologies de base
            ['build_id' => $energyEfficiency?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 500, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $reinforcedArmor?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 500, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $tacticalEspionage?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 500, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $deploymentSpeed?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 500, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $advancedStorage?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 500, 'cost_multiplier' => 1.8, 'level' => 1],

            // Technologies intermédiaires
            ['build_id' => $improvedWeapons?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 1000, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $optimizedRange?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 1000, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $digitalCamouflage?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 1000, 'cost_multiplier' => 1.8, 'level' => 1],

            // Technologies avancées
            ['build_id' => $productionAccelerator?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 2000, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $strategicCommand?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 2000, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $reactiveArmor?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 2000, 'cost_multiplier' => 1.8, 'level' => 1],

            // Technologies expertes
            ['build_id' => $hyperpropulsion?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 4000, 'cost_multiplier' => 1.8, 'level' => 1],
            ['build_id' => $advancedLogistics?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 4000, 'cost_multiplier' => 1.8, 'level' => 1],

            // Technologies maîtres
            ['build_id' => $appliedScience?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 8000, 'cost_multiplier' => 1.8, 'level' => 1],

            // Technologies ultimes
            ['build_id' => $inversionField?->id, 'resource_id' => $researchPoints->id, 'base_cost' => 16000, 'cost_multiplier' => 1.8, 'level' => 1],
        ];

        // Insérer les coûts en research_points
        foreach ($technologyCosts as $cost) {
            if ($cost['build_id']) {
                TemplateBuildCost::create([
                    'build_id' => $cost['build_id'],
                    'resource_id' => $researchPoints->id,
                    'base_cost' => $cost['base_cost'],
                    'cost_multiplier' => $cost['cost_multiplier'],
                    'level' => 1
                ]);
            }
        }
    }
}