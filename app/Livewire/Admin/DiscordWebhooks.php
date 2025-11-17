<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

#[Layout('components.layouts.admin')]
class DiscordWebhooks extends Component
{
    public string $channel = 'annonces';
    public string $title = '';
    public string $content = '';
    public ?string $url = null;
    public ?string $color = '#5865F2'; // Discord blurple par défaut
    public bool $mentionHere = false;

    public ?string $statusMessage = null;
    public ?string $statusType = null; // success|error

    // Webhooks fournis
    protected array $webhooks = [
        'annonces' => 'https://discord.com/api/webhooks/1439505577515417691/vZ1kPkAFe01nl8QBc2FOClFBaYI-DAcmaWasTJ5xTCiA3i1U-W-gzmgaW5jXtB6rLT2V',
        'informations' => 'https://discord.com/api/webhooks/1439509172214562868/SAs9nrJqNf860fkEc1tcnXR64ozeglNGSCUmf8lyMm90Th_nKwN7jdjJTiOIpi-VTNIZ',
    ];

    protected function rules(): array
    {
        return [
            'channel' => 'required|in:annonces,informations',
            'title' => 'required|string|max:256',
            'content' => 'required|string|max:4000',
            'url' => 'nullable|string|max:1024',
            'color' => 'nullable|string|regex:/^#?[0-9a-fA-F]{6}$/',
            'mentionHere' => 'boolean',
        ];
    }

    public function send(): void
    {
        $this->validate();

        $webhook = $this->webhooks[$this->channel] ?? null;
        if (!$webhook) {
            $this->statusMessage = 'Webhook introuvable pour le canal sélectionné.';
            $this->statusType = 'error';
            return;
        }

        $payload = [];
        if ($this->mentionHere) {
            $payload['content'] = '@here';
            $payload['allowed_mentions'] = ['parse' => ['everyone']]; // autorise @here/@everyone
        }

        $embed = [
            'title' => $this->title,
            'description' => $this->content,
        ];
        if ($this->url) {
            $embed['url'] = $this->url;
        }
        if ($this->color) {
            $embed['color'] = $this->hexToIntColor($this->color);
        }

        $payload['embeds'] = [ $embed ];

        try {
            $response = Http::asJson()->post($webhook, $payload);
            if ($response->successful()) {
                $this->statusMessage = 'Message envoyé sur Discord.';
                $this->statusType = 'success';
                // Optionnel: reset partiel
                // $this->title = '';
                // $this->content = '';
            } else {
                $this->statusMessage = 'Échec de l’envoi (' . $response->status() . ').';
                $this->statusType = 'error';
            }
        } catch (\Throwable $e) {
            $this->statusMessage = 'Erreur lors de l’envoi: ' . $e->getMessage();
            $this->statusType = 'error';
        }
    }

    private function hexToIntColor(?string $hex): int
    {
        if (!$hex) return 0x5865F2; // défaut
        $hex = ltrim($hex, '#');
        return (int) hexdec($hex);
    }

    public function render()
    {
        return view('livewire.admin.discord-webhooks')->title('Discord Webhooks');
    }
}