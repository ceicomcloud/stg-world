<?php

namespace App\Livewire\Game\Alliance;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Traits\LogsUserActions;
use App\Models\User;
use App\Models\Planet\PlanetResource;
use Illuminate\Validation\Rule;

#[Layout('livewire.layouts.alliance-layout')]
class Overview extends Component
{
    use WithFileUploads, LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public $editMode = false;
    public $editName = '';
    public $editTag = '';
    public $editExternalDescription = '';
    public $editInternalDescription = '';
    public $editMaxMembers = 50;
    public $editOpenRecruitment = true;
    public $logo = null;

    public $showTransferModal = false;
    public $selectedNewLeaderId = null;

    public bool $showLeaveModal = false;
    public bool $showDeleteModal = false;

    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'overview');
    }

    public function toggleEditMode()
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('edit_alliance_info')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de modifier les informations de l\'alliance.'
            ]);
            return;
        }

        $this->editMode = !$this->editMode;
        
        if ($this->editMode) {
            $this->editName = $this->alliance->name;
            $this->editTag = $this->alliance->tag;
            $this->editExternalDescription = $this->alliance->external_description;
            $this->editInternalDescription = $this->alliance->internal_description;
            $this->editMaxMembers = $this->alliance->max_members;
            $this->editOpenRecruitment = $this->alliance->open_recruitment;
        }
    }

    public function saveAllianceInfo()
    {
        $this->validate([
            'editName' => ['required', 'string', 'max:50', Rule::unique('alliances', 'name')->ignore($this->alliance->id)],
            'editTag' => ['required', 'string', 'max:10', Rule::unique('alliances', 'tag')->ignore($this->alliance->id)],
            'editExternalDescription' => ['nullable','string','max:10000', function($attribute, $value, $fail) {
                if ($value !== null) {
                    $text = trim(strip_tags($value));
                    if ($text === '') {
                        $fail('La description externe ne peut pas être vide.');
                    }
                }
            }],
            'editInternalDescription' => ['nullable','string','max:10000', function($attribute, $value, $fail) {
                if ($value !== null) {
                    $text = trim(strip_tags($value));
                    if ($text === '') {
                        $fail('La description interne ne peut pas être vide.');
                    }
                }
            }],
            'editMaxMembers' => 'required|integer|min:1|max:100',
            'logo' => 'nullable|image|max:2048'
        ]);

        $updateData = [
            'name' => $this->editName,
            'tag' => $this->editTag,
            // Nettoyage des data URI pour éviter des payloads énormes
            'external_description' => $this->sanitizeDescription($this->editExternalDescription),
            'internal_description' => $this->sanitizeDescription($this->editInternalDescription),
            'max_members' => $this->editMaxMembers,
            'open_recruitment' => $this->editOpenRecruitment
        ];

        if ($this->logo) {
            // Enregistrer directement dans le dossier public pour éviter les soucis de visibilité
            $directory = public_path('alliance-logos');
            File::ensureDirectoryExists($directory);

            $extension = $this->logo->getClientOriginalExtension();
            $filename = uniqid('logo_') . ($extension ? '.' . $extension : '');
            $destination = $directory . DIRECTORY_SEPARATOR . $filename;

            File::copy($this->logo->getRealPath(), $destination);
            // Stocker un chemin relatif web-accessible
            $updateData['logo'] = 'alliance-logos/' . $filename;
        }

        $this->alliance->update($updateData);
        $this->alliance->refresh();
        
        $this->editMode = false;
        $this->logo = null;
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Informations de l\'alliance mises à jour!'
        ]);
    }

    private function sanitizeDescription(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $clean = $html;
        // Retirer les balises <img> avec src commençant par data:
        $clean = preg_replace('/<img[^>]+src\s*=\s*["\']\s*data:[^"\']+["\'][^>]*>/i', '', $clean);
        // Retirer toute occurrence de data: dans les attributs style/background etc.
        $clean = preg_replace('/data:[^\"\']+/i', '', $clean);
        // Optionnel: limiter à 10k caractères pour éviter dépassements
        if (mb_strlen($clean) > 10000) {
            $clean = mb_substr($clean, 0, 10000);
        }

        return $clean;
    }

    public function confirmLeave(): void
    {
        $this->showLeaveModal = true;
    }

    public function performLeave(): void
    {
        $this->showLeaveModal = false;
        $this->leaveAlliance();
    }

    // Supprimer l'alliance
    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function performDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteAlliance();
    }

    public function leaveAlliance()
    {
        $user = Auth::user();
        
        $allianceName = $this->alliance->name;
        
        if ($user->leaveAlliance()) {
            $this->logAction(
                'alliance_left',
                'alliance',
                'Départ de l\'alliance',
                ['alliance_name' => $allianceName]
            );
            
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Vous avez quitté l\'alliance.'
            ]);
        } else {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible de quitter l\'alliance.'
            ]);
        }
    }

    public function deleteAlliance()
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->isLeader()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'êtes pas le leader de l\'alliance.'
            ]);
            return;
        }

        // Vérifier que l'alliance n'a qu'un seul membre (le leader)
        if ($this->alliance->members()->count() > 1) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible de supprimer l\'alliance. Il y a encore des membres.'
            ]);
            return;
        }

        try {
            // Supprimer l'alliance
            $this->alliance->delete();
                        
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'L\'alliance a été supprimée avec succès.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Erreur lors de la suppression de l\'alliance.'
            ]);
        }
    }

    public function showTransferLeadershipModal()
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->isLeader()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'êtes pas le leader de l\'alliance.'
            ]);
            return;
        }

        $this->showTransferModal = true;
        $this->selectedNewLeaderId = null;
    }

    public function closeTransferModal()
    {
        $this->showTransferModal = false;
        $this->selectedNewLeaderId = null;
    }

    public function transferLeadership()
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->isLeader()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'êtes pas le leader de l\'alliance.'
            ]);
            return;
        }

        $this->validate([
            'selectedNewLeaderId' => 'required|exists:users,id'
        ]);

        // Vérifier que le nouveau leader est bien membre de l'alliance
        $newLeaderMember = $this->alliance->members()->where('user_id', $this->selectedNewLeaderId)->first();
        if (!$newLeaderMember) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'L\'utilisateur sélectionné n\'est pas membre de l\'alliance.'
            ]);
            return;
        }

        try {
            // Mettre à jour le leader dans l'alliance
            $this->alliance->update([
                'leader_id' => $this->selectedNewLeaderId
            ]);

            // Donner le rang de leader au nouveau leader (niveau le plus élevé)
            $leaderRank = $this->alliance->ranks()->orderBy('level', 'desc')->first();
            if ($leaderRank) {
                $newLeaderMember->update([
                    'rank_id' => $leaderRank->id
                ]);
            }

            // Rétrograder l'ancien leader au rang de membre normal (niveau le plus bas)
            $memberRank = $this->alliance->ranks()->orderBy('level', 'asc')->first();
            if ($memberRank) {
                $this->userAllianceMember->update([
                    'rank_id' => $memberRank->id
                ]);
            }

            $this->closeTransferModal();
            
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Le leadership a été transféré avec succès.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Erreur lors du transfert de leadership.'
            ]);
        }
    }

    public function dismissModals(): void
    {
        $this->showLeaveModal = false;
        $this->showDeleteModal = false;
    }    

    public function render()
    {
        return view('livewire.game.alliance.overview');
    }
}
