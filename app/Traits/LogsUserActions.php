<?php

namespace App\Traits;

use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait LogsUserActions
{
    /**
     * Instance du service de logs
     */
    protected function logService(): LogService
    {
        return app(LogService::class);
    }

    /**
     * Log une action pour l'utilisateur connecté
     */
    protected function logAction(
        string $actionType,
        string $actionCategory,
        string $description,
        array $metadata = [],
        ?int $planetId = null,
        ?int $targetUserId = null,
        string $severity = 'info'
    ): void {
        if (Auth::check()) {
            $request = request();
            $this->logService()->logForCurrentUser(
                $actionType,
                $actionCategory,
                $description,
                $metadata,
                $planetId,
                $targetUserId,
                $severity,
                $request
            );
        }
    }

    /**
     * Log de connexion
     */
    protected function logLogin(): void
    {
        if (Auth::check()) {
            $this->logService()->logLogin(Auth::id(), request());
        }
    }

    /**
     * Log de déconnexion
     */
    protected function logLogout(): void
    {
        if (Auth::check()) {
            $this->logService()->logLogout(Auth::id(), request());
        }
    }

    /**
     * Log de dépense de ressources
     */
    protected function logResourceSpend(
        array $resources,
        string $reason,
        ?int $planetId = null
    ): void {
        if (Auth::check()) {
            $this->logService()->logResourceSpend(
                Auth::id(),
                $resources,
                $reason,
                $planetId,
                request()
            );
        }
    }

    /**
     * Log d'achat de bâtiment
     */
    protected function logBuildingPurchase(
        string $buildingName,
        int $buildingId,
        array $cost,
        ?int $planetId = null
    ): void {
        if (Auth::check()) {
            $this->logService()->logBuildingPurchase(
                Auth::id(),
                $buildingName,
                $buildingId,
                $cost,
                $planetId,
                request()
            );
        }
    }

    /**
     * Log d'envoi de message privé
     */
    protected function logPrivateMessageSent(
        int $recipientId,
        string $subject,
        int $messageId
    ): void {
        if (Auth::check()) {
            $this->logService()->logPrivateMessageSent(
                Auth::id(),
                $recipientId,
                $subject,
                $messageId,
                request()
            );
        }
    }

    /**
     * Log d'adhésion à une alliance
     */
    protected function logAllianceJoin(
        string $allianceName,
        int $allianceId
    ): void {
        if (Auth::check()) {
            $this->logService()->logAllianceJoin(
                Auth::id(),
                $allianceName,
                $allianceId,
                request()
            );
        }
    }

    /**
     * Log d'attaque lancée
     */
    protected function logAttackLaunched(
        int $targetUserId,
        int $attackerPlanetId,
        int $defenderPlanetId,
        string $attackType,
        array $units
    ): void {
        if (Auth::check()) {
            $this->logService()->logAttackLaunched(
                Auth::id(),
                $targetUserId,
                $attackerPlanetId,
                $defenderPlanetId,
                $attackType,
                $units,
                request()
            );
        }
    }

    /**
     * Log de recherche de technologie
     */
    protected function logTechnologyResearch(
        string $technologyName,
        int $technologyId,
        int $level,
        array $cost
    ): void {
        if (Auth::check()) {
            $this->logService()->logTechnologyResearch(
                Auth::id(),
                $technologyName,
                $technologyId,
                $level,
                $cost,
                request()
            );
        }
    }

    /**
     * Log de création d'échange
     */
    protected function logTradeCreated(
        array $offeredResources,
        array $requestedResources,
        int $tradeId
    ): void {
        if (Auth::check()) {
            $this->logService()->logTradeCreated(
                Auth::id(),
                $offeredResources,
                $requestedResources,
                $tradeId,
                request()
            );
        }
    }

    /**
     * Log de lancement de mission
     */
    protected function logMissionLaunched(
        string $missionType,
        int $sourcePlanetId,
        ?int $targetPlanetId,
        array $ships
    ): void {
        if (Auth::check()) {
            $this->logService()->logMissionLaunched(
                Auth::id(),
                $missionType,
                $sourcePlanetId,
                $targetPlanetId,
                $ships,
                request()
            );
        }
    }

    /**
     * Log de modification des paramètres
     */
    protected function logSettingsChanged(array $changedSettings): void
    {
        if (Auth::check()) {
            $this->logService()->logSettingsChanged(
                Auth::id(),
                $changedSettings,
                request()
            );
        }
    }

    /**
     * Log de stockage de ressources dans le bunker
     */
    protected function logBunkerStore(
        int $planetId,
        int $bunkerId,
        int $resourceId,
        int $amount,
        int $bunkerAmountBefore,
        int $bunkerAmountAfter,
        int $planetAmountBefore,
        int $planetAmountAfter
    ): void {
        if (Auth::check()) {
            $this->logService()->logBunkerStore(
                Auth::id(),
                $planetId,
                $bunkerId,
                $resourceId,
                $amount,
                $bunkerAmountBefore,
                $bunkerAmountAfter,
                $planetAmountBefore,
                $planetAmountAfter,
                request()
            );
        }
    }

    /**
     * Log de récupération de ressources du bunker
     */
    protected function logBunkerRetrieve(
        int $planetId,
        int $bunkerId,
        int $resourceId,
        int $amount,
        int $bunkerAmountBefore,
        int $bunkerAmountAfter,
        int $planetAmountBefore,
        int $planetAmountAfter
    ): void {
        if (Auth::check()) {
            $this->logService()->logBunkerRetrieve(
                Auth::id(),
                $planetId,
                $bunkerId,
                $resourceId,
                $amount,
                $bunkerAmountBefore,
                $bunkerAmountAfter,
                $planetAmountBefore,
                $planetAmountAfter,
                request()
            );
        }
    }
}