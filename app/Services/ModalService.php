<?php

namespace App\Services;

class ModalService
{
    /**
     * Ouvre une modal avec le composant spécifié
     *
     * @param string $component Le nom du composant Livewire à charger
     * @param string $title Le titre de la modal
     * @param array $data Les données à passer au composant
     * @return array Les données formatées pour l'événement openModal
     */
    public static function open(string $component, string $title = '', array $data = []): array
    {
        return [
            'component' => $component,
            'title' => $title,
            'data' => $data
        ];
    }

    /**
     * Ferme la modal actuelle
     *
     * @return array Les données pour l'événement closeModal
     */
    public static function close(): array
    {
        return [];
    }

    /**
     * Ouvre une modal de confirmation
     *
     * @param string $title Le titre de la confirmation
     * @param string $message Le message de confirmation
     * @param string $confirmText Le texte du bouton de confirmation
     * @param string $cancelText Le texte du bouton d'annulation
     * @param array $data Données supplémentaires
     * @return array Les données formatées pour l'événement openModal
     */
    public static function confirm(string $title, string $message, string $confirmText = 'Confirmer', string $cancelText = 'Annuler', array $data = []): array
    {
        return self::open('modal.confirm-modal', $title, array_merge([
            'message' => $message,
            'confirmText' => $confirmText,
            'cancelText' => $cancelText
        ], $data));
    }
}