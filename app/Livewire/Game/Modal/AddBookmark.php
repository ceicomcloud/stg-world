<?php

namespace App\Livewire\Game\Modal;

use LivewireUI\Modal\ModalComponent;
use App\Models\User\UserBookmark;
use App\Models\Template\TemplatePlanet;
use App\Models\Server\ServerConfig;

class AddBookmark extends ModalComponent
{
    public $label = '';
    public $galaxy;
    public $system;
    public $position;
    public $planetId = null;
    public $templateId = null;

    public function mount($galaxy = null, $system = null, $position = null, $planetId = null, $templateId = null)
    {
        $this->galaxy = $galaxy;
        $this->system = $system;
        $this->position = $position;
        $this->planetId = $planetId;
        $this->templateId = $templateId;

        if (!$this->templateId && $this->galaxy && $this->system && $this->position) {
            $this->templateId = TemplatePlanet::where('galaxy', $this->galaxy)
                ->where('system', $this->system)
                ->where('position', $this->position)
                ->value('id');
        }
    }

    private function getBookmarkLimit(): int
    {
        $user = auth()->user();
        return $user->vip_active ? ServerConfig::getMaxBookmarksVip() : ServerConfig::getMaxBookmarksNormal();
    }

    private function getBookmarkCount(): int
    {
        return auth()->user()->bookmarks()->active()->count();
    }

    public function save(): void
    {
        $label = trim($this->label);
        if ($label === '') {
            $this->dispatch('toast:error', [
                'title' => 'Nom requis',
                'text' => 'Veuillez saisir un nom pour le bookmark.'
            ]);
            return;
        }

        $limit = $this->getBookmarkLimit();
        $count = $this->getBookmarkCount();
        if ($count >= $limit) {
            $this->dispatch('toast:error', [
                'title' => 'Limite atteinte',
                'text' => "Limite de $limit bookmarks atteinte. Supprimez-en pour ajouter."
            ]);
            return;
        }

        $exists = UserBookmark::where('user_id', auth()->id())
            ->where('label', $label)
            ->active()
            ->exists();
        if ($exists) {
            $this->dispatch('toast:error', [
                'title' => 'Nom déjà utilisé',
                'text' => 'Choisissez un autre nom pour ce bookmark.'
            ]);
            return;
        }

        if (!$this->galaxy || !$this->system || !$this->position) {
            $this->dispatch('toast:error', [
                'title' => 'Coordonnées manquantes',
                'text' => 'Coordonnées invalides pour créer le bookmark.'
            ]);
            return;
        }

        UserBookmark::create([
            'user_id' => auth()->id(),
            'planet_id' => $this->planetId,
            'label' => $label,
            'galaxy' => (int) $this->galaxy,
            'system' => (int) $this->system,
            'position' => (int) $this->position,
            'is_active' => true,
        ]);

        $this->dispatch('toast:success', [
            'title' => 'Bookmark ajouté',
            'text' => 'Le bookmark a été enregistré depuis la Galaxie.'
        ]);

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.game.modal.add-bookmark');
    }
}