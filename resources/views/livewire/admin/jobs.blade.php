<div class="admin-jobs">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-tasks"></i> Gestion des Jobs</h1>
        <div class="admin-page-actions">
            <!-- Onglets principaux -->
            <button class="admin-tab-button {{ $activeTab === 'available' ? 'active' : '' }}" wire:click="setActiveTab('available')">
                <i class="fas fa-list"></i> Jobs Disponibles
            </button>
            <button class="admin-tab-button {{ $activeTab === 'running' ? 'active' : '' }}" wire:click="setActiveTab('running')">
                <i class="fas fa-play"></i> Jobs en Cours
            </button>
            <button class="admin-tab-button {{ $activeTab === 'failed' ? 'active' : '' }}" wire:click="setActiveTab('failed')">
                <i class="fas fa-exclamation-triangle"></i> Jobs Échoués
            </button>
            <button class="admin-tab-button {{ $activeTab === 'batches' ? 'active' : '' }}" wire:click="setActiveTab('batches')">
                <i class="fas fa-layer-group"></i> Lots de Jobs
            </button>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Statut des Ticks -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Statut des Ticks</h2>
                <p class="admin-card-subtitle">Dernières exécutions et métriques</p>
            </div>
            <div class="admin-card-body">
                <div class="admin-grid-3">
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-stream"></i> Files (queues:tick)</h3>
                        <p><strong>Dernière exécution:</strong> {{ $tickMetrics['queues']['last_run_at'] ?? '—' }}</p>
                        <p><strong>Durée:</strong> {{ $tickMetrics['queues']['duration_ms'] }} ms</p>
                        <p><strong>Éléments traités:</strong> {{ $tickMetrics['queues']['processed_count'] }}</p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runQueuesTick" wire:loading.attr="disabled" wire:target="runQueuesTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runQueuesTick">Exécuter</span>
                            <span wire:loading wire:target="runQueuesTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-rocket"></i> Missions (missions:tick)</h3>
                        <p><strong>Dernière exécution:</strong> {{ $tickMetrics['missions']['last_run_at'] ?? '—' }}</p>
                        <p><strong>Durée:</strong> {{ $tickMetrics['missions']['duration_ms'] }} ms</p>
                        <p><strong>Missions traitées:</strong> {{ $tickMetrics['missions']['processed_count'] }}</p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runMissionsTick" wire:loading.attr="disabled" wire:target="runMissionsTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runMissionsTick">Exécuter</span>
                            <span wire:loading wire:target="runMissionsTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-industry"></i> Production (production:tick)</h3>
                        <p><strong>Dernière exécution:</strong> {{ $tickMetrics['production']['last_run_at'] ?? '—' }}</p>
                        <p><strong>Durée:</strong> {{ $tickMetrics['production']['duration_ms'] }} ms</p>
                        <p><strong>Utilisateurs traités:</strong> {{ $tickMetrics['production']['processed_count'] }}</p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runProductionTick" wire:loading.attr="disabled" wire:target="runProductionTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runProductionTick">Exécuter</span>
                            <span wire:loading wire:target="runProductionTick">Exécution...</span>
                        </button>
                    </div>
                </div>
                <div class="admin-grid-3 mt-3">
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-robot"></i> Bot (bot:tick)</h3>
                        <p><strong>Dernière exécution:</strong> {{ $tickMetrics['bot']['last_run_at'] ?? '—' }}</p>
                        <p><strong>Durée:</strong> {{ $tickMetrics['bot']['duration_ms'] }} ms</p>
                        <p><strong>Planètes traitées:</strong> {{ $tickMetrics['bot']['processed_count'] }}</p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runBotTick" wire:loading.attr="disabled" wire:target="runBotTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runBotTick">Exécuter</span>
                            <span wire:loading wire:target="runBotTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-award"></i> Badges (badges:tick)</h3>
                        <p><strong>Dernière exécution:</strong> {{ $tickMetrics['badges']['last_run_at'] ?? '—' }}</p>
                        <p><strong>Durée:</strong> {{ $tickMetrics['badges']['duration_ms'] }} ms</p>
                        <p><strong>Badges attribués:</strong> {{ $tickMetrics['badges']['processed_count'] }}</p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runBadgesTick" wire:loading.attr="disabled" wire:target="runBadgesTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runBadgesTick">Exécuter</span>
                            <span wire:loading wire:target="runBadgesTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-chart-line"></i> Ranking (ranking:tick)</h3>
                        <p><strong>Dernière exécution:</strong> {{ $tickMetrics['ranking']['last_run_at'] ?? '—' }}</p>
                        <p><strong>Durée:</strong> {{ $tickMetrics['ranking']['duration_ms'] }} ms</p>
                        <p><strong>Utilisateurs traités:</strong> {{ $tickMetrics['ranking']['processed_count'] }}</p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runRankingTick" wire:loading.attr="disabled" wire:target="runRankingTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runRankingTick">Exécuter</span>
                            <span wire:loading wire:target="runRankingTick">Exécution...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des exécutions BotTick -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title"><i class="fas fa-robot"></i> Historique BotTick</h2>
                <p class="admin-card-subtitle">Détails des exécutions quotidiennes du bot</p>
            </div>
            <div class="admin-card-body">
                @if(isset($botRuns) && $botRuns->count() > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Statut</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Planètes traitées</th>
                                <th>Ressources générées</th>
                                <th>Ressources dépensées</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($botRuns as $run)
                                <tr>
                                    <td>{{ $run->id }}</td>
                                    <td>
                                        <span class="admin-badge {{ $run->status === 'completed' ? 'success' : ($run->status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($run->status) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($run->started_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ optional($run->finished_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ number_format($run->planets_processed) }}</td>
                                    <td>
                                        @php $gen = json_decode($run->resources_generated_json ?? '{}', true) ?? []; @endphp
                                        @if(count($gen) > 0)
                                            @foreach($gen as $name => $amount)
                                                <div><strong>{{ $name }}</strong>: {{ (int) $amount }}</div>
                                            @endforeach
                                        @else
                                            <span class="admin-text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $spent = json_decode($run->resources_spent_json ?? '{}', true) ?? []; @endphp
                                        @if(count($spent) > 0)
                                            @foreach($spent as $name => $amount)
                                                <div><strong>{{ $name }}</strong>: {{ (int) $amount }}</div>
                                            @endforeach
                                        @else
                                            <span class="admin-text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($run->details_path)
                                            <a class="admin-link" href="{{ asset('storage/'.$run->details_path) }}" target="_blank">JSON</a>
                                        @else
                                            <span class="admin-text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="admin-pagination-container">
                        {{ $botRuns->links() }}
                    </div>
                @else
                    <div class="admin-empty-state">
                        <div class="admin-empty-state-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>Aucune exécution enregistrée</h3>
                        <p>Les détails s’afficheront après le prochain bot:tick.</p>
                    </div>
                @endif
            </div>
        </div>
        <!-- Barre de recherche -->
        <div class="admin-search-bar">
            <div class="admin-search-input-wrapper">
                <i class="fas fa-search admin-search-icon"></i>
                <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                @if($search)
                    <button class="admin-search-clear" wire:click="$set('search', '')">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- Contenu des onglets -->
        <div class="admin-tab-content">
            <!-- Onglet Jobs Disponibles -->
            @if($activeTab === 'available')
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Jobs Disponibles</h2>
                        <p class="admin-card-subtitle">Liste des jobs que vous pouvez lancer manuellement</p>
                    </div>
                    <div class="admin-card-body">
                        @if(count($this->availableJobs) > 0)
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->availableJobs as $job)
                                            <tr>
                                                <td>{{ $job['name'] }}</td>
                                                <td>{{ $job['description'] }}</td>
                                                <td>
                                                    <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="selectJob('{{ $job['name'] }}')">
                                                        <i class="fas fa-play"></i> Lancer
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3>Aucun job trouvé</h3>
                                <p>Aucun job ne correspond à votre recherche.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Formulaire de lancement de job -->
                @if($selectedJob)
                    <div class="admin-card mt-4">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">Lancer {{ $selectedJob }}</h2>
                            <p class="admin-card-subtitle">Configurez les paramètres du job</p>
                        </div>
                        <div class="admin-card-body">
                            <form wire:submit.prevent="dispatchJob">
                                @foreach($this->availableJobs as $job)
                                    @if($job['name'] === $selectedJob && count($job['params']) > 0)
                                        @foreach($job['params'] as $paramName => $paramDescription)
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">{{ $paramDescription }}</label>
                                                @if($paramName === 'checkAllUsers')
                                                    <div class="admin-toggle-switch">
                                                        <input type="checkbox" id="{{ $paramName }}" wire:model="jobParams.{{ $paramName }}">
                                                        <label for="{{ $paramName }}"></label>
                                                    </div>
                                                @elseif(str_contains($paramName, 'userId'))
                                                    <input type="number" class="admin-form-control" wire:model="jobParams.{{ $paramName }}" placeholder="Laisser vide pour tous les utilisateurs">
                                                @else
                                                    <input type="text" class="admin-form-control" wire:model="jobParams.{{ $paramName }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach

                                <div class="admin-form-actions">
                                    <button type="button" class="admin-btn admin-btn-secondary" wire:click="$set('selectedJob', null)">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                    <button type="submit" class="admin-btn admin-btn-primary">
                                        <i class="fas fa-play"></i> Lancer le Job
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Onglet Jobs en Cours -->
            @if($activeTab === 'running')
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Jobs en Cours</h2>
                        <p class="admin-card-subtitle">Liste des jobs actuellement en file d'attente ou en cours d'exécution</p>
                    </div>
                    <div class="admin-card-body">
                        @if(count($runningJobs) > 0)
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Job</th>
                                            <th>Queue</th>
                                            <th>Tentatives</th>
                                            <th>Créé le</th>
                                            <th>Disponible le</th>
                                            <th>Réservé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($runningJobs as $job)
                                            <tr>
                                                <td>{{ $job->id }}</td>
                                                <td>{{ $job->job_name }}</td>
                                                <td>{{ $job->queue }}</td>
                                                <td>{{ $job->attempts }}</td>
                                                <td>{{ $job->created_at }}</td>
                                                <td>{{ $job->available_at }}</td>
                                                <td>{{ $job->reserved_at ?? 'Non réservé' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="admin-pagination-container">
                                {{ $runningJobs->links() }}
                            </div>
                        @else
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3>Aucun job en cours</h3>
                                <p>Il n'y a actuellement aucun job en file d'attente ou en cours d'exécution.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Onglet Jobs Échoués -->
            @if($activeTab === 'failed')
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Jobs Échoués</h2>
                        <p class="admin-card-subtitle">Liste des jobs qui ont échoué lors de leur exécution</p>
                        <div class="admin-card-actions">
                            <button class="admin-btn admin-btn-danger" wire:click="flushFailedJobs" wire:confirm="Êtes-vous sûr de vouloir supprimer tous les jobs échoués ?">
                                <i class="fas fa-trash"></i> Vider tous les jobs échoués
                            </button>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        @if(count($failedJobs) > 0)
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>UUID</th>
                                            <th>Connexion</th>
                                            <th>Queue</th>
                                            <th>Échoué le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($failedJobs as $job)
                                            <tr>
                                                <td>{{ $job->id }}</td>
                                                <td>{{ $job->uuid }}</td>
                                                <td>{{ $job->connection }}</td>
                                                <td>{{ $job->queue }}</td>
                                                <td>{{ $job->failed_at }}</td>
                                                <td>
                                                    <div class="admin-btn-group">
                                                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="retryFailedJob({{ $job->id }})">
                                                            <i class="fas fa-redo"></i> Réessayer
                                                        </button>
                                                        <button class="admin-btn admin-btn-sm admin-btn-danger" wire:click="deleteFailedJob({{ $job->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce job ?">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="admin-pagination-container">
                                {{ $failedJobs->links() }}
                            </div>
                        @else
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3>Aucun job échoué</h3>
                                <p>Tous les jobs se sont exécutés avec succès.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Onglet Lots de Jobs -->
            @if($activeTab === 'batches')
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Lots de Jobs</h2>
                        <p class="admin-card-subtitle">Liste des lots de jobs (batches) et leur état</p>
                    </div>
                    <div class="admin-card-body">
                        @if(count($jobBatches) > 0)
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Total</th>
                                            <th>En attente</th>
                                            <th>Échoués</th>
                                            <th>Créé le</th>
                                            <th>Terminé le</th>
                                            <th>État</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jobBatches as $batch)
                                            <tr>
                                                <td>{{ $batch->id }}</td>
                                                <td>{{ $batch->name }}</td>
                                                <td>{{ $batch->total_jobs }}</td>
                                                <td>{{ $batch->pending_jobs }}</td>
                                                <td>{{ $batch->failed_jobs }}</td>
                                                <td>{{ $batch->created_at }}</td>
                                                <td>{{ $batch->finished_at ?? 'En cours' }}</td>
                                                <td>
                                                    @if($batch->cancelled_at)
                                                        <span class="admin-badge danger">Annulé</span>
                                                    @elseif($batch->finished_at)
                                                        @if($batch->failed_jobs > 0)
                                                            <span class="admin-badge warning">Terminé avec erreurs</span>
                                                        @else
                                                            <span class="admin-badge success">Terminé</span>
                                                        @endif
                                                    @else
                                                        <span class="admin-badge primary">En cours</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="admin-pagination-container">
                                {{ $jobBatches->links() }}
                            </div>
                        @else
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <h3>Aucun lot de jobs</h3>
                                <p>Aucun lot de jobs n'a été créé.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>