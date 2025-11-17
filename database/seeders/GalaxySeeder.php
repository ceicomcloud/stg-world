<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Server\ServerConfig;
use App\Models\Template\TemplatePlanet;
use Illuminate\Support\Facades\DB;

class GalaxySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupération des configurations du serveur
        $galaxies = (int) ServerConfig::where('key', 'galaxies')->value('value') ?? 1;
        $systemsPerGalaxy = (int) ServerConfig::where('key', 'systems_per_galaxy')->value('value') ?? 100;
        $planetsPerSystem = (int) ServerConfig::where('key', 'planets_per_system')->value('value') ?? 10;
        $totalPlanets = (int) ServerConfig::where('key', 'total_planets')->value('value') ?? 5000;
        
        // Calcul du nombre maximum de planètes possibles
        $maxPossiblePlanets = $galaxies * $systemsPerGalaxy * $planetsPerSystem;
        
        // Ajustement si le total_planets dépasse la capacité
        if ($totalPlanets > $maxPossiblePlanets) {
            $this->command->warn("Le nombre total de planètes ({$totalPlanets}) dépasse la capacité maximale ({$maxPossiblePlanets}). Ajustement à {$maxPossiblePlanets}.");
            $totalPlanets = $maxPossiblePlanets;
        }
        
        $this->command->info("Génération de {$totalPlanets} planètes dans {$galaxies} galaxies...");
        
        // Vider la table avant de la remplir (en respectant les contraintes de clés étrangères)
        TemplatePlanet::query()->delete();
        
        $planetsCreated = 0;
        $batchSize = 1000; // Traitement par lots pour optimiser les performances
        $planetsData = [];
        
        // Types de planètes avec leurs probabilités
        $planetTypes = [
            'planet' => 0.85,  // 85% de planètes normales
            'asteroid' => 0.10, // 10% d'astéroïdes
            'debris' => 0.05    // 5% de champs de débris
        ];
        
        // Tailles de planètes avec leurs probabilités
        $planetSizes = [
            'tiny' => 0.15,
            'small' => 0.25,
            'medium' => 0.35,
            'large' => 0.20,
            'huge' => 0.05
        ];
        
        for ($galaxy = 1; $galaxy <= $galaxies && $planetsCreated < $totalPlanets; $galaxy++) {
            for ($system = 1; $system <= $systemsPerGalaxy && $planetsCreated < $totalPlanets; $system++) {
                for ($position = 1; $position <= $planetsPerSystem && $planetsCreated < $totalPlanets; $position++) {
                    
                    // Détermination du type de planète
                    $type = $this->getRandomType($planetTypes);
                    
                    // Détermination de la taille
                    $size = $this->getRandomSize($planetSizes);
                    
                    // Calcul des propriétés basées sur la taille et le type
                    $properties = $this->calculatePlanetProperties($size, $type, $position);
                    
                    $planetsData[] = [
                        'galaxy' => $galaxy,
                        'system' => $system,
                        'position' => $position,
                        'name' => $this->generatePlanetName($galaxy, $system, $position),
                        'type' => $type,
                        'size' => $size,
                        'diameter' => $properties['diameter'],
                        'min_temperature' => $properties['min_temperature'],
                        'max_temperature' => $properties['max_temperature'],
                        'fields' => $properties['fields'],
                        'metal_bonus' => $properties['metal_bonus'],
                        'crystal_bonus' => $properties['crystal_bonus'],
                        'deuterium_bonus' => $properties['deuterium_bonus'],
                        'energy_bonus' => $properties['energy_bonus'],
                        'is_colonizable' => $type === 'planet',
                        'is_occupied' => false,
                        'is_available' => true,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $planetsCreated++;
                    
                    // Insertion par lots pour optimiser les performances
                    if (count($planetsData) >= $batchSize) {
                        DB::table('template_planets')->insert($planetsData);
                        $planetsData = [];
                        $this->command->info("Créé {$planetsCreated} planètes...");
                    }
                }
            }
        }
        
        // Insertion des dernières planètes restantes
        if (!empty($planetsData)) {
            DB::table('template_planets')->insert($planetsData);
        }
        
        $this->command->info("Génération terminée ! {$planetsCreated} planètes créées dans {$galaxies} galaxies.");
    }
    
    /**
     * Sélectionne un type de planète aléatoire basé sur les probabilités
     */
    private function getRandomType(array $types): string
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;
        
        foreach ($types as $type => $probability) {
            $cumulative += $probability;
            if ($rand <= $cumulative) {
                return $type;
            }
        }
        
        return 'planet'; // Fallback
    }
    
    /**
     * Sélectionne une taille de planète aléatoire basée sur les probabilités
     */
    private function getRandomSize(array $sizes): string
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;
        
        foreach ($sizes as $size => $probability) {
            $cumulative += $probability;
            if ($rand <= $cumulative) {
                return $size;
            }
        }
        
        return 'medium'; // Fallback
    }
    
    /**
     * Calcule les propriétés d'une planète basées sur sa taille, son type et sa position
     */
    private function calculatePlanetProperties(string $size, string $type, int $position): array
    {
        // Diamètres basés sur la taille
        $diameters = [
            'tiny' => rand(4000, 6000),
            'small' => rand(6000, 8000),
            'medium' => rand(8000, 12000),
            'large' => rand(12000, 16000),
            'huge' => rand(16000, 20000)
        ];
        
        // Champs de construction basés sur la taille
        $fields = [
            'tiny' => rand(80, 120),
            'small' => rand(120, 180),
            'medium' => rand(180, 250),
            'large' => rand(250, 320),
            'huge' => rand(320, 400)
        ];
        
        // Température basée sur la position dans le système (plus proche du soleil = plus chaud)
        $baseTemp = 50 - ($position * 15); // Position 1 = ~35°C, Position 10 = ~-100°C
        $tempVariation = rand(-20, 20);
        $minTemp = $baseTemp + $tempVariation - 30;
        $maxTemp = $baseTemp + $tempVariation + 30;
        
        // Bonus de ressources basés sur le type et la position
        $metalBonus = 1.0;
        $crystalBonus = 1.0;
        $deuteriumBonus = 1.0;
        $energyBonus = 1.0;
        
        if ($type === 'planet') {
            // Les planètes proches du soleil ont plus de métal et d'énergie
            if ($position <= 3) {
                $metalBonus = rand(110, 130) / 100;
                $energyBonus = rand(105, 120) / 100;
            }
            // Les planètes moyennes ont plus de cristal
            elseif ($position >= 4 && $position <= 7) {
                $crystalBonus = rand(110, 130) / 100;
            }
            // Les planètes éloignées ont plus de deutérium
            else {
                $deuteriumBonus = rand(110, 150) / 100;
            }
        }
        
        // Ajustements pour les astéroïdes et débris
        if ($type === 'asteroid') {
            $fields[$size] = (int)($fields[$size] * 0.3); // Moins de champs
            $metalBonus *= 1.5; // Plus de métal
        } elseif ($type === 'debris') {
            $fields[$size] = 0; // Pas de champs constructibles
            $metalBonus *= 0.5;
            $crystalBonus *= 0.5;
        }
        
        return [
            'diameter' => $diameters[$size],
            'min_temperature' => $minTemp,
            'max_temperature' => $maxTemp,
            'fields' => $fields[$size],
            'metal_bonus' => round($metalBonus, 2),
            'crystal_bonus' => round($crystalBonus, 2),
            'deuterium_bonus' => round($deuteriumBonus, 2),
            'energy_bonus' => round($energyBonus, 2),
        ];
    }
    
    /**
     * Génère un nom de planète basé sur sa position
     */
    private function generatePlanetName(int $galaxy, int $system, int $position): string
    {
        $prefixes = [
            'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta',
            'Nova', 'Stellar', 'Cosmic', 'Nebula', 'Orion', 'Vega', 'Sirius', 'Rigel',
            'Proxima', 'Centauri', 'Andromeda', 'Cassiopeia', 'Perseus', 'Draco'
        ];
        
        $suffixes = [
            'Prime', 'Major', 'Minor', 'Secundus', 'Tertius', 'Quartus',
            'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'
        ];
        
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = $suffixes[($position - 1) % count($suffixes)];
        
        return "{$prefix} {$suffix}";
    }
}