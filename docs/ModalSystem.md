# Système de Modale (wire-elements/modal v3)

Ce système s’appuie sur le package `wire-elements/modal` (v3) pour ouvrir des modales avec des composants Livewire dynamiques.

## Architecture

### Composants principaux

1. **BaseModal** (`App\Livewire\Modal\BaseModal`)
   - Composant principal qui gère l'affichage de la modal
   - Écoute les événements `openModal` et `closeModal`
   - Charge dynamiquement les composants Livewire

2. **ModalService** (`App\Services\ModalService`)
   - Service utilitaire pour formater les données de modal
   - Méthodes helper pour différents types de modals

3. **HasModal** (`App\Traits\HasModal`)
   - Trait pour faciliter l'utilisation dans les composants Livewire
   - Méthodes raccourcies pour ouvrir/fermer les modals

## Installation

Le système est automatiquement inclus dans le layout `game.blade.php` :

```blade
@livewire('wire-elements-modal')
```

## Utilisation

### Méthode 1: Dispatch direct depuis une vue

```blade
<button onclick="Livewire.dispatch('openModal', { component: 'game.modal.building-info', arguments: { buildingId: 1, type: 'building' } })">
    Ouvrir Modal
</button>
```

### Méthode 2: Depuis un composant Livewire

```php
class MonComposant extends \Livewire\Component
{
    public function ouvrirModal()
    {
        $this->dispatch('openModal', component: 'game.modal.building-info', arguments: [
            'buildingId' => 1,
            'type' => 'building',
        ]);
    }

    public function fermerModal()
    {
        $this->dispatch('closeModal');
    }
}
```

### Méthode 3: Avec le ModalService

```php
use App\Services\ModalService;

public function ouvrirModal()
{
    $this->dispatch('openModal', ModalService::open(
        'game.modal.building-info',
        'Informations Bâtiment',
        ['buildingId' => 1]
    ));
}
```

## Création d'un composant modal

### 1. Créer le composant Livewire

```bash
php artisan make:livewire Game/Modal/MonComposant
```

### 2. Implémenter le composant

```php
<?php

namespace App\Livewire\Game\Modal;

use Livewire\Component;

class MonComposant extends Component
{
    public $data;
    public $monParametre;

    public function mount($data = [], $monParametre = null)
    {
        $this->data = $data;
        $this->monParametre = $monParametre;
    }

    public function action()
    {
        // Logique métier
        
        // Fermer la modal
        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.game.modal.mon-composant');
    }
}
```

### 3. Créer la vue

```blade
<div>
    <div class="modal-section">
        <h3>Mon Composant</h3>
        <p>Paramètre reçu: {{ $monParametre }}</p>
    </div>

    <div class="modal-section">
        <button class="btn btn-primary" wire:click="action">
            Action
        </button>
        <button class="btn btn-secondary" wire:click="$dispatch('closeModal')">
            Fermer
        </button>
    </div>
</div>
```

## Styles CSS

Le système utilise les styles définis dans `resources/css/modal.css` avec l'attribut `page="modal"`.

## Événements disponibles

- `openModal`: Ouvre une modal avec un composant
- `closeModal`: Ferme la modal actuelle

## Paramètres openModal

- `component`: Nom du composant Livewire à charger (requis)
- `title`: Titre de la modal (optionnel)
- `data`: Données à passer au composant (optionnel)

## Exemples d'utilisation avancée

### Modal de confirmation

```php
$this->confirmModal(
    'Confirmer la suppression',
    'Êtes-vous sûr de vouloir supprimer cet élément ?',
    'Supprimer',
    'Annuler',
    ['itemId' => $id]
);
```

### Modal avec données complexes

```php
$this->openModal(
    'game.modal.building-upgrade',
    'Améliorer le bâtiment',
    [
        'building' => $building->toArray(),
        'costs' => $costs,
        'requirements' => $requirements
    ]
);
```