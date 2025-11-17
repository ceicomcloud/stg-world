<?php

namespace App\Livewire\Game\Alliance;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Traits\LogsUserActions;
use App\Models\Planet\PlanetResource;

#[Layout('livewire.layouts.alliance-layout')]
class Bank extends Component
{
    use LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public $bankDepositAmount = 0;
    public $bankWithdrawAmount = 0;

    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'bank');
    }

    public function depositToDeuteriumBank()
    {
        $this->validate([
            'bankDepositAmount' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $planet = $user->getActualPlanet();
        $deuteriumResource = PlanetResource::where('planet_id', $planet->id)
            ->where('resource_id', 3) // Deuterium
            ->first();

        // Vérifier la capacité maximale de la banque via la technologie d'alliance "Stockage Avancé"
        $currentBank = (int) $this->alliance->deuterium_bank;
        $maxBankCapacity = (int) $this->alliance->getMaxDeuteriumStorage();
        $availableCapacity = max(0, $maxBankCapacity - $currentBank);

        if ($availableCapacity <= 0) {
            $this->dispatch('toast:error', [
                'title' => 'Banque pleine!',
                'text' => 'La capacité de la banque est atteinte (' . number_format($maxBankCapacity) . '). Améliorez "Stockage Avancé" pour augmenter la capacité.'
            ]);
            return;
        }

        if ($this->bankDepositAmount > $availableCapacity) {
            $this->dispatch('toast:error', [
                'title' => 'Dépôt trop élevé!',
                'text' => 'Capacité restante: ' . number_format($availableCapacity) . '. Réduisez le montant ou améliorez "Stockage Avancé".'
            ]);
            return;
        }

        if (!$deuteriumResource || $deuteriumResource->current_amount < $this->bankDepositAmount) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas assez de deuterium.'
            ]);
            return;
        }

        $deuteriumResource->decrement('current_amount', $this->bankDepositAmount);
        $this->alliance->addToDeuteriumBank($this->bankDepositAmount);
        $this->userAllianceMember->addContribution($this->bankDepositAmount);

        // Log alliance bank deposit
        $this->logAction(
            'bank_deposit',
            'alliance',
            'Dépôt de ' . number_format($this->bankDepositAmount) . ' deuterium dans la banque de l\'alliance',
            ['amount' => $this->bankDepositAmount, 'alliance' => $this->alliance->name]
        );

        $this->reset('bankDepositAmount');
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Deuterium déposé dans la banque!'
        ]);
    }

    public function withdrawFromDeuteriumBank()
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_bank')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer la banque.'
            ]);
            return;
        }

        $this->validate([
            'bankWithdrawAmount' => 'required|integer|min:1'
        ]);

        if (!$this->alliance->withdrawFromDeuteriumBank($this->bankWithdrawAmount)) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Pas assez de deuterium dans la banque.'
            ]);
            return;
        }

        $user = Auth::user();
        $planet = $user->getActualPlanet();
        $deuteriumResource = PlanetResource::where('planet_id', $planet->id)
            ->where('resource_id', 3) // Deuterium
            ->first();

        $deuteriumResource->increment('current_amount', $this->bankWithdrawAmount);
        
        // Log alliance bank withdrawal
        $this->logAction(
            'bank_withdrawal',
            'alliance',
            'Retrait de ' . number_format($this->bankWithdrawAmount) . ' deuterium de la banque de l\'alliance',
            ['amount' => $this->bankWithdrawAmount, 'alliance' => $this->alliance->name]
        );
        
        $this->alliance->refresh();
        $this->reset('bankWithdrawAmount');
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Deuterium retiré de la banque!'
        ]);
    }

    public function render()
    {
        return view('livewire.game.alliance.bank');
    }
}
