<?php

namespace App\Backend\Util;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Fournit des méthodes statiques pour manipuler et formater les dates.
 * Centralise la gestion des fuseaux horaires et des formats de date de l'application.
 */
class DateHelper
{
    private const TIMEZONE = 'Europe/Paris';
    private const FORMAT_FR = 'd/m/Y H:i:s';
    private const FORMAT_DB = 'Y-m-d H:i:s';

    /**
     * Formate une date (venant de la DB ou un objet DateTime) en format français lisible.
     *
     * @param string|DateTime|null $date La date à formater.
     * @param string $format Le format de sortie souhaité.
     * @return string La date formatée ou une chaîne vide en cas d'erreur.
     */
    public static function formatToFrench(?string $date, string $format = self::FORMAT_FR): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $datetime = ($date instanceof DateTime) ? $date : new DateTime($date);
            $datetime->setTimezone(new DateTimeZone(self::TIMEZONE));
            return $datetime->format($format);
        } catch (Exception $e) {
            error_log("DateHelper format error: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Calcule le temps écoulé depuis une date donnée sous une forme lisible (ex: "il y a 2 heures").
     *
     * @param string|DateTime|null $date La date de départ.
     * @return string La chaîne de caractères représentant le temps écoulé.
     */
    public static function timeAgo(?string $date): string
    {
        if (empty($date)) {
            return 'jamais';
        }

        try {
            $datetime = ($date instanceof DateTime) ? $date : new DateTime($date);
            $now = new DateTime('now', new DateTimeZone(self::TIMEZONE));
            $interval = $now->diff($datetime);

            if ($interval->y > 0) return "il y a " . $interval->y . " an(s)";
            if ($interval->m > 0) return "il y a " . $interval->m . " mois";
            if ($interval->d > 0) return "il y a " . $interval->d . " jour(s)";
            if ($interval->h > 0) return "il y a " . $interval->h . " heure(s)";
            if ($interval->i > 0) return "il y a " . $interval->i . " minute(s)";
            return "à l'instant";
        } catch (Exception $e) {
            error_log("DateHelper timeAgo error: " . $e->getMessage());
            return 'date invalide';
        }
    }

    /**
     * Vérifie si une date est passée.
     *
     * @param string|DateTime|null $date La date à vérifier.
     * @return bool True si la date est dans le passé, false sinon.
     */
    public static function isPast(?string $date): bool
    {
        if (empty($date)) {
            return false;
        }
        try {
            $datetime = ($date instanceof DateTime) ? $date : new DateTime($date);
            $now = new DateTime('now', new DateTimeZone(self::TIMEZONE));
            return $datetime < $now;
        } catch (Exception $e) {
            return false;
        }
    }
}