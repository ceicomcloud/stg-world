<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\GalaxySeeder;
use App\Models\Server\ServerConfig;
use App\Models\Template\TemplatePlanet;

class GenerateGalaxies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galaxy:generate 
                            {--force : Force la régénération même si des planètes existent}
                            {--galaxies= : Nombre de galaxies (override config)}
                            {--systems= : Nombre de systèmes par galaxie (override config)}
                            {--planets= : Nombre de planètes par système (override config)}
                            {--total= : Nombre total de planètes (override config)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère automatiquement les galaxies, systèmes et planètes basés sur la configuration du serveur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Vérifier s'il y a déjà des planètes
        $existingPlanets = TemplatePlanet::count();
        
        if ($existingPlanets > 0 && !$this->option('force')) {
            if (!$this->confirm("Il y a déjà {$existingPlanets} planètes dans la base de données. Voulez-vous les remplacer ?")) {
                $this->info('Opération annulée.');
                return 0;
            }
        }
        
        // Afficher la configuration actuelle
        $this->displayCurrentConfig();
        
        // Temporairement override les configs si des options sont fournies
        $this->overrideConfigIfNeeded();
        
        // Exécuter le seeder
        $this->info('Démarrage de la génération des galaxies...');
        $seeder = new GalaxySeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        // Restaurer les configs originales
        $this->restoreOriginalConfig();
        
        $this->newLine();
        $this->info('✅ Génération des galaxies terminée avec succès !');
        
        // Afficher un résumé
        $this->displaySummary();
        
        return 0;
    }
    
    /**
     * Affiche la configuration actuelle
     */
    private function displayCurrentConfig()
    {
        $this->info('Configuration actuelle du serveur :');
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Galaxies', ServerConfig::where('key', 'galaxies')->value('value') ?? 'Non défini'],
                ['Systèmes par galaxie', ServerConfig::where('key', 'systems_per_galaxy')->value('value') ?? 'Non défini'],
                ['Planètes par système', ServerConfig::where('key', 'planets_per_system')->value('value') ?? 'Non défini'],
                ['Total planètes', ServerConfig::where('key', 'total_planets')->value('value') ?? 'Non défini'],
            ]
        );
        $this->newLine();
    }
    
    /**
     * Override temporairement les configs si des options sont fournies
     */
    private function overrideConfigIfNeeded()
    {
        $this->originalConfigs = [];
        
        $overrides = [
            'galaxies' => $this->option('galaxies'),
            'systems_per_galaxy' => $this->option('systems'),
            'planets_per_system' => $this->option('planets'),
            'total_planets' => $this->option('total'),
        ];
        
        foreach ($overrides as $key => $value) {
            if ($value !== null) {
                $config = ServerConfig::where('key', $key)->first();
                if ($config) {
                    $this->originalConfigs[$key] = $config->value;
                    $config->update(['value' => $value]);
                    $this->warn("Override temporaire: {$key} = {$value}");
                }
            }
        }
        
        if (!empty($this->originalConfigs)) {
            $this->newLine();
        }
    }
    
    /**
     * Restaure les configs originales
     */
    private function restoreOriginalConfig()
    {
        if (!empty($this->originalConfigs)) {
            foreach ($this->originalConfigs as $key => $originalValue) {
                ServerConfig::where('key', $key)->update(['value' => $originalValue]);
            }
            $this->info('Configurations originales restaurées.');
        }
    }
    
    /**
     * Affiche un résumé de la génération
     */
    private function displaySummary()
    {
        $totalPlanets = TemplatePlanet::count();
        $galaxies = TemplatePlanet::distinct('galaxy')->count();
        $systems = TemplatePlanet::distinct('galaxy', 'system')->count();
        
        $planetTypes = TemplatePlanet::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
        
        $this->info('Résumé de la génération :');
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Total planètes générées', $totalPlanets],
                ['Nombre de galaxies', $galaxies],
                ['Nombre de systèmes', $systems],
                ['Planètes normales', $planetTypes['planet'] ?? 0],
                ['Astéroïdes', $planetTypes['asteroid'] ?? 0],
                ['Champs de débris', $planetTypes['debris'] ?? 0],
            ]
        );
    }
    
    /**
     * Stockage temporaire des configs originales
     */
    private $originalConfigs = [];
}
