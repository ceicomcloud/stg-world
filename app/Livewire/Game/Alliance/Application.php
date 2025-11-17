<?php

namespace App\Livewire\Game\Alliance;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use App\Models\User;
use App\Models\Planet\PlanetResource;
use App\Models\Alliance\AllianceRank;
use App\Models\Alliance\AllianceApplication;

#[Layout('livewire.layouts.alliance-layout')]
class Application extends Component
{
    use LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public $selectedApplicationId = null;
    public $showAcceptAppModal = false;
    public $showRejectAppModal = false;

    public $applicationsPage = 1;
    public $perPage = 10;

    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'applications');
    }

    public function confirmAcceptApplication(int $applicationId): void
    {
        $this->selectedApplicationId = $applicationId;
        $this->showAcceptAppModal = true;
    }

    public function performAcceptApplication(): void
    {
        $applicationId = $this->selectedApplicationId;
        $this->dismissModals();
        if ($applicationId) {
            $this->acceptApplication($applicationId);
        }
    }

    // Rejeter une candidature (via modale)
    public function confirmRejectApplication(int $applicationId): void
    {
        $this->selectedApplicationId = $applicationId;
        $this->showRejectAppModal = true;
    }

    public function performRejectApplication(): void
    {
        $applicationId = $this->selectedApplicationId;
        $this->dismissModals();
        if ($applicationId) {
            $this->rejectApplication($applicationId);
        }
    }

    public function acceptApplication(int $applicationId)
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_applications')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les candidatures.'
            ]);
            return;
        }

        $application = AllianceApplication::where('id', $applicationId)
            ->where('alliance_id', $this->alliance->id)
            ->first();

        if (!$application || !$application->isPending()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Candidature introuvable ou déjà traitée.'
            ]);
            return;
        }

        $reviewer = Auth::user();
        $accepted = $application->accept($reviewer);

        if ($accepted) {
            $this->logAction(
                'application_accepted',
                'alliance',
                'Candidature acceptée',
                [
                    'applicant_id' => $application->user_id,
                    'alliance' => $this->alliance->name
                ]
            );

            $this->alliance->refresh();

            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'La candidature a été acceptée.'
            ]);
        } else {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible d\'accepter la candidature.'
            ]);
        }
    }

    public function rejectApplication(int $applicationId)
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_applications')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les candidatures.'
            ]);
            return;
        }

        $application = AllianceApplication::where('id', $applicationId)
            ->where('alliance_id', $this->alliance->id)
            ->first();

        if (!$application || !$application->isPending()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Candidature introuvable ou déjà traitée.'
            ]);
            return;
        }

        $reviewer = Auth::user();
        $rejected = $application->reject($reviewer);

        if ($rejected) {
            $this->logAction(
                'application_rejected',
                'alliance',
                'Candidature rejetée',
                [
                    'applicant_id' => $application->user_id,
                    'alliance' => $this->alliance->name
                ]
            );

            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'La candidature a été rejetée.'
            ]);
        } else {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible de rejeter la candidature.'
            ]);
        }
    }

    public function viewUserProfile(int $userId)
    {
        $user = User::find($userId);
        if ($user) {
            $userName = $user->name;
            $this->dispatch('openModal', component: 'game.modal.ranking-info', arguments: [
                'title' => 'Profil de ' . $userName,
                'userId' => $userId,
            ]);
        }
    }

    public function dismissModals(): void
    {
        $this->showAcceptAppModal = false;
        $this->showRejectAppModal = false;

        $this->selectedApplicationId = null;
    }    

    public function render()
    {
        $data = [
            'availablePermissions' => AllianceRank::PERMISSIONS,
            'rankLevels' => AllianceRank::LEVELS
        ];
        
        $data['pendingApplications'] = $this->alliance->pendingApplications()->paginate($this->perPage, ['*'], 'applications', $this->applicationsPage);

        return view('livewire.game.alliance.application', $data);
    }
}
