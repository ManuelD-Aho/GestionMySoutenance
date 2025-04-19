@echo off
REM Script de démarrage pour Windows adapté à la région
echo -----------------------------------------------------
echo Démarrage de l'environnement Docker pour la plateforme
echo de gestion des soutenances MIAGE - UFHB Cocody
echo -----------------------------------------------------

REM Vérifier si Docker est installé
where docker >nul 2>nul
if %errorlevel% neq 0 (
    echo Docker n'est pas installé ou n'est pas dans le PATH
    echo Veuillez installer Docker Desktop pour Windows
    pause
    exit /b 1
)

REM Vérifier si Docker est en cours d'exécution
docker info >nul 2>nul
if %errorlevel% neq 0 (
    echo Docker n'est pas en cours d'exécution
    echo Veuillez démarrer Docker Desktop
    pause
    exit /b 1
)

REM Créer le fichier .env s'il n'existe pas
if not exist .env (
    echo Création du fichier .env...
    echo APP_ENV=dev> .env
    echo APP_DEBUG=true>> .env
    echo DB_NAME=universite>> .env
    echo DB_USER=miage_user>> .env
    echo DB_PASSWORD=miage_password>> .env
    echo DB_ROOT_PASSWORD=root_password>> .env
    echo TIMEZONE=Africa/Abidjan>> .env
    echo APP_NAME=Plateforme de Gestion des Soutenances MIAGE - UFHB>> .env
) else (
    echo Fichier .env trouvé
)

REM Vérifier si les répertoires nécessaires existent
if not exist var\logs mkdir var\logs
if not exist var\logs\mysql mkdir var\logs\mysql
if not exist var\logs\nginx mkdir var\logs\nginx
if not exist var\logs\php mkdir var\logs\php
if not exist var\sessions mkdir var\sessions

REM Démarrer les conteneurs avec détection des pannes de courant
echo Démarrage des conteneurs Docker avec protection contre les coupures...
echo Les données sont persistantes même en cas de coupure électrique.
docker-compose up -d

REM Vérifier le statut des conteneurs
docker-compose ps

REM Afficher les URLs d'accès
echo.
echo Application accessible à l'adresse : http://localhost:8080
echo PHPMyAdmin accessible à l'adresse : http://localhost:8081
echo.
echo -----------------------------------------------------
echo IMPORTANT: En cas de coupure électrique, redémarrez
echo simplement ce script quand le courant revient.
echo -----------------------------------------------------
echo.

REM Attendre avant de fermer
pause