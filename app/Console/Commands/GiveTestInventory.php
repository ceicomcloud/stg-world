<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Template\TemplateInventory;
use App\Models\User\UserInventory;

class GiveTestInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage: php artisan inventory:give-test {userId}
     */
    protected $signature = 'inventory:give-test {userId}';

    /**
     * The console command description.
     */
    protected $description = 'Ajoute 1 exemplaire de chaque TemplateInventory à un utilisateur donné (pour tests visuels).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = (int) $this->argument('userId');
        $user = User::find($userId);
        if (!$user) {
            $this->error("Utilisateur {$userId} introuvable.");
            return Command::FAILURE;
        }

        $templates = TemplateInventory::query()->get();
        $count = 0;

        foreach ($templates as $tpl) {
            $inv = UserInventory::firstOrCreate([
                'user_id' => $user->id,
                'template_inventory_id' => $tpl->id,
            ], [
                'quantity' => 0,
                'acquired_at' => now(),
            ]);

            $inv->quantity = ($inv->quantity ?? 0) + 1;
            $inv->save();
            $count++;
        }

        $this->info("Ajouté 1 exemplaire pour {$count} templates à l’utilisateur {$user->id}.");
        return Command::SUCCESS;
    }
}