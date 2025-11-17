<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Server\ServerConfig;
use App\Models\Server\ServerSchedule;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.admin')]
class Options extends Component
{
    public bool $truceEnabled = false;
    public bool $truceBlockEarth = false;
    public bool $truceBlockSpatial = false;
    public bool $truceBlockSpy = false;
    public string $truceMessage = '';

    public string $saveStatus = '';

    // Bonus globaux
    public float $globalProductionBonusPercent = 0.0;
    public float $globalStorageBonusPercent = 0.0;
    // Shop
    public bool $shopEnabled = false;
    public float $globalShopBonusPercent = 0.0; // en % additionnel, appliqué aux achats

    // Formulaire de planification
    public string $scheduleType = 'truce'; // 'truce' | 'bonus' | 'shop_bonus'
    public ?string $scheduleStartsAt = null; // format Y-m-d H:i
    public ?string $scheduleEndsAt = null;   // format Y-m-d H:i
    public string $scheduleMessage = '';
    // Trêve
    public bool $scheduleBlockEarth = true;
    public bool $scheduleBlockSpatial = true;
    public bool $scheduleBlockSpy = true;
    // Bonus
    public float $scheduleProductionBonusPercent = 0.0;
    public float $scheduleStorageBonusPercent = 0.0;
    public float $scheduleShopBonusPercent = 0.0; // % sur l'or acheté

    // Liste des plannings
    public array $recentSchedules = [];
    public array $recentLogs = [];
    public array $eventPresets = [
        // Clés des grands événements (ajustables)
        'noel' => 'Trêve de Noël',
        'nouvel_an' => 'Trêve du Nouvel An',
        'halloween' => 'Halloween',
        'saint_valentin' => 'Saint-Valentin',
        'fete_nationale' => 'Fête nationale',
    ];

    public function mount()
    {
        $this->truceEnabled = (bool) ServerConfig::get('truce_enabled', false);
        $this->truceBlockEarth = (bool) ServerConfig::get('truce_block_earth_attack', false);
        $this->truceBlockSpatial = (bool) ServerConfig::get('truce_block_spatial_attack', false);
        $this->truceBlockSpy = (bool) ServerConfig::get('truce_block_spy', false);
        $this->truceMessage = (string) ServerConfig::get('truce_message', 'Trêve active: certaines actions sont temporairement désactivées.');

        // Charger bonus globaux existants en pourcentage
        $productionRate = (float) ServerConfig::get('production_rate', 1.0);
        $storageRate = (float) ServerConfig::get('storage_rate', 1.0);
        $this->globalProductionBonusPercent = max(0.0, ($productionRate - 1.0) * 100.0);
        $this->globalStorageBonusPercent = max(0.0, ($storageRate - 1.0) * 100.0);

        // Shop status and bonus
        $this->shopEnabled = (bool) ServerConfig::isShopEnabled();
        $shopRate = (float) ServerConfig::getShopRewardRate();
        $this->globalShopBonusPercent = max(0.0, ($shopRate - 1.0) * 100.0);

        $this->loadRecentSchedules();
    }

    public function enableAllBlocks()
    {
        $this->truceEnabled = true;
        $this->truceBlockEarth = true;
        $this->truceBlockSpatial = true;
        $this->truceBlockSpy = true;
    }

    public function disableAllBlocks()
    {
        $this->truceBlockEarth = false;
        $this->truceBlockSpatial = false;
        $this->truceBlockSpy = false;
    }

