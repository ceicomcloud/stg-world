# Système de Logs Utilisateur - StargateV3

## Vue d'ensemble

Le système de logs utilisateur permet de tracer toutes les actions importantes effectuées par les joueurs dans le jeu. Il comprend :

- **Migration** : `2025_06_16_000001_create_user_logs_table.php`
- **Modèle** : `App\Models\User\UserLog`
- **Service** : `App\Services\LogService`
- **Trait** : `App\Traits\LogsUserActions`
- **Exemple** : `App\Livewire\Examples\ExampleWithLogs`

## Structure de la table `user_logs`

```sql
CREATE TABLE user_logs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_category VARCHAR(30) NOT NULL,
    description TEXT NOT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    planet_id BIGINT NULL,
    target_user_id BIGINT NULL,
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Types d'actions supportées

### Authentification (`auth`)
- `login` - Connexion utilisateur
- `logout` - Déconnexion utilisateur

### Ressources (`resource`)
- `resource_spend` - Dépense de ressources
- `resource_gain` - Gain de ressources

### Bâtiments (`building`)
- `building_purchase` - Achat de bâtiment
- `building_upgrade` - Amélioration de bâtiment

### Messages (`message`)
- `private_message_sent` - Message privé envoyé
- `private_message_received` - Message privé reçu

### Forum (`forum`)
- `forum_post` - Post sur le forum

### Alliance (`alliance`)
- `alliance_join` - Adhésion à une alliance
- `alliance_leave` - Départ d'une alliance

### Combat (`combat`)
- `attack_launched` - Attaque lancée
- `attack_received` - Attaque reçue

### Commerce (`trade`)
- `trade_created` - Échange créé
- `trade_accepted` - Échange accepté

### Technologies (`technology`)
- `technology_research` - Recherche de technologie

### Missions (`mission`)
- `mission_launched` - Mission lancée

### Planètes (`planet`)
- `planet_created` - Planète créée

### Paramètres (`settings`)
- `settings_changed` - Paramètres modifiés

## Utilisation

### 1. Dans un composant Livewire

```php
use App\Traits\LogsUserActions;

class MonComposant extends Component
{
    use LogsUserActions;
    
    public function acheterBatiment()
    {
        // Logique d'achat...
        
        // Log automatique
        $this->logBuildingPurchase(
            'Mine de métal',
            1, // ID du bâtiment
            ['metal' => 100, 'cristal' => 50], // Coût
            $planetId
        );
        
        // Log de dépense de ressources
        $this->logResourceSpend(
            ['metal' => 100, 'cristal' => 50],
            'Achat de bâtiment',
            $planetId
        );
    }
    
    public function envoyerMessage()
    {
        // Logique d'envoi...
        
        $this->logPrivateMessageSent(
            $recipientId,
            'Sujet du message',
            $messageId
        );
    }
}
```

### 2. Dans un service

```php
use App\Services\LogService;

class MonService
{
    public function __construct(
        private LogService $logService
    ) {}
    
    public function effectuerAction(User $user)
    {
        // Logique métier...
        
        // Log direct
        $this->logService->logResourceGain(
            $user->id,
            ['metal' => 500],
            'Production automatique',
            $planetId
        );
    }
}
```

### 3. Log personnalisé

```php
// Avec le trait
$this->logAction(
    'custom_action',
    'custom_category',
    'Description de l\'action personnalisée',
    ['data' => 'valeur'],
    $planetId,
    $targetUserId,
    UserLog::SEVERITY_WARNING
);

// Avec le service directement
$logService->log(
    $userId,
    'custom_action',
    'custom_category',
    'Description avec {placeholder}',
    ['placeholder' => 'valeur remplacée'],
    $planetId,
    $targetUserId,
    UserLog::SEVERITY_INFO,
    $request
);
```

## Récupération des logs

### Logs d'un utilisateur

```php
$logService = app(LogService::class);

// Tous les logs
$logs = $logService->getUserLogs($userId);

// Logs filtrés
$logs = $logService->getUserLogs(
    $userId,
    'building_purchase', // Type d'action
    'building',          // Catégorie
    20,                  // Limite
    0                    // Offset
);
```

### Statistiques

```php
// Statistiques des 30 derniers jours
$stats = $logService->getLogStats($userId, 30);

// Retourne :
// [
//     'total' => 150,
//     'by_category' => ['building' => 20, 'resource' => 80, ...],
//     'by_severity' => ['info' => 140, 'warning' => 10, ...],
//     'by_day' => ['2025-06-15' => 10, '2025-06-16' => 15, ...]
// ]
```

### Avec Eloquent

```php
use App\Models\User\UserLog;

// Logs récents
$logs = UserLog::byUser($userId)
    ->recent(24) // 24 dernières heures
    ->with(['planet', 'targetUser'])
    ->get();

// Logs par catégorie
$buildingLogs = UserLog::byUser($userId)
    ->byCategory('building')
    ->orderBy('created_at', 'desc')
    ->get();

// Logs par type d'action
$loginLogs = UserLog::byUser($userId)
    ->byActionType('login')
    ->byDateRange(
        Carbon::now()->subDays(7),
        Carbon::now()
    )
    ->get();
```

## Relations Eloquent

### Dans le modèle User

```php
// Logs de l'utilisateur
$user->userLogs;

// Logs où l'utilisateur est la cible
$user->targetLogs;
```

### Dans le modèle UserLog

```php
// Utilisateur qui a effectué l'action
$log->user;

// Planète concernée
$log->planet;

// Utilisateur cible
$log->targetUser;
```

## Métadonnées et placeholders

Les métadonnées permettent de stocker des données supplémentaires et d'utiliser des placeholders dans les descriptions :

```php
$logService->log(
    $userId,
    'building_purchase',
    'building',
    'Achat du bâtiment {building_name} pour {total_cost} ressources',
    [
        'building_name' => 'Mine de métal',
        'building_id' => 1,
        'cost' => ['metal' => 100, 'cristal' => 50],
        'total_cost' => 150
    ]
);

// La description formatée sera :
// "Achat du bâtiment Mine de métal pour 150 ressources"
```

## Nettoyage automatique

```php
// Supprimer les logs de plus de 90 jours
$deletedCount = $logService->cleanOldLogs(90);
```

## Bonnes pratiques

1. **Utilisez le trait `LogsUserActions`** dans vos composants Livewire pour une intégration facile
2. **Loggez les actions importantes** : achats, attaques, échanges, etc.
3. **Utilisez les bonnes catégories et types** pour faciliter les recherches
4. **Ajoutez des métadonnées pertinentes** pour le contexte
5. **Choisissez le bon niveau de sévérité** :
   - `info` : Actions normales
   - `warning` : Actions importantes (attaques, gros achats)
   - `error` : Erreurs récupérables
   - `critical` : Erreurs critiques
6. **Nettoyez régulièrement** les anciens logs

## Exemple d'intégration complète

Voir le fichier `app/Livewire/Examples/ExampleWithLogs.php` pour un exemple complet d'utilisation du système de logs.

## Commandes utiles

```bash
# Exécuter la migration
php artisan migrate

# Créer un nouveau type de log (optionnel)
php artisan make:migration add_new_log_types_to_user_logs_table

# Nettoyer les anciens logs (à ajouter dans une commande artisan)
php artisan logs:clean --days=90
```