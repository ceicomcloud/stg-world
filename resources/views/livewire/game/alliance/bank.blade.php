<div>
    <h3 class="bank-title">🏦 Banque de l'Alliance</h3>
                        
    <div class="bank-section">
        <div class="bank-card">
            <h4>💰 Solde Actuel</h4>
            <div class="bank-balance">
                <span class="bank-balance-value">{{ number_format($alliance->deuterium_bank) }}</span>
                <span class="bank-balance-label">Deuterium</span>
            </div>
            <div class="bank-capacity" style="margin-top: 8px; color: var(--stargate-text-secondary);">
                <span>Capacité maximale:</span>
                <span style="color: var(--stargate-accent); font-weight: 600; margin-left: 6px;">{{ number_format($alliance->getMaxDeuteriumStorage()) }}</span>
                <span style="margin-left: 4px;">Deuterium</span>
            </div>
        </div>
        
        <div class="bank-card">
            <h4>📥 Déposer du Deuterium</h4>
            <div class="bank-actions">
                <input type="number" class="bank-input" wire:model="bankDepositAmount" 
                    placeholder="Quantité" min="1">
                <button class="btn btn-primary" wire:click="depositToDeuteriumBank">
                    Déposer
                </button>
            </div>
            @error('bankDepositAmount') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
        </div>
        
        @if($userAllianceMember && $userAllianceMember->hasPermission('manage_bank'))
            <div class="bank-card">
                <h4>📤 Retirer du Deuterium</h4>
                <div class="bank-actions">
                    <input type="number" class="bank-input" wire:model="bankWithdrawAmount" 
                        placeholder="Quantité" min="1">
                    <button class="btn btn-danger" wire:click="withdrawFromDeuteriumBank">
                        Retirer
                    </button>
                </div>
                @error('bankWithdrawAmount') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
            </div>
        @endif
    </div>
</div>