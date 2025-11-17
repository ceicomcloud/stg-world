<div>
    <!-- Avatar/Logo de l'alliance -->
    <div class="modal-alliance-avatar">
        <!--[if BLOCK]><![endif]--><?php if(!empty($allianceData['logo_url'])): ?>
            <img src="<?php echo e($allianceData['logo_url']); ?>" alt="Logo Alliance">
        <?php else: ?>
            <i class="fas fa-shield-alt"></i>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!-- Nom de l'alliance -->
    <div class="modal-alliance-name">
        <h2><?php echo e($allianceData['name'] ?? 'Alliance inconnue'); ?> [<?php echo e($allianceData['tag'] ?? 'N/A'); ?>]</h2>
        <div class="alliance-creation-date">
            <i class="fas fa-calendar-alt"></i>
            Créée <?php echo e($allianceData['created_at']->diffForHumans() ?? 'N/A'); ?>

        </div>
    </div>

    <!-- Points totaux -->
    <div class="modal-total-points">
        <i class="fas fa-trophy"></i>
        <span class="points-text">Points totaux:</span>
        <span class="points-value"><?php echo e(number_format($allianceData['total_points'] ?? 0)); ?></span>
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
                <span class="info-value"><?php echo e($allianceData['leader']['name'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-users"></i>
                <span class="info-value"><?php echo e($allianceData['members_count'] ?? 0); ?>/<?php echo e($allianceData['max_members'] ?? 0); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-chart-line"></i>
                <span class="info-value"><?php echo e(number_format($allianceData['average_points'] ?? 0)); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-<?php echo e($this->getRecruitmentIcon()); ?>"></i>
                <span class="info-value recruitment-<?php echo e($this->getRecruitmentColor()); ?>"><?php echo e($this->getRecruitmentStatus()); ?></span>
            </div>
        </div>
    </div>

    <!-- Description interne (visible seulement pour les membres) -->
    <!--[if BLOCK]><![endif]--><?php if($isUserMember && !empty($allianceData['description'])): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-file-text"></i>
                Description Interne
            </h3>
            <div class="alliance-description">
                <?php echo $allianceData['description']; ?>

            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Description externe -->
    <!--[if BLOCK]><![endif]--><?php if(!empty($allianceData['external_description'])): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-globe"></i>
                Description Publique
            </h3>
            <div class="alliance-description">
                <?php echo $allianceData['external_description']; ?>

            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Top membres -->
    <!--[if BLOCK]><![endif]--><?php if(count($allianceData['top_members'] ?? []) > 0): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-star"></i>
                Top Membres
            </h3>
            <div class="top-members-list">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $allianceData['top_members']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="top-member-item">
                        <div class="member-position">
                            <!--[if BLOCK]><![endif]--><?php if($index === 0): ?>
                                <i class="fas fa-crown" style="color: #ffd700;"></i>
                            <?php elseif($index === 1): ?>
                                <i class="fas fa-medal" style="color: #c0c0c0;"></i>
                            <?php elseif($index === 2): ?>
                                <i class="fas fa-award" style="color: #cd7f32;"></i>
                            <?php else: ?>
                                <span class="position-number"><?php echo e($index + 1); ?></span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="member-info">
                            <div class="member-name"><?php echo e($member['name']); ?></div>
                            <div class="member-rank"><?php echo e($member['rank_name']); ?></div>
                        </div>
                        <div class="member-points">
                            <?php echo e(number_format($member['points'])); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

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
                    <div class="stat-value"><?php echo e(round(($allianceData['members_count'] ?? 0) / max($allianceData['max_members'] ?? 1, 1) * 100, 1)); ?>%</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Rangs définis</div>
                    <div class="stat-value"><?php echo e($allianceData['ranks_count'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/alliance-info.blade.php ENDPATH**/ ?>