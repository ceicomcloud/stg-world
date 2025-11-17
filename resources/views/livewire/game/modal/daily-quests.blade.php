<div class="daily-quests-modal">
    @if(empty($quests))
        <div class="empty-state">
            <i class="fas fa-ghost"></i>
            <span>Aucune quête pour aujourd'hui.</span>
        </div>
    @else
        <div class="quests-list">
            @foreach($quests as $quest)
                @php
                    $progress = (int)($quest['progress'] ?? 0);
                    $target = (int)($quest['target'] ?? 1);
                    $percent = min(100, (int) floor(($progress / max(1,$target)) * 100));
                    $claimed = !empty($quest['claimed_at']);
                    $complete = $progress >= $target;
                @endphp
                <div class="quest-card">
                    <div class="quest-header">
                        <div class="quest-title">{{ $quest['title'] ?? 'Quête' }}</div>
                        @if($claimed)
                            <span class="badge badge-claimed"><i class="fas fa-check-circle"></i> Réclamée</span>
                        @elseif($complete)
                            <span class="badge badge-ready"><i class="fas fa-gift"></i> Prête</span>
                        @else
                            <span class="badge badge-progress"><i class="fas fa-hourglass-half"></i> En cours</span>
                        @endif
                    </div>
                    <div class="quest-description">{{ $quest['description'] ?? '' }}</div>

                    <div class="quest-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="progress-text">{{ $progress }} / {{ $target }}</div>
                    </div>

                    @if(isset($quest['reward']) && $quest['reward']['type'] === 'resource')
                        <div class="quest-reward">
                            <i class="fas fa-cubes"></i>
                            <span>Récompense: {{ number_format($quest['reward']['amount']) }} {{ ucfirst($quest['reward']['resource']) }}</span>
                        </div>
                    @endif

                    <div class="quest-actions">
                        <button class="btn btn-primary" wire:click="claimReward('{{ $quest['key'] }}')" @disabled(!$complete || $claimed)>
                            <i class="fas fa-gift"></i> Réclamer
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>