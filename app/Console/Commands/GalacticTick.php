<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GalacticEventService;
use App\Models\Server\ServerConfig;

class GalacticTick extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'galactic:tick {--count=3} {--galaxy=} {--system=} {--position=} {--dry-run}';

    /**
     * The console command description.
     */
    protected $description = 'Génère des événements galactiques aléatoires (galaxie/système/position).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $service = new GalacticEventService();

        $count = (int) ($this->option('count') ?? 3);
        $count = max(1, $count);

        $galaxies = ServerConfig::getGalaxies();
        $systemsPerGalaxy = ServerConfig::getSystemsPerGalaxy();
        $positionsPerSystem = ServerConfig::getPlanetsPerSystem();

        $this->info("Galactic tick: génération de {$count} événement(s)");

        for ($i = 0; $i < $count; $i++) {
            $galaxy = $this->option('galaxy') ? (int) $this->option('galaxy') : random_int(1, max(1, $galaxies));
            $system = $this->option('system') ? (int) $this->option('system') : random_int(1, max(1, $systemsPerGalaxy));
            $position = $this->option('position') ? (int) $this->option('position') : random_int(1, max(1, $positionsPerSystem));

            if ($this->option('dry-run')) {
                $this->line("[DRY] Événement (ambient) à {$galaxy}:{$system}:{$position}");
                continue;
            }

            try {
                $ev = $service->spawnAmbientEvent($galaxy, $system, $position);
                $endAt = method_exists($ev, 'end_at') ? (string) $ev->end_at : (string) $ev->getAttribute('end_at');
                $this->info(sprintf(
                    'Créé: %s (%s) %s à %d:%d:%s, fin %s',
                    $ev->title,
                    $ev->severity,
                    $ev->key,
                    $ev->galaxy,
                    $ev->system,
                    $ev->position ?? '-',
                    is_string($endAt) ? $endAt : (string) $endAt
                ));
            } catch (\Throwable $e) {
                $this->error(sprintf('Échec création événement à %d:%d:%s — %s', $galaxy, $system, $position, $e->getMessage()));
            }
        }

        $this->comment('Terminé.');
        return self::SUCCESS;
    }
}