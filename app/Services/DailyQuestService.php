<?php

namespace App\Services;

use App\Models\User\UserDailyQuest;
use App\Models\User;
use App\Models\Server\ServerConfig;
use Carbon\Carbon;

class DailyQuestService
{
    public function getOrCreateTodayQuests($user): array
    {
        $date = Carbon::today()->toDateString();
        $record = UserDailyQuest::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        if (!$record) {
            $record = UserDailyQuest::create([
                'user_id' => $user->id,
                'date' => $date,
                'quests' => $this->generateDefaultQuests(),
            ]);
        }

        return $record->quests ?? [];
    }

    public function incrementProgress($user, string $questKey, int $amount = 1): void
    {
        $date = Carbon::today()->toDateString();
        $record = UserDailyQuest::where('user_id', $user->id)
            ->where('date', $date)
            ->first();
        if (!$record) { return; }

        $quests = collect($record->quests ?? [])
            ->map(function ($q) use ($questKey, $amount) {
                if (($q['key'] ?? null) === $questKey) {
                    $q['progress'] = (int)($q['progress'] ?? 0) + $amount;
                }
                return $q;
            })
            ->values()
            ->toArray();

        $record->update(['quests' => $quests]);
    }

    public function markClaimed($user, string $questKey): void
    {
        $date = Carbon::today()->toDateString();
        $record = UserDailyQuest::where('user_id', $user->id)
            ->where('date', $date)
            ->first();
        if (!$record) { return; }

        $quests = collect($record->quests ?? [])
            ->map(function ($q) use ($questKey) {
                if (($q['key'] ?? null) === $questKey) {
                    if (empty($q['claimed_at'])) {
                        $q['claimed_at'] = Carbon::now()->toDateTimeString();
                    }
                }
                return $q;
            })
            ->values()
            ->toArray();

        $record->update(['quests' => $quests]);
    }

    /**
     * Incrémente la progression de toutes les quêtes dont la clé commence par un préfixe.
     */
    public function incrementProgressByPrefix($user, string $prefix, int $amount = 1): void
    {
        $date = Carbon::today()->toDateString();
        $record = UserDailyQuest::where('user_id', $user->id)
            ->where('date', $date)
            ->first();
        if (!$record) { return; }

        $quests = collect($record->quests ?? [])
            ->map(function ($q) use ($prefix, $amount) {
                $key = $q['key'] ?? '';
                if ($key !== '' && str_starts_with($key, $prefix)) {
                    $q['progress'] = (int)($q['progress'] ?? 0) + $amount;
                }
                return $q;
            })
            ->values()
            ->toArray();

        $record->update(['quests' => $quests]);
    }

    private function generateDefaultQuests(): array
    {
        $min = (int) (ServerConfig::get('daily_quests_count_min', 4));
        $max = (int) (ServerConfig::get('daily_quests_count_max', 6));
        if ($min < 1) { $min = 1; }
        if ($max < $min) { $max = $min; }
        $count = random_int($min, $max);

        $factories = [
            function () {
                return $this->makeQuest(
                    'start_technology',
                    'Lancer une technologie',
                    "Démarrer la recherche d'une technologie.",
                    1,
                    $this->chooseRewardByDifficulty(1),
                    'technology'
                );
            },
            function () {
                return $this->makeQuest(
                    'start_building',
                    'Lancer un bâtiment',
                    "Commencer la construction d'un bâtiment.",
                    1,
                    $this->chooseRewardByDifficulty(1),
                    'building'
                );
            },
            function () {
                $type = $this->pick(['unit', 'defense', 'ship']);
                $titleMap = [
                    'unit' => "Lancer une production d'unité",
                    'defense' => "Lancer une production de défense",
                    'ship' => "Lancer une production de vaisseau",
                ];
                return $this->makeQuest(
                    'produce_' . $type,
                    $titleMap[$type] ?? 'Lancer une production',
                    "Produire au moins un élément dans le chantier.",
                    1,
                    $this->chooseRewardByDifficulty(1),
                    'production'
                );
            },
            function () {
                $missionMap = ['attaque' => 'attack', 'espionnage' => 'spy', 'exploration' => 'explore', 'extraction' => 'extract'];
                $fr = array_keys($missionMap);
                $frChoice = $this->pick($fr);
                $type = $missionMap[$frChoice];
                return $this->makeQuest(
                    'mission_' . $type,
                    'Lancer une mission d\'' . $frChoice,
                    "Envoyer une flotte en mission.",
                    1,
                    $this->chooseRewardByDifficulty(2),
                    'mission'
                );
            },
            function () {
                return $this->makeQuest(
                    'obtain_badge',
                    'Obtenir un badge',
                    "Remporter un badge unique.",
                    1,
                    $this->chooseRewardByDifficulty(2),
                    'badge'
                );
            },
            function () {
                $n = $this->pick([50, 100, 200]);
                $difficulty = ($n >= 200) ? 3 : (($n >= 100) ? 2 : 1);
                return $this->makeQuest(
                    'galaxy_browse_' . $n,
                    'Parcourir ' . $n . ' systèmes',
                    'Naviguer dans la galaxie pour découvrir des systèmes.',
                    $n,
                    $this->chooseRewardByDifficulty($difficulty),
                    'galaxy'
                );
            },
            function () {
                $pts = $this->pick([20000, 50000, 200000]);
                $difficulty = ($pts >= 200000) ? 3 : (($pts >= 50000) ? 2 : 1);
                return $this->makeQuest(
                    'gain_points_' . $pts,
                    'Gagner ' . number_format($pts, 0, '.', ' ') . ' points',
                    'Augmenter votre score global.',
                    $pts,
                    $this->chooseRewardByDifficulty($difficulty),
                    'points'
                );
            },
            function () {
                $msgs = $this->pick([5, 10, 20]);
                return $this->makeQuest(
                    'chat_send_messages',
                    'Envoyer ' . $msgs . ' messages dans le chat',
                    'Participer aux discussions.',
                    $msgs,
                    $this->chooseRewardByDifficulty(1),
                    'chat'
                );
            },
            function () {
                $acts = $this->pick([1, 3, 5]);
                return $this->makeQuest(
                    'bunker_add_resources',
                    'Ajouter des ressources au bunker',
                    'Effectuer ' . $acts . " dépôt(s) au bunker.",
                    $acts,
                    $this->chooseRewardByDifficulty(2),
                    'bunker'
                );
            },
        ];

        shuffle($factories);
        $selected = array_slice($factories, 0, $count);
        $quests = [];
        foreach ($selected as $factory) {
            $quests[] = $factory();
        }
        return $quests;
    }

    private function makeQuest(string $key, string $title, string $description, int $target, array $reward, string $category): array
    {
        return [
            'key' => $key,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'target' => $target,
            'progress' => 0,
            'reward' => $reward,
            'claimed_at' => null,
        ];
    }

    private function pick(array $choices)
    {
        return $choices[array_rand($choices)];
    }

    private function chooseRewardByDifficulty(int $difficulty): array
    {
        $resource = $this->pick(['metal', 'crystal', 'deuterium']);
        switch ($difficulty) {
            case 3:
                $amount = $this->pick([50000, 75000, 100000]);
                break;
            case 2:
                $amount = $this->pick([15000, 20000, 25000]);
                break;
            default:
                $amount = $this->pick([5000, 7000, 10000]);
                break;
        }
        return ['type' => 'resource', 'resource' => $resource, 'amount' => $amount];
    }
}