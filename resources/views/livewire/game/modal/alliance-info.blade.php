<div>
    <!-- Avatar/Logo de l'alliance -->
    <div class="modal-alliance-avatar">
        @if(!empty($allianceData['logo_url']))
            <img src="{{ $allianceData['logo_url'] }}" alt="Logo Alliance">
        @else
            <i class="fas fa-shield-alt"></i>
        @endif
    </div>

    <!-- Nom de l'alliance -->
    <div class="modal-alliance-name">
        <h2>{{ $allianceData['name'] ?? 'Alliance inconnue' }} [{{ $allianceData['tag'] ?? 'N/A' }}]</h2>
        <div class="alliance-creation-date">
            <i class="fas fa-calendar-alt"></i>
            Créée {{ $allianceData['created_at']->diffForHumans() ?? 'N/A' }}
        </div>
    </div>

    <!-- Points totaux -->
    <div class="modal-total-points">
        <i class="fas fa-trophy"></i>
        <span class="points-text">Points totaux:</span>
        <span class="points-value">{{ number_format($allianceData['total_points'] ?? 0) }}</span>
    </div>

    <!-- Informations générales -->
    <div class="modal-section">
        <h3 class="section-title">
            <i class="fas fa-info-circle"></i>
            Informations Générales
        </h3>
        <div class="alliance-info-grid">
            <div class="info-item">
                <i class="fas fa-user"></i>
                <span class="info-value">{{ $allianceData['leader']['name'] ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <i class="fas fa-users"></i>
                <span class="info-value">{{ $allianceData['members_count'] ?? 0 }}/{{ $allianceData['max_members'] ?? 0 }}</span>
            </div>
            <div class="info-item">
                <i class="fas fa-chart-line"></i>
                <span class="info-value">{{ number_format($allianceData['average_points'] ?? 0) }}</span>
            </div>
            <div class="info-item">
                <i class="fas fa-{{ $this->getRecruitmentIcon() }}"></i>
                <span class="info-value recruitment-{{ $this->getRecruitmentColor() }}">{{ $this->getRecruitmentStatus() }}</span>
            </div>
        </div>
    </div>

    <!-- Description interne (visible seulement pour les membres) -->
    @if($isUserMember && !empty($allianceData['description']))
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-file-text"></i>
                Description Interne
            </h3>
            <div class="alliance-description">
                {!! $allianceData['description'] !!}
            </div>
        </div>
    @endif

    <!-- Description externe -->
    @if(!empty($allianceData['external_description']))
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-globe"></i>
                Description Publique
            </h3>
            <div class="alliance-description">
                {!! $allianceData['external_description'] !!}
            </div>
        </div>
    @endif

    <!-- Top membres -->
    @if(count($allianceData['top_members'] ?? []) > 0)
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-star"></i>
                Top Membres
            </h3>
            <div class="top-members-list">
                @foreach($allianceData['top_members'] as $index => $member)
                    <div class="top-member-item">
                        <div class="member-position">
                            @if($index === 0)
                                <i class="fas fa-crown" style="color: #ffd700;"></i>
                            @elseif($index === 1)
                                <i class="fas fa-medal" style="color: #c0c0c0;"></i>
                            @elseif($index === 2)
                                <i class="fas fa-award" style="color: #cd7f32;"></i>
                            @else
                                <span class="position-number">{{ $index + 1 }}</span>
                            @endif
                        </div>
                        <div class="member-info">
                            <div class="member-name">{{ $member['name'] }}</div>
                            <div class="member-rank">{{ $member['rank_name'] }}</div>
                        </div>
                        <div class="member-points">
                            {{ number_format($member['points']) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Statistiques avancées -->
    <div class="modal-section">
        <h3 class="section-title">
            <i class="fas fa-chart-bar"></i>
            Statistiques
        </h3>
        <div class="alliance-stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Taux de remplissage</div>
                    <div class="stat-value">{{ round(($allianceData['members_count'] ?? 0) / max($allianceData['max_members'] ?? 1, 1) * 100, 1) }}%</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Rangs définis</div>
                    <div class="stat-value">{{ $allianceData['ranks_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>