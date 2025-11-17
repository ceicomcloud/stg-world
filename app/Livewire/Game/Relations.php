<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\User\UserRelation;
use App\Models\User;
use App\Services\PrivateMessageService;

#[Layout('components.layouts.game')]
class Relations extends Component
{
    public $incoming = [];
    public $outgoing = [];
    public $accepted = [];

    // Confirmation modals
    public bool $showCancelModal = false;
    public bool $showBreakModal = false;
    public ?int $selectedRelationId = null;

    public function mount()
    {
        $this->loadRelations();
    }

    public function loadRelations(): void
    {
        $userId = Auth::id();
        $this->incoming = UserRelation::where('receiver_id', $userId)
            ->where('status', UserRelation::STATUS_PENDING)
            ->with('requester')
            ->get();

        $this->outgoing = UserRelation::where('requester_id', $userId)
            ->where('status', UserRelation::STATUS_PENDING)
            ->with('receiver')
            ->get();

        $this->accepted = UserRelation::where(function ($q) use ($userId) {
                $q->where('requester_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->where('status', UserRelation::STATUS_ACCEPTED)
            ->with(['requester', 'receiver'])
            ->orderBy('accepted_at', 'desc')
            ->get();
    }

    public function accept(int $relationId, PrivateMessageService $pm): void
    {
        $relation = UserRelation::find($relationId);
        if (!$relation || $relation->receiver_id !== Auth::id()) {
            return;
        }

        $relation->update([
            'status' => UserRelation::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'rejected_at' => null,
        ]);

        // Notify requester
        $requester = User::find($relation->requester_id);
        $receiver = User::find($relation->receiver_id);
        if ($requester && $receiver) {
            $pm->createSystemNotification(
                $requester,
                'Pacte accepté',
                "🤝 Votre demande de pacte a été acceptée par <strong>{$receiver->name}</strong>."
            );
        }

        $this->dispatch('toast:success', [
            'title' => 'Succès',
            'text' => 'Pacte accepté.'
        ]);
        $this->loadRelations();
    }

    public function reject(int $relationId): void
    {
        $relation = UserRelation::find($relationId);
        if (!$relation || $relation->receiver_id !== Auth::id()) {
            return;
        }

        $relation->update([
            'status' => UserRelation::STATUS_REJECTED,
            'rejected_at' => now(),
        ]);

        $this->dispatch('toast:success', [
            'title' => 'Succès',
            'text' => 'Pacte refusé.'
        ]);
        $this->loadRelations();
    }

    public function cancel(int $relationId): void
    {
        $relation = UserRelation::find($relationId);
        if (!$relation || $relation->requester_id !== Auth::id()) {
            return;
        }

        $relation->delete();
        $this->dispatch('toast:success', [
            'title' => 'Succès',
            'text' => 'Demande de pacte annulée.'
        ]);
        $this->loadRelations();
    }

    public function render()
    {
        return view('livewire.game.relations');
    }

    /**
     * Ouvrir la modale d'information de profil (RankingInfo)
     */
    public function openUserProfile(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $this->dispatch('openModal', component: 'game.modal.ranking-info', arguments: [
                'title' => 'Profil de ' . $user->name,
                'userId' => $user->id,
            ]);
        }
    }

    public function break(int $relationId, PrivateMessageService $pm): void
    {
        $relation = UserRelation::find($relationId);
        $userId = Auth::id();
        if (!$relation || $relation->status !== UserRelation::STATUS_ACCEPTED) {
            return;
        }

        // Seule une des deux parties peut annuler
        if ($relation->requester_id !== $userId && $relation->receiver_id !== $userId) {
            return;
        }

        $relation->update([
            'status' => UserRelation::STATUS_REJECTED,
            'rejected_at' => now(),
        ]);

        // Notifier l'autre joueur de l'annulation
        $otherId = $relation->requester_id === $userId ? $relation->receiver_id : $relation->requester_id;
        $other = User::find($otherId);
        $actor = User::find($userId);
        if ($other && $actor) {
            $pm->createSystemNotification(
                $other,
                'Pacte annulé',
                "⚠️ Le pacte a été annulé par <strong>{$actor->name}</strong>."
            );
        }

        $this->dispatch('toast:success', [
            'title' => 'Pacte annulé',
            'text' => 'Le pacte a été annulé avec succès.'
        ]);
        $this->loadRelations();
    }

    // ------- Confirmations -------
    public function confirmCancel(int $relationId): void
    {
        $this->selectedRelationId = $relationId;
        $this->showCancelModal = true;
        $this->showBreakModal = false;
    }

    public function confirmBreak(int $relationId): void
    {
        $this->selectedRelationId = $relationId;
        $this->showBreakModal = true;
        $this->showCancelModal = false;
    }

    public function performCancel(): void
    {
        if ($this->selectedRelationId) {
            $this->cancel($this->selectedRelationId);
        }
        $this->dismissModals();
    }

    public function performBreak(): void
    {
        if ($this->selectedRelationId) {
            // Résoudre le service et appeler la méthode existante
            $pm = app(PrivateMessageService::class);
            $this->break($this->selectedRelationId, $pm);
        }
        $this->dismissModals();
    }

    public function dismissModals(): void
    {
        $this->showCancelModal = false;
        $this->showBreakModal = false;
        $this->selectedRelationId = null;
    }
}