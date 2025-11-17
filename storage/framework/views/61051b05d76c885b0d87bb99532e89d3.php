<div>
    <h3>🔬 Technologies d'Alliance</h3>
                    
    <div class="technologies-section">
        <div class="alliance-info-card">
            <p style="color: var(--stargate-text-secondary); margin-bottom: 20px;">
                Les technologies d'alliance améliorent les capacités de votre alliance. 
                Chaque amélioration coûte du deuterium de la banque d'alliance.
            </p>
            
            <div class="bank-info" style="margin-bottom: 30px;">
                <strong>💰 Deuterium en banque: </strong>
                <span style="color: var(--stargate-accent);"><?php echo e(number_format($alliance->deuterium_bank)); ?></span>
            </div>
        </div>
        
        <div class="technologies-grid">
            <!-- Technology: Members -->
            <?php
                $membersTech = $technologies['members'] ?? null;
            ?>
            <div class="technology-card">
                <div class="technology-header">
                    <div class="technology-icon">👥</div>
                    <div class="technology-info">
                        <h4><?php echo e($membersTech ? $membersTech->getName() : 'Expansion des Membres'); ?></h4>
                        <p><?php echo e($membersTech ? $membersTech->getDescription() : 'Augmente la capacité maximale de membres de l\'alliance'); ?></p>
                    </div>
                </div>
                
                <div class="technology-stats">
                    <div class="stat-row">
                        <span>Niveau actuel:</span>
                        <span class="stat-value"><?php echo e($membersTech ? $membersTech->level : 0); ?>/15</span>
                    </div>
                    <div class="stat-row">
                        <span>Bonus actuel:</span>
                        <span class="stat-value">+<?php echo e($membersTech ? $membersTech->getBonus() : 0); ?> membres</span>
                    </div>
                    <div class="stat-row">
                        <span>Capacité totale:</span>
                        <span class="stat-value"><?php echo e($alliance->getMaxMembers()); ?> membres</span>
                    </div>
                    <?php if($membersTech && $membersTech->canUpgrade()): ?>
                        <div class="stat-row">
                            <span>Prochain niveau:</span>
                            <span class="stat-value">+<?php echo e($membersTech->getNextLevelBonus()); ?> membres</span>
                        </div>
                        <div class="stat-row">
                            <span>Coût:</span>
                            <span class="stat-value cost"><?php echo e(number_format($membersTech->getUpgradeCost())); ?> deuterium</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if($membersTech && $membersTech->canUpgrade()): ?>
                    <button class="btn btn-primary" 
                            wire:click="showTechnologyUpgrade('members')"
                            <?php echo e($alliance->deuterium_bank < $membersTech->getUpgradeCost() ? 'disabled' : ''); ?>>
                        🔬 Améliorer
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        ✅ Niveau Maximum
                    </button>
                <?php endif; ?>
            </div>
            
            <!-- Technology: Bank -->
            <?php
                $bankTech = $technologies['bank'] ?? null;
            ?>
            <div class="technology-card">
                <div class="technology-header">
                    <div class="technology-icon">🏦</div>
                    <div class="technology-info">
                        <h4><?php echo e($bankTech ? $bankTech->getName() : 'Stockage Avancé'); ?></h4>
                        <p><?php echo e($bankTech ? $bankTech->getDescription() : 'Augmente la capacité de stockage de deuterium de la banque'); ?></p>
                    </div>
                </div>
                
                <div class="technology-stats">
                    <div class="stat-row">
                        <span>Niveau actuel:</span>
                        <span class="stat-value"><?php echo e($bankTech ? $bankTech->level : 0); ?>/15</span>
                    </div>
                    <div class="stat-row">
                        <span>Bonus actuel:</span>
                        <span class="stat-value">+<?php echo e($bankTech ? number_format($bankTech->getBonus()) : 0); ?> deuterium</span>
                    </div>
                    <div class="stat-row">
                        <span>Capacité totale:</span>
                        <span class="stat-value"><?php echo e(number_format($alliance->getMaxDeuteriumStorage())); ?> deuterium</span>
                    </div>
                    <?php if($bankTech && $bankTech->canUpgrade()): ?>
                        <div class="stat-row">
                            <span>Prochain niveau:</span>
                            <span class="stat-value">+<?php echo e(number_format($bankTech->getNextLevelBonus())); ?> deuterium</span>
                        </div>
                        <div class="stat-row">
                            <span>Coût:</span>
                            <span class="stat-value cost"><?php echo e(number_format($bankTech->getUpgradeCost())); ?> deuterium</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if($bankTech && $bankTech->canUpgrade()): ?>
                    <button class="btn btn-primary" 
                            wire:click="showTechnologyUpgrade('bank')"
                            <?php echo e($alliance->deuterium_bank < $bankTech->getUpgradeCost() ? 'disabled' : ''); ?>>
                        🔬 Améliorer
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        ✅ Niveau Maximum
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($showUpgradeModal && $selectedTechnology): ?>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; z-index: 1000; animation: fadeIn 0.3s ease;">
            <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5); animation: slideIn 0.3s ease;">
                <h3 style="color: #fff; margin-bottom: 20px; text-align: center;">
                    🔬 Améliorer <?php echo e($selectedTechnology->getName()); ?>

                </h3>
                
                <div style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--stargate-text-secondary);">Niveau actuel:</span>
                        <span style="color: #fff; font-weight: 600;"><?php echo e($selectedTechnology->level); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--stargate-text-secondary);">Niveau suivant:</span>
                        <span style="color: var(--stargate-accent); font-weight: 600;"><?php echo e($selectedTechnology->level + 1); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--stargate-text-secondary);">Bonus actuel:</span>
                        <span style="color: #fff;"><?php echo e($selectedTechnology->technology_type === 'members' ? '+' . $selectedTechnology->getBonus() . ' membres' : '+' . number_format($selectedTechnology->getBonus()) . ' deuterium'); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="color: var(--stargate-text-secondary);">Nouveau bonus:</span>
                        <span style="color: var(--stargate-accent); font-weight: 600;"><?php echo e($selectedTechnology->technology_type === 'members' ? '+' . $selectedTechnology->getNextLevelBonus() . ' membres' : '+' . number_format($selectedTechnology->getNextLevelBonus()) . ' deuterium'); ?></span>
                    </div>
                    <hr style="border: none; border-top: 1px solid rgba(255, 255, 255, 0.1); margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--stargate-text-secondary);">Coût d'amélioration:</span>
                        <span style="color: #ffc107; font-weight: 700; font-size: 18px;"><?php echo e(number_format($selectedTechnology->getUpgradeCost())); ?> deuterium</span>
                    </div>
                </div>
                
                <?php if($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost()): ?>
                    <div style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <p style="color: #dc3545; margin: 0; text-align: center;">
                            ⚠️ Deuterium insuffisant en banque
                        </p>
                    </div>
                <?php else: ?>
                    <div style="background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <p style="color: #28a745; margin: 0; text-align: center;">
                            ✅ Amélioration disponible
                        </p>
                    </div>
                <?php endif; ?>
                
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button wire:click="closeUpgradeModal" 
                            style="padding: 12px 24px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; cursor: pointer; transition: all 0.3s ease; font-weight: 500;">
                        Annuler
                    </button>
                    <button wire:click="upgradeTechnology" 
                            <?php echo e($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'disabled' : ''); ?>

                            style="padding: 12px 24px; border-radius: 8px; border: none; background: <?php echo e($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'rgba(108, 117, 125, 0.5)' : 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)'); ?>; color: #fff; font-weight: 600; cursor: <?php echo e($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'not-allowed' : 'pointer'); ?>; transition: all 0.3s ease;">
                        🔬 Améliorer
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/technology.blade.php ENDPATH**/ ?>