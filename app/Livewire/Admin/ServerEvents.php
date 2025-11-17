<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\EventService;
use App\Services\PrivateMessageService;
use Illuminate\Support\Carbon;

#[Layout('components.layouts.admin')]
class ServerEvents extends Component
{
    public ?array $activeEvent = null;

    // Formulaire de création d'événement
    public string $type = 'attaque';
    public string $reward_type = 'resource'; // resource | gold
    public int $base_reward = 0;
    public float $points_multiplier = 0.0;
    public ?string $end_at = null; // datetime string (Y-m-d H:i)

    public array $leaders = [];

    public function mount(EventService $events)
    {
        $this->activeEvent = $events->getActiveEvent();
        $this->loadLeaders($events);
    }

    private function loadLeaders(EventService $events): void
    {
        $this->leaders = [];
        if ($this->activeEvent) {
            $users = $events->getTopUsersByActiveEvent(10, 0);
            $rank = 0;
            foreach ($users as $user) {
                $rank++;
                $this->leaders[] = [
                    'rank' => $rank,
                    'name' => $user->name,
                    'alliance' => $user->alliance->name ?? null,
                    'score' => $user->userStatEvent?->{$events->mapTypeToColumn($this->activeEvent['type'])} ?? 0,
                ];
            }
        }
    }

    public function startEvent(EventService $events)
    {
        $this->validate([
            'type' => 'required|in:attaque,exploration,extraction,pillage,construction',
            'reward_type' => 'required|in:resource,gold',
            'base_reward' => 'nullable|integer|min:0',
            'points_multiplier' => 'nullable|numeric|min:0',
            'end_at' => 'nullable|date_format:Y-m-d H:i',
        ]);

        $config = [
            'type' => $this->type,
            'reward_type' => $this->reward_type,
            'base_reward' => (int) $this->base_reward,
            'points_multiplier' => (float) $this->points_multiplier,
            'end_at' => $this->end_at ? Carbon::createFromFormat('Y-m-d H:i', $this->end_at) : null,
        ];

        $events->startEvent($config);

        $this->activeEvent = $events->getActiveEvent();
        $this->loadLeaders($events);

        $this->dispatch('admin:toast:success', ['message' => 'Événement démarré avec succès']);
    }

    public function stopEvent(EventService $events)
    {
        if (!$this->activeEvent) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucun événement actif']);
            return;
        }

        $pm = app(PrivateMessageService::class);
        $events->finalizeEvent($pm);

        $this->activeEvent = $events->getActiveEvent();
        $this->leaders = [];

        $this->dispatch('admin:toast:success', ['message' => 'Événement arrêté et récompenses distribuées']);
    }

    public function render()
    {
        return view('livewire.admin.server-events')
            ->title('Événements serveur');
    }
}