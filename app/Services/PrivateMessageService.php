<?php

namespace App\Services;

use App\Models\Messaging\PrivateConversation;
use App\Models\Messaging\PrivateMessage;
use App\Models\User;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\Planet;
use Carbon\Carbon;

class PrivateMessageService
{
    /**
     * Create a system message for mission events
     */
    public function createSystemMessage(User $user, string $type, string $title, string $message): PrivateConversation
    {
        // Create or find existing system conversation for this type
        $conversation = PrivateConversation::where('created_by', $user->id)
            ->where('type', $type)
            ->where('title', $title)
            ->first();

        if (!$conversation) {
            $conversation = PrivateConversation::create([
                'title' => $title,
                'type' => $type,
                'created_by' => $user->id,
                'last_message_at' => Carbon::now(),
                'is_active' => true
            ]);

            // Add user as participant
            $conversation->addParticipant($user);
        }

        // Create the message
        PrivateMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => null, // System message
            'message' => $message,
            'is_read' => false,
            'is_system_message' => true
        ]);

        $conversation->updateLastMessageTime();

        return $conversation;
    }

    /**
     * Create colonization mission messages
     */
    public function createColonizationMessage(PlanetMission $mission, array $result): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        if ($result['success']) {
            $title = "Rapports de Colonisation";
            $message = "<div class='system-message-content'>";
            $message .= "<p>🌍 <strong>Colonisation Réussie</strong></p>";
            $message .= "<p>📍 <strong>Coordonnées:</strong> {$coordinates}</p>";
            $message .= "<p>🪐 <strong>Planète:</strong> {$result['message']}</p>";
            $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
            $message .= "</div>";
        } else {
            $title = "Rapports de Colonisation";
            $message = "<div class='system-message-content'>";
            $message .= "<p>❌ <strong>Colonisation Échouée</strong></p>";
            $message .= "<p>📍 <strong>Coordonnées:</strong> {$coordinates}</p>";
            $message .= "<p>⚠️ <strong>Raison:</strong> {$result['message']}</p>";
            $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
            $message .= "</div>";
        }

        $this->createSystemMessage($user, 'colonize', $title, $message);
    }

    /**
     * Create attack mission messages
     */
    public function createAttackMessage(PlanetMission $mission, array $result): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        $title = "Rapports d'Attaque";
        $message = "<div class='system-message-content'>";
        $message .= "<p>⚔️ <strong>Rapport d'Attaque</strong></p>";
        $message .= "<p>🎯 <strong>Coordonnées cible:</strong> {$coordinates}</p>";
        $message .= "<p>📊 <strong>Résultat:</strong> {$result['message']}</p>";
        $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'attack', $title, $message);
    }

    /**
     * Create spy mission messages
     */
    public function createSpyMessage(PlanetMission $mission, array $result): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        $title = "Rapports d'Espionnage";
        $message = "<div class='system-message-content'>";
        $message .= "<p>🕵️ <strong>Rapport d'Espionnage</strong></p>";
        $message .= "<p>🎯 <strong>Coordonnées cible:</strong> {$coordinates}</p>";
        
        // Afficher le message de résultat
        $message .= "<p>📋 <strong>Résultat:</strong> {$result['message']}</p>";
        
        // Afficher les données d'espionnage si disponibles
        if (isset($result['data']) && !empty($result['data'])) {
            // Informations de la planète
            if (isset($result['data']['planet'])) {
                $planet = $result['data']['planet'];
                $message .= "<div class='spy-section planet-info-section'>";
                $message .= "<h4>🪐 Informations de la planète</h4>";
                $message .= "<div class='planet-info-grid'>";
                $message .= "<div class='planet-info-item'>";
                $message .= "<div class='planet-info-label'>Nom:</div>";
                $message .= "<div class='planet-info-value'>{$planet['name']}</div>";
                $message .= "</div>";
                $message .= "<div class='planet-info-item'>";
                $message .= "<div class='planet-info-label'>Propriétaire:</div>";
                $message .= "<div class='planet-info-value'>{$planet['owner']}</div>";
                $message .= "</div>";
                $message .= "</div>";
                $message .= "</div>";
            }
            
            // Ressources
            if (isset($result['data']['resources'])) {
                $resources = $result['data']['resources'];
                $message .= "<div class='spy-section resources-section'>";
                $message .= "<h4>💎 Ressources</h4>";
                $message .= "<div class='spy-resources-grid'>";
                foreach ($resources as $name => $data) {
                    $displayName = $data['display_name'] ?? ucfirst($name);
                    $amount = $data['amount'] ?? $data; // Compatibilité avec l'ancien format
                    $message .= "<div class='spy-resource-item'>";
                    $message .= "<div class='spy-resource-name'><strong>" . $displayName . "</strong></div>";
                    $message .= "<div class='spy-resource-amount'>" . number_format($amount) . "</div>";
                    $message .= "</div>";
                }
                $message .= "</div>";
                $message .= "</div>";
            }
            
            // Bâtiments
            if (isset($result['data']['buildings'])) {
                $buildings = $result['data']['buildings'];
                $message .= "<div class='spy-section buildings-section'>";
                $message .= "<h4>🏗️ Bâtiments</h4>";
                if (empty($buildings)) {
                    $message .= "<p class='spy-empty-notice'>Aucun bâtiment détecté.</p>";
                } else {
                    $message .= "<div class='spy-grid'>";
                    foreach ($buildings as $name => $level) {
                        $message .= "<div class='spy-grid-item'>";
                        $message .= "<div class='spy-item-name'><strong>" . ucfirst($name) . "</strong></div>";
                        $message .= "<div class='spy-item-value'>Niveau " . $level . "</div>";
                        $message .= "</div>";
                    }
                    $message .= "</div>";
                }
                $message .= "</div>";
            }
            
            // Défenses
            if (isset($result['data']['defenses'])) {
                $defenses = $result['data']['defenses'];
                $message .= "<div class='spy-section defenses-section'>";
                $message .= "<h4>🛡️ Défenses</h4>";
                if (empty($defenses)) {
                    $message .= "<p class='spy-empty-notice'>Aucune défense détectée.</p>";
                } else {
                    $message .= "<div class='spy-grid'>";
                    foreach ($defenses as $name => $quantity) {
                        $message .= "<div class='spy-grid-item'>";
                        $message .= "<div class='spy-item-name'><strong>" . ucfirst($name) . "</strong></div>";
                        $message .= "<div class='spy-item-value'>" . number_format($quantity) . "</div>";
                        $message .= "</div>";
                    }
                    $message .= "</div>";
                }
                $message .= "</div>";
            }
            
            // Vaisseaux
            if (isset($result['data']['ships'])) {
                $ships = $result['data']['ships'];
                $message .= "<div class='spy-section ships-section'>";
                $message .= "<h4>🚀 Vaisseaux</h4>";
                if (empty($ships)) {
                    $message .= "<p class='spy-empty-notice'>Aucun vaisseau détecté.</p>";
                } else {
                    $message .= "<div class='spy-grid'>";
                    foreach ($ships as $name => $quantity) {
                        $message .= "<div class='spy-grid-item'>";
                        $message .= "<div class='spy-item-name'><strong>" . ucfirst($name) . "</strong></div>";
                        $message .= "<div class='spy-item-value'>" . number_format($quantity) . "</div>";
                        $message .= "</div>";
                    }
                    $message .= "</div>";
                }
                $message .= "</div>";
            }
            
            // Technologies
            if (isset($result['data']['technologies'])) {
                $technologies = $result['data']['technologies'];
                $message .= "<div class='spy-section technologies-section'>";
                $message .= "<h4>🔬 Technologies</h4>";
                if (empty($technologies)) {
                    $message .= "<p class='spy-empty-notice'>Aucune technologie détectée.</p>";
                } else {
                    $message .= "<div class='spy-grid'>";
                    foreach ($technologies as $name => $level) {
                        $message .= "<div class='spy-grid-item'>";
                        $message .= "<div class='spy-item-name'><strong>" . ucfirst($name) . "</strong></div>";
                        $message .= "<div class='spy-item-value'>Niveau " . $level . "</div>";
                        $message .= "</div>";
                    }
                    $message .= "</div>";
                }
                $message .= "</div>";
            }
            
            // Risque de détection
            if (isset($result['data']['detection_risk'])) {
                $message .= "<div class='spy-section detection-risk-section'>";
                $message .= "<h4>⚠️ Risque de détection</h4>";
                $message .= "<p class='detection-risk-value'>Risque que votre flotte soit détectée: <strong>{$result['data']['detection_risk']}</strong></p>";
                $message .= "</div>";
            }
        }
        
        $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'spy', $title, $message);
    }

    /**
     * Create transport mission messages
     */
    public function createTransportMessage(PlanetMission $mission, array $result): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        // Récupérer tous les template resources pour les afficher par leur nom
        $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
        
        $title = "Rapports de Transport";
        $message = "<div class='system-message-content'>";
        $message .= "<p>🚀 <strong>Rapport de Transport</strong></p>";
        $message .= "<p>📍 <strong>Coordonnées destination:</strong> {$coordinates}</p>";
        $message .= "<p>📊 <strong>Résultat:</strong> {$result['message']}</p>";
        
        if (isset($mission->resources) && is_array($mission->resources)) {
            $message .= "<p>📦 <strong>Ressources transportées:</strong></p>";
            $message .= "<ul>";
            foreach ($mission->resources as $resourceId => $amount) {
                if ($amount > 0) {
                    // Récupérer le nom de la ressource à partir de son ID
                    $resourceName = isset($templateResources[$resourceId]) ? $templateResources[$resourceId]->name : "Ressource #{$resourceId}";
                    $message .= "<li>" . ucfirst($resourceName) . ": " . number_format($amount) . "</li>";
                }
            }
            $message .= "</ul>";
        }
        
        $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'send', $title, $message);
    }

    /**
     * Create return mission messages
     */
    public function createReturnMessage(PlanetMission $mission): void
    {
        $user = $mission->user;
        $fromPlanet = $mission->fromPlanet;
        
        $title = "Rapports de Retour";
        $message = "<div class='system-message-content'>";
        $message .= "<p>🏠 <strong>Flotte de Retour</strong></p>";
        $message .= "<p>🪐 <strong>Planète d'origine:</strong> {$fromPlanet->name}</p>";
        $message .= "<p>📍 <strong>Coordonnées:</strong> {$fromPlanet->templatePlanet->galaxy}:{$fromPlanet->templatePlanet->system}:{$fromPlanet->templatePlanet->position}</p>";
        $message .= "<p>🎯 <strong>Mission:</strong> " . $this->getMissionType($mission->mission_type) . "</p>";

        // Afficher les vaisseaux retournés (si présents)
        if (isset($mission->ships) && !empty($mission->ships)) {
            $message .= "<div class='return-section ships-section'>";
            $message .= "<h4>🚀 Vaisseaux retournés</h4>";
            $message .= "<ul>";
            foreach ($mission->ships as $shipKey => $shipData) {
                // Déterminer quantité et nom selon le format
                $quantity = is_array($shipData) ? ((int)($shipData['quantity'] ?? 0)) : (int) $shipData;
                if ($quantity <= 0) { continue; }

                $name = null;
                if (is_array($shipData) && isset($shipData['name'])) {
                    $name = $shipData['name'];
                } else {
                    if (is_numeric($shipKey)) {
                        $tpl = \App\Models\Template\TemplateBuild::find((int) $shipKey);
                        $name = $tpl ? ($tpl->display_name ?? $tpl->name) : null;
                    } else {
                        // Clé non numérique: probablement le nom
                        $name = (string) $shipKey;
                    }
                }

                $displayName = $name ? ucfirst($name) : 'Vaisseau';
                $message .= "<li>{$displayName} : " . number_format($quantity) . "</li>";
            }
            $message .= "</ul>";
            $message .= "</div>";
        }

        // Afficher les ressources retournées (si présentes)
        if (isset($mission->resources) && is_array($mission->resources) && !empty($mission->resources)) {
            $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
            $message .= "<div class='return-section resources-section'>";
            $message .= "<h4>📦 Ressources retournées</h4>";
            $message .= "<ul>";
            foreach ($mission->resources as $resourceId => $amount) {
                $amount = (int) $amount;
                if ($amount <= 0) { continue; }
                $res = $templateResources[$resourceId] ?? null;
                $resName = $res ? ($res->display_name ?? $res->name) : ("Ressource #" . $resourceId);
                $message .= "<li>" . ucfirst($resName) . ": " . number_format($amount) . "</li>";
            }
            $message .= "</ul>";
            $message .= "</div>";
        }

        $message .= "<p>📅 <strong>Date de retour:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'return', $title, $message);
    }

    /**
     * Create mission departure message
     */
    public function createMissionDepartureMessage(PlanetMission $mission): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        $title = "Rapports de Mission";

        $message = "<div class='system-message-content'>";
        $message .= "<p>🚀 <strong>Départ de Mission</strong></p>";
        $message .= "<p>🎯 <strong>Type:</strong> " . $this->getMissionType($mission->mission_type) . "</p>";
        $message .= "<p>📍 <strong>Destination:</strong> {$coordinates}</p>";
        $message .= "<p>🪐 <strong>Planète d'origine:</strong> {$mission->fromPlanet->name}</p>";
        $message .= "<p>🕐 <strong>Heure de départ:</strong> " . $mission->departure_time->format('d/m/Y H:i:s') . "</p>";
        $message .= "<p>🕒 <strong>Heure d'arrivée prévue:</strong> " . $mission->arrival_time->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'system', $title, $message);
    }

    /**
     * Create general system notification
     */
    public function createSystemNotification(User $user, string $title, string $message): void
    {
        $this->createSystemMessage($user, 'system', $title, $message);
    }

    /**
     * Get mission type in French
     */
    private function getMissionType(string $missionType): string
    {
        return match($missionType) {
            'colonize' => 'Colonisation',
            'attack' => 'Attaque',
            'spy' => 'Espionnage',
            'transport' => 'Transport',
            'send' => 'Transport',
            'return' => 'Retour',
            'basement' => 'Mission Sous-sol',
            'extract' => 'Extraction',
            'explore' => 'Exploration',
            default => ucfirst($missionType)
        };
    }

    /**
     * Create basement mission message
     */
    public function createBasementMessage(PlanetMission $mission, array $result): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        $title = "Rapports de Mission transfert";
        $message = "<div class='system-message-content'>";
        $message .= "<p>🏗️ <strong>Rapport de Mission transfert</strong></p>";
        $message .= "<p>📍 <strong>Coordonnées destination:</strong> {$coordinates}</p>";
        $message .= "<p>📊 <strong>Résultat:</strong> {$result['message']}</p>";
        
        // Afficher les unités transférées
        if (isset($result['transferred_units']) && !empty($result['transferred_units'])) {
            $message .= "<p>👥 <strong>Unités transférées:</strong></p>";
            $message .= "<ul>";
            foreach ($result['transferred_units'] as $unitId => $unitData) {
                $unitName = $unitData['name'] ?? 'Unité inconnue';
                $quantity = $unitData['quantity'] ?? 0;
                $message .= "<li>" . ucfirst($unitName) . ": " . number_format($quantity) . "</li>";
            }
            $message .= "</ul>";
        }
        
        // Afficher les vaisseaux transférés
        if (isset($result['transferred_ships']) && !empty($result['transferred_ships'])) {
            $message .= "<p>🚀 <strong>Vaisseaux transférés:</strong></p>";
            $message .= "<ul>";
            foreach ($result['transferred_ships'] as $shipId => $shipData) {
                $shipName = $shipData['name'] ?? 'Vaisseau inconnu';
                $quantity = $shipData['quantity'] ?? 0;
                $message .= "<li>" . ucfirst($shipName) . ": " . number_format($quantity) . "</li>";
            }
            $message .= "</ul>";
        }
        
        $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'basement', $title, $message);
    }

    /**
     * Create extract mission message
     */
    public function createExtractMessage(PlanetMission $mission, array $result): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        // Récupérer tous les template resources pour les afficher par leur nom
        $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
        
        $title = "Rapports d'Extraction";
        $message = "<div class='system-message-content'>";
        $message .= "<p>⛏️ <strong>Rapport d'Extraction</strong></p>";
        $message .= "<p>📍 <strong>Coordonnées destination:</strong> {$coordinates}</p>";
        $message .= "<p>📊 <strong>Résultat:</strong> {$result['message']}</p>";
        
        if (isset($result['extracted_resources']) && is_array($result['extracted_resources'])) {
            $message .= "<p>💎 <strong>Ressources extraites:</strong></p>";
            $message .= "<ul>";
            foreach ($result['extracted_resources'] as $resourceId => $amount) {
                if ($amount > 0) {
                    // Récupérer le nom de la ressource à partir de son ID
                    $resourceName = isset($templateResources[$resourceId]) ? $templateResources[$resourceId]->name : "Ressource #{$resourceId}";
                    $message .= "<li>" . ucfirst($resourceName) . ": " . number_format($amount) . "</li>";
                }
            }
            $message .= "</ul>";
        }
        
        $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'extract', $title, $message);
    }

    /**
     * Create explore mission message
     */
    public function createExploreMessage(PlanetMission $mission, array $result): void
    {
        $user = $mission->user;
        $coordinates = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        
        $title = "Rapports d'Exploration";
        $message = "<div class='system-message-content'>";
        $message .= "<p>🧭 <strong>Rapport d'Exploration</strong></p>";
        $message .= "<p>📍 <strong>Coordonnées destination:</strong> {$coordinates}</p>";
        $message .= "<p>📊 <strong>Résultat:</strong> {$result['message']}</p>";

        if (isset($result['awarded_items']) && is_array($result['awarded_items']) && !empty($result['awarded_items'])) {
            $message .= "<p>🎁 <strong>Récompenses:</strong></p><ul>";
            foreach ($result['awarded_items'] as $item) {
                $name = $item['name'] ?? 'Objet inconnu';
                $quantity = $item['quantity'] ?? 1;
                $message .= "<li>" . ucfirst($name) . ": " . number_format($quantity) . "</li>";
            }
            $message .= "</ul>";
        }
        
        $message .= "<p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>";
        $message .= "</div>";

        $this->createSystemMessage($user, 'explore', $title, $message);
    }

    /**
     * Create alliance broadcast message
     */
    public function createAllianceBroadcast(User $sender, string $title, string $message): PrivateConversation
    {
        // Vérifier que l'utilisateur fait partie d'une alliance
        if (!$sender->alliance_id) {
            throw new \InvalidArgumentException('L\'utilisateur doit faire partie d\'une alliance pour envoyer un message collectif.');
        }

        // Récupérer tous les membres de l'alliance
        $allianceMembers = User::where('alliance_id', $sender->alliance_id)->get();

        if ($allianceMembers->isEmpty()) {
            throw new \InvalidArgumentException('Aucun membre trouvé dans l\'alliance.');
        }

        // Créer la conversation d'alliance
        $conversation = PrivateConversation::create([
            'title' => $title,
            'type' => 'alliance',
            'created_by' => $sender->id,
            'last_message_at' => Carbon::now(),
            'is_active' => true
        ]);

        // Ajouter tous les membres de l'alliance comme participants
        foreach ($allianceMembers as $member) {
            $conversation->addParticipant($member);
        }

        // Créer le message initial
        PrivateMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'message' => $message,
            'is_read' => false,
            'is_system_message' => false
        ]);

        $conversation->updateLastMessageTime();

        return $conversation;
    }
}