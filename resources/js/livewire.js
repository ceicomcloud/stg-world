/**
 * livewire.js - Fonctionnalités en temps réel pour Stargate V3
 *
 * Ce fichier gère désormais uniquement les timers des missions.
 * Les timers des files d'attente (queue-timer) sont pris en charge par Alpine.
 */

// Fonction pour mettre à jour les timers des missions
function updateTimers() {
    document.querySelectorAll('.mission-timer').forEach(timer => {
        const endTsStr = timer.getAttribute('data-end-time');
        if (!endTsStr) return;
        const endTs = parseInt(endTsStr, 10);
        if (!endTs || isNaN(endTs)) return;

        const diff = (endTs * 1000) - Date.now();

        if (diff <= 0) {
            timer.textContent = 'Terminé';
            if (!timer.hasAttribute('data-completed')) {
                timer.setAttribute('data-completed', 'true');
                const missionItem = timer.closest('.mission-item-compact');
                if (missionItem) {
                    window.Livewire.dispatch('missionsUpdated');
                }
            }
        } else {
            timer.textContent = formatTimeRemaining(diff);
        }
    });
}

// Fonction pour formater le temps restant
function formatTimeRemaining(milliseconds) {
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

// Initialisation des timers lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    updateTimers();
    setInterval(updateTimers, 1000);
});


export { updateTimers, formatTimeRemaining };