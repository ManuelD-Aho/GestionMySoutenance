<?php

/**
 * Fichier de configuration central pour les stratégies de génération d'identifiants.
 * Chaque entrée définit les règles pour un type d'entité spécifique.
 *
 * - 'prefix': Le préfixe de 3 ou 4 lettres utilisé pour l'ID (ex: RAP). C'est aussi le nom de la séquence en base de données.
 * - 'padding': Le nombre de chiffres pour la partie séquentielle, complétée par des zéros (ex: 4 pour 0001).
 * - 'reset_yearly': (bool) Si true, la séquence est remise à zéro chaque année. Si false, la séquence est globale et continue.
 */

return [
    // --- Entités de Processus ---
    'rapport_etudiant' => [
        'prefix' => 'RAP',
        'padding' => 5,
        'reset_yearly' => true,
    ],
    'compte_rendu' => [
        'prefix' => 'PV',
        'padding' => 5,
        'reset_yearly' => true,
    ],
    'session_validation' => [
        'prefix' => 'SESS',
        'padding' => 4,
        'reset_yearly' => true,
    ],
    'reclamation' => [
        'prefix' => 'RECL',
        'padding' => 5,
        'reset_yearly' => true,
    ],
    'penalite' => [
        'prefix' => 'PEN',
        'padding' => 5,
        'reset_yearly' => true,
    ],
    'delegation' => [
        'prefix' => 'DEL',
        'padding' => 6,
        'reset_yearly' => false,
    ],

    // --- Entités Techniques et d'Audit ---
    'document_genere' => [
        'prefix' => 'DOC',
        'padding' => 7,
        'reset_yearly' => false,
    ],
    'vote_commission' => [
        'prefix' => 'VOTE',
        'padding' => 7,
        'reset_yearly' => false,
    ],
    'enregistrement' => [
        'prefix' => 'AUDIT',
        'padding' => 8,
        'reset_yearly' => false,
    ],
    'piste' => [
        'prefix' => 'TRACE',
        'padding' => 8,
        'reset_yearly' => false,
    ],
    'historique_mot_de_passe' => [
        'prefix' => 'HMP',
        'padding' => 8,
        'reset_yearly' => false,
    ],
    'conversation' => [
        'prefix' => 'CONV',
        'padding' => 7,
        'reset_yearly' => false,
    ],
    'message_chat' => [
        'prefix' => 'MSG',
        'padding' => 8,
        'reset_yearly' => false,
    ],
    'reception_notification' => [
        'prefix' => 'RECP',
        'padding' => 8,
        'reset_yearly' => false,
    ],

    // --- Entités de Configuration (si nécessaire) ---
    'rapport_modele' => [
        'prefix' => 'TPL', // Template
        'padding' => 4,
        'reset_yearly' => false,
    ],
    'rapport_modele_section' => [
        'prefix' => 'TPLSEC',
        'padding' => 5,
        'reset_yearly' => false,
    ],
    'conformite_detail' => [
        'prefix' => 'CONFDT',
        'padding' => 7,
        'reset_yearly' => false,
    ],
];