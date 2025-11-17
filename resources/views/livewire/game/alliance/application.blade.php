<div>
    <h3>📝 Candidatures</h3>
                    
    <div class="applications-section">
        @if(isset($pendingApplications) && $pendingApplications->count() > 0)
            @foreach($pendingApplications as $application)
                <div class="application-item">
                    <div class="application-header">
                        <div class="applicant-info">
                            <div class="applicant-avatar">
                                <span class="applicant-initial">{{ substr($application->user->name, 0, 1) }}</span>
                            </div>
                            <div class="applicant-details">
                                <h4 class="applicant-name">{{ $application->user->name }}</h4>
                                <span class="application-date">Candidature du {{ $application->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                        </div>
                        <div class="application-status">
                            <span class="status-badge status-pending">En attente</span>
                        </div>
                    </div>
                    
                    @if($application->message)
                        <div class="application-message">
                            <h5>💬 Message de candidature:</h5>
                            <p>{{ $application->message }}</p>
                        </div>
                    @endif
                                                        
                    <div class="application-actions">
                        <button class="btn btn-success" 
                                wire:click="confirmAcceptApplication({{ $application->id }})">
                            ✅ Accepter
                        </button>
                        <button class="btn btn-danger" 
                                wire:click="confirmRejectApplication({{ $application->id }})">
                            ❌ Rejeter
                        </button>
                        <button class="btn btn-secondary" 
                                wire:click="viewUserProfile({{ $application->user->id }})">
                            👤 Voir le profil
                        </button>
                    </div>
                </div>
            @endforeach
            
            <!-- Pagination -->
            <div class="applications-pagination">
                {{ $pendingApplications->links() }}
            </div>
        @else
            <div class="no-applications">
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h4>Aucune candidature en attente</h4>
                    <p>Il n'y a actuellement aucune candidature à examiner.</p>
                </div>
            </div>
        @endif
    </div>

    <x-input.modal-confirmation
        wire:model="showAcceptAppModal"
        wire:key="alliance-modal-accept-app"
        title="Accepter la candidature"
        message="Accepter cette candidature ?"
        icon="fas fa-check-circle text-success"
        confirmText="Accepter"
        cancelText="Annuler"
        onConfirm="performAcceptApplication"
        onCancel="dismissModals"
    />

    <x-input.modal-confirmation
        wire:model="showRejectAppModal"
        wire:key="alliance-modal-reject-app"
        title="Rejeter la candidature"
        message="Rejeter cette candidature ?"
        icon="fas fa-times-circle text-danger"
        confirmText="Rejeter"
        cancelText="Annuler"
        onConfirm="performRejectApplication"
        onCancel="dismissModals"
    />
</div>