    public function save()
    {
        // Persister les valeurs en respectant les types et catégories
        ServerConfig::set('truce_enabled', $this->truceEnabled, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
        ServerConfig::set('truce_block_earth_attack', $this->truceBlockEarth, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
        ServerConfig::set('truce_block_spatial_attack', $this->truceBlockSpatial, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
        ServerConfig::set('truce_block_spy', $this->truceBlockSpy, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
        ServerConfig::set('truce_message', $this->truceMessage, ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_COMBAT);

        $this->saveStatus = 'saved';

        // Optionnel: notifier visuellement
        $this->dispatch('swal:success', [
            'title' => 'Options sauvegardées',
            'text' => 'La trêve et les options associées ont été mises à jour.'
        ]);
    }

    public function saveBonuses()
    {
        $prodRate = max(0.0, 1.0 + ($this->globalProductionBonusPercent / 100.0));
        $stockRate = max(0.0, 1.0 + ($this->globalStorageBonusPercent / 100.0));

        ServerConfig::set('production_rate', $prodRate, ServerConfig::TYPE_FLOAT, ServerConfig::CATEGORY_PRODUCTION);
        ServerConfig::set('storage_rate', $stockRate, ServerConfig::TYPE_FLOAT, ServerConfig::CATEGORY_STORAGE);

        $this->dispatch('swal:success', [
            'title' => 'Bonus appliqués',
            'text' => 'Les bonus globaux de production et stockage ont été mis à jour.'
        ]);
    }

    public function saveShopOptions()
    {
        $rate = max(0.0, 1.0 + ($this->globalShopBonusPercent / 100.0));
        ServerConfig::set('shop_enabled', $this->shopEnabled, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_SHOP);
        ServerConfig::set('shop_reward_rate', $rate, ServerConfig::TYPE_FLOAT, ServerConfig::CATEGORY_SHOP);

        $this->dispatch('swal:success', [
            'title' => 'Boutique mise à jour',
            'text' => 'État de la boutique et bonus Happy Hours enregistrés.'
        ]);
    }

    public function createSchedule()
    {
        $startsAt = $this->scheduleStartsAt ? \Carbon\Carbon::parse($this->scheduleStartsAt) : now();
        $endsAt = $this->scheduleEndsAt ? \Carbon\Carbon::parse($this->scheduleEndsAt) : null;

        $payload = [];
        if ($this->scheduleType === 'truce') {
            $payload = [
                'block_earth' => $this->scheduleBlockEarth,
                'block_spatial' => $this->scheduleBlockSpatial,
                'block_spy' => $this->scheduleBlockSpy,
            ];
        } elseif ($this->scheduleType === 'bonus') {
            $payload = [
                'production_bonus_percent' => $this->scheduleProductionBonusPercent,
                'storage_bonus_percent' => $this->scheduleStorageBonusPercent,
            ];
        } elseif ($this->scheduleType === 'shop_bonus') {
            $payload = [
                'gold_bonus_percent' => $this->scheduleShopBonusPercent,
            ];
        }

        ServerSchedule::create([
            'type' => $this->scheduleType,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'payload' => $payload,
            'message' => $this->scheduleMessage ?: null,
            'enabled' => true,
        ]);

        $this->resetScheduleForm();
        $this->loadRecentSchedules();

        $this->dispatch('swal:success', [
            'title' => 'Planning créé',
            'text' => 'Le planning a été ajouté et sera appliqué automatiquement.'
        ]);
    }

    public function toggleSchedule($id)
    {
        $schedule = ServerSchedule::find($id);
        if ($schedule) {
            $schedule->enabled = !$schedule->enabled;
            $schedule->save();
            $this->loadRecentSchedules();
        }
    }

    private function resetScheduleForm(): void
    {
        $this->scheduleType = 'truce';
        $this->scheduleStartsAt = null;
        $this->scheduleEndsAt = null;
        $this->scheduleMessage = '';
        $this->scheduleBlockEarth = true;
        $this->scheduleBlockSpatial = true;
        $this->scheduleBlockSpy = true;
        $this->scheduleProductionBonusPercent = 0.0;
        $this->scheduleStorageBonusPercent = 0.0;
        $this->scheduleShopBonusPercent = 0.0;
    }

    public function applyPreset(string $key): void
    {
        $year = now()->year;
        $this->scheduleType = 'truce';
        $this->scheduleBlockEarth = true;
        $this->scheduleBlockSpatial = true;
        $this->scheduleBlockSpy = true;

        switch ($key) {
            case 'noel':
                $start = \Carbon\Carbon::create($year, 12, 24, 0, 0);
                $end = \Carbon\Carbon::create($year, 12, 26, 0, 0);
                $this->scheduleMessage = 'Trêve de Noël 🎄 Joyeuses fêtes!';
                break;
            case 'nouvel_an':
                $start = \Carbon\Carbon::create($year, 12, 31, 12, 0);
                $end = \Carbon\Carbon::create($year + 1, 1, 1, 23, 59);
                $this->scheduleMessage = 'Trêve du Nouvel An 🎆 Bonne année!';
                break;
            case 'halloween':
                $start = \Carbon\Carbon::create($year, 10, 31, 0, 0);
                $end = \Carbon\Carbon::create($year, 11, 1, 0, 0);
                $this->scheduleMessage = 'Trêve Halloween 🎃';
                break;
            case 'saint_valentin':
                $start = \Carbon\Carbon::create($year, 2, 14, 0, 0);
                $end = \Carbon\Carbon::create($year, 2, 15, 0, 0);
                $this->scheduleMessage = 'Trêve de la Saint-Valentin 💖';
                break;
            case 'fete_nationale':
                // France: 14 juillet
                $start = \Carbon\Carbon::create($year, 7, 14, 0, 0);
                $end = \Carbon\Carbon::create($year, 7, 15, 0, 0);
                $this->scheduleMessage = 'Trêve de la fête nationale 🇫🇷';
                break;
            default:
                return;
        }

        $this->scheduleStartsAt = $start->format('Y-m-d\TH:i');
        $this->scheduleEndsAt = $end->format('Y-m-d\TH:i');
    }

    private function loadRecentSchedules(): void
    {
        $this->recentSchedules = ServerSchedule::orderBy('starts_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'type' => $s->type,
                    'starts_at' => optional($s->starts_at)->format('Y-m-d H:i'),
                    'ends_at' => optional($s->ends_at)->format('Y-m-d H:i'),
                    'enabled' => (bool) $s->enabled,
                    'message' => $s->message,
                    'payload' => $s->payload,
                ];
            })
            ->toArray();
    }

    private function loadRecentLogs(): void
    {
        $this->recentLogs = \App\Models\Server\ServerScheduleLog::orderBy('applied_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'applied_at' => optional($l->applied_at)->format('Y-m-d H:i'),
                    'message' => $l->message,
                    'changes' => $l->changes,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        $this->loadRecentLogs();
        return view('livewire.admin.options', [
                'recentSchedules' => $this->recentSchedules,
                'recentLogs' => $this->recentLogs,
            ])
            ->title('Options');
    }
}