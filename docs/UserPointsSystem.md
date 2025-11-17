# Système de Points Utilisateur

Ce document décrit le système de calcul de points pour les utilisateurs du jeu Stargate V3.

## Vue d'ensemble

Le système de points calcule automatiquement les points des utilisateurs basés sur :
- **Bâtiments** : Points basés sur les coûts de construction et niveaux
- **Unités** : Points basés sur les coûts de production et quantités
- **Défenses** : Points basés sur les coûts de construction et quantités
- **Vaisseaux** : Points basés sur les coûts de construction et quantités
- **Technologies** : Points basés sur les coûts de recherche et niveaux

## Calcul des Points

### Règles de Conversion
- **Bâtiments/Unités/Défenses/Vaisseaux** : 1000 ressources dépensées = 1 point
- **Technologies** : 100 ressources dépensées = 1 point

### Formule de Calcul

#### Bâtiments et Technologies
Pour les éléments avec des niveaux, le coût total est calculé en additionnant les coûts de tous les niveaux :
```
Coût Total = Σ(Coût du niveau i) pour i = 1 à niveau_actuel
```

#### Unités, Défenses et Vaisseaux
Pour les éléments avec des quantités :
```
Coût Total = Coût_unitaire × Quantité
```

## Structure de la Base de Données

### Table `user_stats`
```sql
CREATE TABLE user_stats (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY,
    total_points BIGINT DEFAULT 0,
    building_points BIGINT DEFAULT 0,
    units_points BIGINT DEFAULT 0,
    defense_points BIGINT DEFAULT 0,
    ship_points BIGINT DEFAULT 0,
    technology_points BIGINT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(user_id)
);
```

## Modèles

### UserStat
Le modèle `App\Models\User\UserStat` gère les statistiques de points :

```php
// Récupérer les points d'un utilisateur
$user = User::find(1);
$points = $user->userStat->getPointsBreakdown();

// Mettre à jour les points totaux
$user->userStat->updateTotalPoints();
```

## Job de Calcul

### CalculateUserPointsJob
Le job `App\Jobs\CalculateUserPointsJob` effectue les calculs :

```php
// Calculer pour un utilisateur spécifique
CalculateUserPointsJob::dispatch($userId);

// Calculer pour tous les utilisateurs
CalculateUserPointsJob::dispatch();
```

## Commande Artisan

### Utilisation
```bash
# Calculer les points pour tous les utilisateurs
php artisan points:calculate

# Calculer les points pour un utilisateur spécifique
php artisan points:calculate --user=123

# Exécuter en arrière-plan (queue)
php artisan points:calculate --queue

# Combinaison des options
php artisan points:calculate --user=123 --queue
```

## Service UserPointsService

Le service `App\Services\UserPointsService` fournit une interface simple :

```php
use App\Services\UserPointsService;

$pointsService = new UserPointsService();

// Calculer les points d'un utilisateur
$pointsService->calculateUserPoints($userId);

// Obtenir les points d'un utilisateur
$points = $pointsService->getUserPoints($userId);

// Obtenir le top 10 des utilisateurs
$topUsers = $pointsService->getTopUsersByPoints(10);

// Obtenir le classement d'un utilisateur
$ranking = $pointsService->getUserRanking($userId);

// Obtenir le top par catégorie
$topBuilders = $pointsService->getTopUsersByCategory('building_points', 10);
```

## Automatisation

### Planification
Pour automatiser le calcul des points, ajoutez dans `app/Console/Kernel.php` :

```php
protected function schedule(Schedule $schedule)
{
    // Calculer les points toutes les heures
    $schedule->command('points:calculate --queue')
             ->hourly()
             ->withoutOverlapping();
}
```

### Événements
Vous pouvez déclencher le calcul lors d'événements spécifiques :

```php
// Après construction d'un bâtiment
event(new BuildingConstructed($user, $building));

// Dans un listener
class RecalculateUserPoints
{
    public function handle($event)
    {
        CalculateUserPointsJob::dispatch($event->user->id);
    }
}
```

## Performance

### Optimisations
- Le job traite les utilisateurs par chunks de 100
- Utilisation de la queue pour les gros calculs
- Index sur `user_id` dans la table `user_stats`
- Calculs en lot pour réduire les requêtes DB

### Monitoring
- Logs d'erreur dans `storage/logs/laravel.log`
- Temps d'exécution affiché dans la commande
- Support des queues pour traitement asynchrone

## Exemples d'Utilisation

### Affichage des Points dans une Vue
```php
// Dans un contrôleur Livewire
class UserProfile extends Component
{
    public function render()
    {
        $user = auth()->user();
        $points = $user->userStat?->getPointsBreakdown() ?? [];
        $ranking = app(UserPointsService::class)->getUserRanking($user->id);
        
        return view('livewire.user-profile', compact('points', 'ranking'));
    }
}
```

### Classement Global
```php
// Dans un contrôleur de classement
class RankingController extends Controller
{
    public function index(UserPointsService $pointsService)
    {
        $topUsers = $pointsService->getTopUsersByPoints(50);
        $categories = [
            'building_points' => 'Constructeurs',
            'units_points' => 'Armées',
            'defense_points' => 'Défenseurs',
            'ship_points' => 'Flottes',
            'technology_points' => 'Chercheurs'
        ];
        
        return view('ranking', compact('topUsers', 'categories'));
    }
}
```

## Dépannage

### Problèmes Courants
1. **Points non calculés** : Vérifier que la migration a été exécutée
2. **Erreurs de calcul** : Vérifier les relations entre modèles
3. **Performance lente** : Utiliser la queue pour les gros calculs
4. **Données manquantes** : Initialiser les stats avec `initializeUserStats()`

### Commandes de Debug
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Tester le calcul pour un utilisateur
php artisan points:calculate --user=1

# Vérifier la queue
php artisan queue:work
```