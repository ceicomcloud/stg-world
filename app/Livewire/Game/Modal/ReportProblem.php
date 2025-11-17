<?php

namespace App\Livewire\Game\Modal;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Http;
use App\Models\Server\ServerConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ReportProblem extends ModalComponent   
{
    public $problem = '';
    public $category = 'bug';
    public $categories = [
        'bug' => 'Bug technique',
        'gameplay' => 'Problème de gameplay',
        'suggestion' => 'Suggestion',
        'other' => 'Autre'
    ];
    
    public function mount()
    {
        if (!ServerConfig::get('report_problem_enabled', false)) {
            $this->dispatch('closeModal');
        }
    }
    
    public function render()
    {
        return view('livewire.game.modal.report-problem');
    }
    
    public function submitReport()
    {
        // Validation
        $this->validate([
            'problem' => 'required|min:10|max:1000',
            'category' => 'required|in:' . implode(',', array_keys($this->categories))
        ]);
        
        // Récupérer le webhook Discord
        $webhookUrl = ServerConfig::get('report_problem_webhook_url', '');
        
        if (empty($webhookUrl)) {
            $this->dispatch('swal:error', [
                'title' => 'Signalement',
                'text' => 'Le système de signalement n\'est pas correctement configuré.'
            ]);
            return;
        }
        
        // Préparer les données pour l'embed Discord
        $user = Auth::user();
        $embed = [
            'title' => 'Signalement de problème',
            'description' => $this->problem,
            'color' => $this->getCategoryColor(),
            'fields' => [
                [
                    'name' => 'Catégorie',
                    'value' => $this->categories[$this->category],
                    'inline' => true
                ],
                [
                    'name' => 'Utilisateur',
                    'value' => $user->name,
                    'inline' => true
                ],
                [
                    'name' => 'ID Utilisateur',
                    'value' => $user->id,
                    'inline' => true
                ],
                [
                    'name' => 'Date du signalement',
                    'value' => now()->format('d/m/Y H:i:s'),
                    'inline' => true
                ],
                [
                    'name' => 'Page concernée',
                    'value' => Request::fullUrl(),
                    'inline' => true
                ],
                [
                    'name' => 'Route',
                    'value' => request()->route() ? request()->route()->getName() : 'Non disponible',
                    'inline' => true
                ],
                [
                    'name' => 'Paramètres de route',
                    'value' => request()->route() ? json_encode(request()->route()->parameters()) : 'Non disponible',
                    'inline' => false
                ]
            ],
            'footer' => [
                'text' => 'Système de signalement - StargateV3'
            ],
            'timestamp' => now()->toIso8601String()
        ];
        
        // Envoyer au webhook Discord
        try {
            Http::post($webhookUrl, [
                'embeds' => [$embed]
            ]);

            $this->dispatch('swal:success', [
                'title' => 'Signalement',
                'text' => 'Votre signalement a été envoyé avec succès.'
            ]);
            
            $this->reset('problem');
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            $this->dispatch('swal:error', [
                'title' => 'Signalement',
                'text' => 'Une erreur est survenue lors de l\'envoi du signalement.'
            ]);
        }
    }
    
    private function getCategoryColor()
    {
        return match($this->category) {
            'bug' => 15158332, // Rouge
            'gameplay' => 3447003, // Bleu
            'suggestion' => 3066993, // Vert
            default => 10181046, // Gris
        };
    }
}
