import './bootstrap';
import './livewire';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Clipboard from '@ryangjchandler/alpine-clipboard'
import Swal from 'sweetalert2'


Alpine.plugin(Clipboard);

window.Swal = Swal;

// Système de toast personnalisé pour l'administration
const adminToast = {
    create(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `admin-toast admin-toast-${type}`;
        toast.innerHTML = `
            <div class="admin-toast-icon">
                <i class="fas fa-${this.getIcon(type)}"></i>
            </div>
            <div class="admin-toast-content">
                <p>${message}</p>
            </div>
            <button class="admin-toast-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Ajouter au conteneur de toasts ou en créer un s'il n'existe pas
        let container = document.querySelector('.admin-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'admin-toast-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(toast);
        
        // Gérer la fermeture du toast
        const closeBtn = toast.querySelector('.admin-toast-close');
        closeBtn.addEventListener('click', () => {
            this.close(toast);
        });
        
        // Fermeture automatique après la durée spécifiée
        setTimeout(() => {
            this.close(toast);
        }, duration);
        
        // Animation d'entrée
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        return toast;
    },
    
    close(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        
        // Supprimer l'élément après l'animation
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            
            // Supprimer le conteneur s'il est vide
            const container = document.querySelector('.admin-toast-container');
            if (container && container.children.length === 0) {
                document.body.removeChild(container);
            }
        }, 300);
    },
    
    getIcon(type) {
        switch (type) {
            case 'success': return 'check-circle';
            case 'error': return 'exclamation-circle';
            case 'warning': return 'exclamation-triangle';
            case 'info': 
            default: return 'info-circle';
        }
    },
    
    success(message, duration) {
        return this.create(message, 'success', duration);
    },
    
    error(message, duration) {
        return this.create(message, 'error', duration);
    },
    
    warning(message, duration) {
        return this.create(message, 'warning', duration);
    },
    
    info(message, duration) {
        return this.create(message, 'info', duration);
    }
};

window.adminToast = adminToast;

// Composant Alpine pour les timers de files d'attente (queue-timer)
// Remplace la mise à jour via resources/js/livewire.js par un compte à rebours autonome.
// Utilisation dans Blade :
// <div class="queue-timer" x-data="queueTimer('1680000000')" x-init="init()" x-text="display"></div>
window.queueTimer = function(endTs) {
    return {
        endTs: (typeof endTs === 'string') ? parseInt(endTs, 10) : endTs,
        display: '',
        completed: false,
        _intervalId: null,
        init() {
            if (!this.endTs || isNaN(this.endTs)) {
                this.display = '';
                return;
            }
            this.update();
            this._intervalId = setInterval(() => this.update(), 1000);
        },
        update() {
            const diff = (this.endTs * 1000) - Date.now();
            if (diff <= 0) {
                this.display = 'Terminé';
                if (!this.completed) {
                    this.completed = true;
                    if (this._intervalId) {
                        clearInterval(this._intervalId);
                        this._intervalId = null;
                    }
                    // Déclenche l’événement Livewire existant afin de rafraîchir les queues
                    if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                        window.Livewire.dispatch('queuesUpdated');
                    }
                }
            } else {
                this.display = this.formatTimeRemaining(diff);
            }
        },
        formatTimeRemaining(milliseconds) {
            const seconds = Math.floor(milliseconds / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            const remainingHours = hours % 24;
            const remainingMinutes = minutes % 60;
            const remainingSeconds = seconds % 60;

            let result = '';
            if (days > 0) result += `${days}j `;
            if (hours > 0 || days > 0) result += `${remainingHours.toString().padStart(2, '0')}h `;
            result += `${remainingMinutes.toString().padStart(2, '0')}m ${remainingSeconds.toString().padStart(2, '0')}s`;
            return result;
        }
    };
};

document.addEventListener('livewire:init', () => {
    // Garder les événements SweetAlert existants pour la compatibilité
    Livewire.on('swal:success', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        Swal.fire({
            icon: 'success',
            title: data.title ?? 'Succès !',
            text: data.text ?? '',
            confirmButtonColor: '#3085d6'
        });
    });

    Livewire.on('swal:error', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        Swal.fire({
            icon: 'error',
            title: data.title ?? 'Erreur !',
            text: data.text ?? '',
            confirmButtonColor: '#d33'
        });
    });

    Livewire.on('toast:success', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        Swal.fire({
            icon: 'success',
            title: data.title ?? 'Succès !',
            text: data.text ?? '',

            position: 'top-end',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    });

    Livewire.on('toast:error', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        Swal.fire({
            icon: 'error',
            title: data.title ?? 'Erreur !',
            text: data.text ?? '',

            position: 'top-end',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    });
    
    // Nouveaux événements pour les toasts d'administration
    Livewire.on('admin:toast', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        const message = data.message ?? '';
        const type = data.type ?? 'info';
        const duration = data.duration ?? 3000;
        
        adminToast.create(message, type, duration);
    });
    
    Livewire.on('admin:toast:success', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        const message = data.message ?? '';
        const duration = data.duration ?? 3000;
        
        adminToast.success(message, duration);
    });
    
    Livewire.on('admin:toast:error', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        const message = data.message ?? '';
        const duration = data.duration ?? 3000;
        
        adminToast.error(message, duration);
    });
    
    Livewire.on('admin:toast:warning', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        const message = data.message ?? '';
        const duration = data.duration ?? 3000;
        
        adminToast.warning(message, duration);
    });
    
    Livewire.on('admin:toast:info', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        const message = data.message ?? '';
        const duration = data.duration ?? 3000;
        
        adminToast.info(message, duration);
    });

    Livewire.on('background-updated', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        const background = data.background || 'default';
        
        if (background && background !== 'default') {
            document.body.style.setProperty('background-image', `url(/images/bg-${background}.png)`, 'important');
        } else {
            document.body.style.removeProperty('background-image');
        }
    });
});

Livewire.start();

// Capture et neutralise les rejets de promesses dus à des opérations avortées
// (ex.: fetch annulé par Livewire lors d’une mise à jour rapide ou navigation).
// On garde le logging pour les autres erreurs afin de ne pas masquer un vrai problème.
window.addEventListener('unhandledrejection', (event) => {
    const reason = event?.reason;
    // DOMException pour les opérations avortées côté Web API
    const isAbortError = reason && (reason.name === 'AbortError' || reason.message?.includes('The operation was aborted'));
    if (isAbortError) {
        console.info('Info: une requête/promesse a été annulée (AbortError). Comportement souvent bénin avec Livewire/SPA.');
        event.preventDefault(); // évite l’erreur "Uncaught (in promise)" dans la console
        return;
    }
    // Pour les autres rejets non gérés, afficher une trace plus lisible
    console.error('Unhandled promise rejection:', reason);
});
