<div>
    <h3>👥 Membres de l'Alliance ({{ $members->total() }})</h3>
                    
    <div class="members-list">
        @foreach($members as $member)
            <div class="member-item">
                <div class="member-info">
                    <span class="member-name">{{ $member->user->name }}</span>
                    @if($member->rank)
                        <span class="member-rank">{{ $member->rank->name }}</span>
                    @endif
                    @if($member->user_id === $alliance->leader_id)
                        <span style="color: gold;">👑</span>
                    @endif
                </div>
                <div class="member-stats">
                    <span>Rejoint: {{ $member->joined_at->format('d/m/Y') }}</span>
                    <span>Contribution: {{ number_format($member->contributed_deuterium) }} D</span>
                </div>
            </div>
        @endforeach
    </div>
    
    {{ $members->links() }}
</div>
