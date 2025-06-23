<?php

namespace App\Backend\Util;

/**
 * Fournit des méthodes statiques pour nettoyer et sécuriser les données en sortie.
 * Essentiel pour la prévention des attaques XSS.
 */
class Sanitizer
{
    /**
     * Nettoie une chaîne de caractères pour un affichage sécurisé en HTML.
     * Convertit les caractères spéciaux en entités HTML.
     *
     * @param string|null $string La chaîne à nettoyer.
     * @return string La chaîne nettoyée.
     */
    public static function html(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Nettoie une URL pour une utilisation sécurisée dans les attributs href ou src.
     * Supprime tous les caractères sauf ceux autorisés dans une URL.
     *
     * @param string|null $url L'URL à nettoyer.
     * @return string L'URL nettoyée.
     */
    public static function url(?string $url): string
    {
        if ($url === null) {
            return '';
        }
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Nettoie une adresse e-mail.
     * Supprime tous les caractères illégaux d'une adresse e-mail.
     *
     * @param string|null $email L'email à nettoyer.
     * @return string L'email nettoyé.
     */
    public static function email(?string $email): string
    {
        if ($email === null) {
            return '';
        }
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Nettoie une chaîne de caractères en ne gardant que les chiffres.
     *
     * @param string|null $number La chaîne à nettoyer.
     * @return string La chaîne contenant uniquement des chiffres.
     */
    public static function digits(?string $number): string
    {
        if ($number === null) {
            return '';
        }
        return preg_replace('/[^0-9]/', '', $number);
    }
}