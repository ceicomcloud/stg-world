# Système de Génération Automatique des Galaxies

## Vue d'ensemble

Le système de génération automatique des galaxies permet de créer l'univers du jeu basé sur la configuration du serveur. Il génère automatiquement les galaxies, systèmes et planètes selon les paramètres définis dans `ServerConfig`.

## Configuration du Serveur

Les paramètres suivants dans `ServerConfig` contrôlent la génération :

- **`galaxies`** : Nombre total de galaxies (défaut: 5)
- **`systems_per_galaxy`** : Nombre de systèmes par galaxie (défaut: 100)
- **`planets_per_system`** : Nombre de planètes par système (défaut: 10)
- **`total_planets`** : Nombre maximum de planètes dans l'univers (défaut: 7500)

### Calcul Automatique

Le système calcule automatiquement :
- **Capacité maximale** = `galaxies × systems_per_galaxy × planets_per_system`
- Si `total_planets` > capacité maximale, il est ajusté automatiquement

## Utilisation

### Via Seeder (lors du seeding initial)

```bash
php artisan db:seed
```

Le `GalaxySeeder` est automatiquement appelé dans `DatabaseSeeder`.

### Via Commande Artisan

#### Génération standard
```bash
php artisan galaxy:generate
```

#### Génération forcée (sans confirmation)
```bash
php artisan galaxy:generate --force
```

#### Override temporaire des paramètres
```bash
# Générer avec 3 galaxies au lieu de la config
php artisan galaxy:generate --galaxies=3

# Générer avec des paramètres personnalisés
php artisan galaxy:generate --galaxies=10 --systems=50 --planets=15 --total=5000
```

## Types de Planètes Générées

Le système génère différents types de planètes avec des probabilités définies :

### Types et Probabilités
- **Planètes normales** : 85% (colonisables)
- **Astéroïdes** : 10% (ressources minières)
- **Champs de débris** : 5% (non colonisables)

### Tailles de Planètes
- **Tiny** : 15% (80-120 champs)
- **Small** : 25% (120-180 champs)
- **Medium** : 35% (180-250 champs)
- **Large** : 20% (250-320 champs)
- **Huge** : 5% (320-400 champs)

## Propriétés des Planètes

### Température
Basée sur la position dans le système :
- **Position 1-3** : Planètes chaudes (proches du soleil)
- **Position 4-7** : Planètes tempérées
- **Position 8-10** : Planètes froides (éloignées du soleil)

### Bonus de Ressources
Distribution automatique selon la position :
- **Positions 1-3** : Bonus métal et énergie
- **Positions 4-7** : Bonus cristal
- **Positions 8-10** : Bonus deutérium

### Caractéristiques Spéciales
- **Astéroïdes** : 50% plus de métal, 70% moins de champs
- **Champs de débris** : Aucun champ constructible

## Noms des Planètes

Les planètes reçoivent des noms générés automatiquement :
- **Format** : `[Préfixe] [Suffixe]`
- **Préfixes** : Alpha, Beta, Gamma, Nova, Stellar, Orion, etc.
- **Suffixes** : Prime, Major, I, II, III, etc. (basés sur la position)

## Performance

### Optimisations
- **Traitement par lots** : 1000 planètes par insertion
- **Utilisation de DB::table()** : Plus rapide que Eloquent pour les gros volumes
- **Génération en mémoire** : Calculs effectués avant insertion

### Temps de Génération
- **7500 planètes** : ~10-15 secondes
- **Affichage du progrès** : Mise à jour toutes les 1000 planètes

## Structure de Base de Données

### Table `template_planets`
```sql
CREATE TABLE template_planets (
    id BIGINT PRIMARY KEY,
    galaxy INT NOT NULL,
    system INT NOT NULL,
    position INT NOT NULL,
    name VARCHAR(255),
    type ENUM('planet', 'moon', 'asteroid', 'debris'),
    size ENUM('tiny', 'small', 'medium', 'large', 'huge'),
    diameter INT DEFAULT 0,
    min_temperature INT DEFAULT -50,
    max_temperature INT DEFAULT 50,
    fields INT DEFAULT 163,
    metal_bonus DECIMAL(5,2) DEFAULT 1.00,
    crystal_bonus DECIMAL(5,2) DEFAULT 1.00,
    deuterium_bonus DECIMAL(5,2) DEFAULT 1.00,
    energy_bonus DECIMAL(5,2) DEFAULT 1.00,
    is_colonizable BOOLEAN DEFAULT TRUE,
    is_occupied BOOLEAN DEFAULT FALSE,
    is_available BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_position (galaxy, system, position)
);
```

## Exemples d'Utilisation

### Serveur de Test (Petit)
```bash
php artisan galaxy:generate --galaxies=2 --systems=20 --planets=5 --total=200
```

### Serveur de Production (Grand)
```bash
php artisan galaxy:generate --galaxies=10 --systems=200 --planets=15 --total=30000
```

### Régénération Rapide
```bash
php artisan galaxy:generate --force
```

## Intégration avec le Jeu

Les planètes générées sont utilisées par :
- **Système de colonisation** : `is_colonizable = true`
- **Exploration spatiale** : Toutes les planètes visibles
- **Commerce et ressources** : Bonus appliqués automatiquement
- **Interface utilisateur** : Affichage des galaxies/systèmes

## Maintenance

### Vérification de l'Intégrité
```sql
-- Vérifier les doublons
SELECT galaxy, system, position, COUNT(*) 
FROM template_planets 
GROUP BY galaxy, system, position 
HAVING COUNT(*) > 1;

-- Statistiques par type
SELECT type, COUNT(*) as count, 
       ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM template_planets), 2) as percentage
FROM template_planets 
GROUP BY type;
```

### Sauvegarde Recommandée
Avant toute régénération en production :
```bash
mysqldump -u user -p database template_planets > backup_planets.sql
```

## Dépannage

### Erreur de Contrainte de Clé Étrangère
Si des planètes sont déjà référencées :
```bash
# Supprimer d'abord les références
php artisan tinker
>>> App\Models\Planet\Planet::truncate();
>>> exit
php artisan galaxy:generate --force
```

### Performance Lente
- Vérifier les index sur `(galaxy, system, position)`
- Augmenter `innodb_buffer_pool_size` si nécessaire
- Utiliser `--force` pour éviter les confirmations

## Évolutions Futures

- Support des lunes (type 'moon')
- Génération de ressources spéciales
- Événements cosmiques (supernovas, trous noirs)
- Zones PvP/PvE par galaxie
- Import/Export de configurations d'univers