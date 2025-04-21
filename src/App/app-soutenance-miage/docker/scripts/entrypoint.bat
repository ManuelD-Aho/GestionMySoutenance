@echo off
setlocal enabledelayedexpansion

REM Script de gestion Docker pour l'application MIAGE Soutenances
REM Université Félix Houphouët-Boigny - Côte d'Ivoire
REM Date: 2025-04-19 19:28

echo ============================================================
echo   Plateforme de Gestion des Soutenances MIAGE
echo   Université Félix Houphouët-Boigny - Côte d'Ivoire
echo ============================================================
echo.

REM Vérification de Docker
echo Vérification de Docker...
docker info >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Docker n'est pas en cours d'exécution.
    echo Tentative de démarrage de Docker Desktop...

    if exist "C:\Program Files\Docker\Docker\Docker Desktop.exe" (
        start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    ) else if exist "C:\Program Files\Docker Desktop\Docker Desktop.exe" (
        start "" "C:\Program Files\Docker Desktop\Docker Desktop.exe"
    ) else (
        echo ERREUR: Impossible de trouver Docker Desktop.
        echo Veuillez le démarrer manuellement.
        goto fin
    )

    echo Attente du démarrage de Docker (30 secondes max)...
    for /L %%i in (1,1,30) do (
        timeout /t 1 /nobreak >nul
        docker info >nul 2>&1
        if !ERRORLEVEL! EQU 0 (
            echo Docker est maintenant démarré.
            goto docker_ok
        )
    )

    echo ERREUR: Docker n'a pas démarré dans le temps imparti.
    goto fin
)

:docker_ok
echo Docker est prêt.

REM Vérification de docker-compose
echo Vérification de docker-compose...
if exist "%LOCALAPPDATA%\Docker\wsl\data\ext4.vhdx" (
    echo Version Docker avec WSL2 détectée.
    set COMPOSE_CMD=docker compose
) else (
    docker compose version >nul 2>&1
    if !ERRORLEVEL! EQU 0 (
        set COMPOSE_CMD=docker compose
    ) else (
        docker-compose version >nul 2>&1
        if !ERRORLEVEL! EQU 0 (
            set COMPOSE_CMD=docker-compose
        ) else (
            echo ERREUR: Docker Compose n'est pas disponible.
            goto fin
        )
    )
)

echo Utilisation de la commande: %COMPOSE_CMD%

REM Création des répertoires
echo Création des répertoires nécessaires...
if not exist "public\uploads\rapports" mkdir "public\uploads\rapports" 2>nul
if not exist "public\uploads\documents" mkdir "public\uploads\documents" 2>nul
if not exist "public\uploads\temp" mkdir "public\uploads\temp" 2>nul
if not exist "var\logs" mkdir "var\logs" 2>nul
if not exist "var\sessions" mkdir "var\sessions" 2>nul

REM Vérification ou création du fichier .env
if not exist ".env" (
    echo Création du fichier .env...
    (
        echo APP_ENV=dev
        echo APP_DEBUG=true
        echo DB_HOST=db
        echo DB_PORT=3306
        echo DB_NAME=universite
        echo DB_USER=miage_user
        echo DB_PASSWORD=miage_password
        echo DB_ROOT_PASSWORD=root_password
        echo TIMEZONE=Africa/Abidjan
    ) > .env
)

REM Démarrage des conteneurs
echo Démarrage des conteneurs Docker...
%COMPOSE_CMD% up -d

if %ERRORLEVEL% NEQ 0 (
    echo ERREUR: Impossible de démarrer les conteneurs.
    %COMPOSE_CMD% logs
    goto fin
)

echo Attente du démarrage complet des services (20 secondes)...
timeout /t 20 /nobreak >nul

REM Vérification des conteneurs
echo Vérification des conteneurs...
%COMPOSE_CMD% ps

echo.
echo ============================================================
echo   APPLICATION PRÊTE À L'EMPLOI
echo ============================================================
echo.
echo   Application Web:    http://localhost:8080
echo   PHPMyAdmin:         http://localhost:8081
echo.
echo ============================================================
echo.

REM Ouverture automatique du navigateur
echo Ouverture de l'application dans le navigateur...
start "" http://localhost:8080

echo.
echo Commandes utiles:
echo - Pour arrêter les services:    %COMPOSE_CMD% down
echo - Pour voir les journaux:       %COMPOSE_CMD% logs -f
echo - Pour redémarrer les services: %COMPOSE_CMD% restart
echo.

:fin
echo.
pause
endlocal