@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

:: ─────────────────────────────────────────────────────────────
:: PGFE — Script d'installation pour Windows
::
:: Télécharge la dernière release, installe les prérequis,
:: configure le backend Laravel, le frontend Vue.js,
:: la base MariaDB et génère les scripts start/stop.
:: ─────────────────────────────────────────────────────────────

:: ── Configuration ────────────────────────────────────────────
set "REPO_OWNER=IN-AFRICA"
set "REPO_NAME=PGFE_INSTALL"
set "INSTALL_DIR=pgfe"
set "DB_NAME=pgfe_db"
set "DB_USER=pgfe_user"
set "APP_PORT=8000"
set "FRONTEND_PORT=5173"

:: Génération d'un mot de passe aléatoire
for /f %%i in ('powershell -NoProfile -Command "[System.Guid]::NewGuid().ToString('N').Substring(0,16)"') do set "DB_PASSWORD=pgfe_%%i"

set "TOTAL_STEPS=6"
set "ERRORS=0"

:: ── Point d'entrée ───────────────────────────────────────────
cls
echo.
echo   ╔═══════════════════════════════════════════════════════════════╗
echo   ║                                                               ║
echo   ║   ██████╗  ██████╗ ███████╗███████╗                          ║
echo   ║   ██╔══██╗██╔════╝ ██╔════╝██╔════╝                          ║
echo   ║   ██████╔╝██║  ███╗█████╗  █████╗                            ║
echo   ║   ██╔═══╝ ██║   ██║██╔══╝  ██╔══╝                            ║
echo   ║   ██║     ╚██████╔╝██║     ███████╗                          ║
echo   ║   ╚═╝      ╚═════╝ ╚═╝     ╚══════╝                          ║
echo   ║                                                               ║
echo   ╚═══════════════════════════════════════════════════════════════╝
echo.

:: Vérification des droits administrateur
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Ce script doit etre execute en tant qu'administrateur.
    echo     Clic droit sur le fichier ^> "Executer en tant qu'administrateur"
    pause
    exit /b 1
)

call :install_prerequisites
if !ERRORS! neq 0 goto :end_error
call :download_and_extract
if !ERRORS! neq 0 goto :end_error
call :install_backend
if !ERRORS! neq 0 goto :end_error
call :install_frontend
if !ERRORS! neq 0 goto :end_error
call :setup_database
if !ERRORS! neq 0 goto :end_error
call :create_start_scripts
call :create_info_file

echo.
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo   Installation terminee!
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.
echo   PGFE a ete installe avec succes!
echo.
echo   Repertoire: %CD%\%INSTALL_DIR%
echo.
echo   Pour demarrer PGFE:
echo     cd %INSTALL_DIR%
echo     start.cmd
echo.
echo   Sauvegardez vos identifiants de base de donnees:
echo     Base: %DB_NAME%
echo     User: %DB_USER%
echo     Pass: %DB_PASSWORD%
echo.
pause
exit /b 0

:end_error
echo.
echo [X] L'installation a echoue. Consultez les messages ci-dessus.
pause
exit /b 1

:: ── Étape 1 : Prérequis système ─────────────────────────────
:install_prerequisites
echo.
echo [Etape 1/%TOTAL_STEPS%] Installation des prerequis
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

:: Vérification de winget
where winget >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] winget n'est pas disponible. Installez App Installer depuis le Microsoft Store.
    set "ERRORS=1"
    goto :eof
)
echo [OK] winget disponible

:: PHP
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [ ] Installation de PHP 8.2...
    winget install --id PHP.PHP.8.2 --accept-source-agreements --accept-package-agreements -e >nul 2>&1
    if !errorlevel! neq 0 (
        echo [X] Echec de l'installation de PHP
        set "ERRORS=1"
        goto :eof
    )
    :: Ajouter PHP au PATH de la session courante
    for /f "tokens=*" %%p in ('where /r "C:\Program Files" php.exe 2^>nul') do (
        set "PATH=%%~dpp;!PATH!"
        goto :php_path_found
    )
    for /f "tokens=*" %%p in ('where /r "%LOCALAPPDATA%" php.exe 2^>nul') do (
        set "PATH=%%~dpp;!PATH!"
        goto :php_path_found
    )
    :php_path_found
)
where php >nul 2>&1
if %errorlevel% equ 0 (
    for /f %%v in ('php -r "echo PHP_VERSION;"') do echo [OK] PHP installe: %%v
) else (
    echo [!] PHP installe mais necessite un redemarrage du terminal
)

:: Composer
where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo [ ] Installation de Composer...
    winget install --id Composer.Composer --accept-source-agreements --accept-package-agreements -e >nul 2>&1
    if !errorlevel! neq 0 (
        echo [X] Echec de l'installation de Composer
        set "ERRORS=1"
        goto :eof
    )
    :: Ajouter Composer au PATH de la session courante
    set "PATH=C:\ProgramData\ComposerSetup\bin;%APPDATA%\Composer\vendor\bin;!PATH!"
)
echo [OK] Composer disponible

:: Node.js
where node >nul 2>&1
if %errorlevel% neq 0 (
    echo [ ] Installation de Node.js 20...
    winget install --id OpenJS.NodeJS.LTS --accept-source-agreements --accept-package-agreements -e >nul 2>&1
    if !errorlevel! neq 0 (
        echo [X] Echec de l'installation de Node.js
        set "ERRORS=1"
        goto :eof
    )
    :: Ajouter Node.js au PATH de la session courante
    set "PATH=C:\Program Files\nodejs;!PATH!"
)
where node >nul 2>&1
if %errorlevel% equ 0 (
    for /f %%v in ('node -v') do echo [OK] Node.js installe: %%v
) else (
    echo [!] Node.js installe mais necessite un redemarrage du terminal
)

:: pnpm
where pnpm >nul 2>&1
if %errorlevel% neq 0 (
    echo [ ] Installation de pnpm...
    call npm install -g pnpm >nul 2>&1
)
where pnpm >nul 2>&1
if %errorlevel% equ 0 (
    for /f %%v in ('pnpm -v') do echo [OK] pnpm installe: %%v
) else (
    echo [!] pnpm installe mais necessite un redemarrage du terminal
)

:: MariaDB
where mysql >nul 2>&1
if %errorlevel% neq 0 (
    where mariadb >nul 2>&1
    if !errorlevel! neq 0 (
        echo [ ] Installation de MariaDB...
        winget install --id MariaDB.Server --accept-source-agreements --accept-package-agreements -e >nul 2>&1
        if !errorlevel! neq 0 (
            echo [X] Echec de l'installation de MariaDB
            set "ERRORS=1"
            goto :eof
        )
        :: Ajouter MariaDB au PATH de la session courante
        set "PATH=C:\Program Files\MariaDB 11.2\bin;C:\Program Files\MariaDB\bin;!PATH!"
        echo [OK] MariaDB installe
    ) else (
        echo [OK] MariaDB deja installe
    )
) else (
    echo [OK] MariaDB deja installe
)

echo.
echo [OK] Prerequis verifies
goto :eof

:: ── Étape 2 : Téléchargement et extraction ──────────────────
:download_and_extract
echo.
echo [Etape 2/%TOTAL_STEPS%] Telechargement de PGFE
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

if exist "%INSTALL_DIR%" (
    echo [!] Le repertoire %INSTALL_DIR% existe deja.
    set /p "CONFIRM=Supprimer et continuer? (o/n) "
    if /i "!CONFIRM!" equ "o" (
        rmdir /s /q "%INSTALL_DIR%"
    ) else (
        echo [X] Installation annulee
        set "ERRORS=1"
        goto :eof
    )
)

:: Récupération de la dernière release via l'API GitHub
echo [ ] Detection de la derniere version...
set "TMP_JSON=%TEMP%\pgfe_release.json"
powershell -NoProfile -Command ^
    "try { $r = Invoke-RestMethod 'https://api.github.com/repos/%REPO_OWNER%/%REPO_NAME%/releases?per_page=1'; ^
    $r | ConvertTo-Json -Depth 10 | Out-File '%TMP_JSON%' -Encoding UTF8 } catch { exit 1 }"
if %errorlevel% neq 0 (
    echo [X] Impossible de contacter l'API GitHub
    set "ERRORS=1"
    goto :eof
)

:: Extraction de l'URL de téléchargement (.zip asset ou zipball)
for /f "usebackq delims=" %%u in (`powershell -NoProfile -Command ^
    "$j = Get-Content '%TMP_JSON%' | ConvertFrom-Json; ^
    $asset = $j[0].assets | Where-Object { $_.name -like '*.zip' } | Select-Object -First 1; ^
    if ($asset) { $asset.browser_download_url } else { $j[0].zipball_url }"`) do set "DOWNLOAD_URL=%%u"

if "%DOWNLOAD_URL%"=="" (
    echo [X] Impossible de detecter la derniere release.
    set "ERRORS=1"
    goto :eof
)
echo [OK] Derniere release: %DOWNLOAD_URL%

:: Téléchargement
set "TMP_ZIP=%TEMP%\pgfe_download.zip"
echo [ ] Telechargement...
powershell -NoProfile -Command ^
    "Invoke-WebRequest '%DOWNLOAD_URL%' -OutFile '%TMP_ZIP%' -UseBasicParsing"
if %errorlevel% neq 0 (
    echo [X] Echec du telechargement
    set "ERRORS=1"
    goto :eof
)
echo [OK] Telechargement termine

:: Extraction
set "TMP_EXTRACT=%TEMP%\pgfe_extract"
if exist "%TMP_EXTRACT%" rmdir /s /q "%TMP_EXTRACT%"
echo [ ] Extraction...
powershell -NoProfile -Command ^
    "Expand-Archive -Path '%TMP_ZIP%' -DestinationPath '%TMP_EXTRACT%' -Force"
if %errorlevel% neq 0 (
    echo [X] Echec de l'extraction
    set "ERRORS=1"
    goto :eof
)

:: Déplacer le sous-dossier extrait vers INSTALL_DIR
for /d %%d in ("%TMP_EXTRACT%\*") do (
    move "%%d" "%INSTALL_DIR%" >nul
    goto :extract_done
)
:extract_done

:: Nettoyage
del /f /q "%TMP_ZIP%" >nul 2>&1
del /f /q "%TMP_JSON%" >nul 2>&1
rmdir /s /q "%TMP_EXTRACT%" >nul 2>&1

echo [OK] Extraction terminee dans %INSTALL_DIR%
goto :eof

:: ── Étape 3 : Backend Laravel ────────────────────────────────
:install_backend
echo.
echo [Etape 3/%TOTAL_STEPS%] Installation du Backend (Laravel)
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

pushd "%INSTALL_DIR%\backend"

echo [ ] Installation des dependances Composer...
call composer install --no-interaction --prefer-dist --optimize-autoloader --quiet
if %errorlevel% neq 0 (
    echo [X] Echec de l'installation Composer
    popd
    set "ERRORS=1"
    goto :eof
)
echo [OK] Dependances Composer installees

:: Création et configuration du .env
if not exist ".env" (
    if not exist ".env.example" (
        echo [X] Fichier .env.example manquant dans le backend
        popd
        set "ERRORS=1"
        goto :eof
    )
    copy .env.example .env >nul

    powershell -NoProfile -Command ^
        "$c = Get-Content '.env' -Raw; ^
        $c = $c -replace 'DB_DATABASE=.*', 'DB_DATABASE=%DB_NAME%'; ^
        $c = $c -replace 'DB_USERNAME=.*', 'DB_USERNAME=%DB_USER%'; ^
        $c = $c -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=%DB_PASSWORD%'; ^
        $c = $c -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=mysql'; ^
        $c = $c -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1'; ^
        $c = $c -replace 'DB_PORT=.*', 'DB_PORT=3306'; ^
        Set-Content '.env' $c"
    echo [OK] Fichier .env configure
)

php artisan key:generate --force >nul
php artisan storage:link >nul 2>&1

echo [OK] Backend configure
popd
goto :eof

:: ── Étape 4 : Frontend Vue.js ───────────────────────────────
:install_frontend
echo.
echo [Etape 4/%TOTAL_STEPS%] Installation du Frontend (Vue.js)
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

pushd "%INSTALL_DIR%\frontend"

echo [ ] Installation des dependances frontend...
call pnpm install --silent >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] Echec de l'installation des dependances frontend
    popd
    set "ERRORS=1"
    goto :eof
)
echo [OK] Dependances frontend installees

popd
goto :eof

:: ── Étape 5 : Base de données MariaDB ───────────────────────
:setup_database
echo.
echo [Etape 5/%TOTAL_STEPS%] Configuration de la base de donnees
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

:: Tentative de connexion automatique à MariaDB
mysql -u root -e "SELECT 1;" >nul 2>&1
if %errorlevel% equ 0 (
    echo [ ] Creation de la base de donnees...
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -u root -e "CREATE USER IF NOT EXISTS '%DB_USER%'@'localhost' IDENTIFIED BY '%DB_PASSWORD%';"
    mysql -u root -e "GRANT ALL PRIVILEGES ON %DB_NAME%.* TO '%DB_USER%'@'localhost';"
    mysql -u root -e "FLUSH PRIVILEGES;"
    echo [OK] Base de donnees creee
) else (
    echo [!] Impossible de se connecter automatiquement a MariaDB
    echo.
    echo     Executez manuellement ces commandes SQL:
    echo.
    echo       mysql -u root -p
    echo       CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    echo       CREATE USER '%DB_USER%'@'localhost' IDENTIFIED BY '%DB_PASSWORD%';
    echo       GRANT ALL PRIVILEGES ON %DB_NAME%.* TO '%DB_USER%'@'localhost';
    echo       FLUSH PRIVILEGES;
    echo       EXIT;
    echo.
    pause
)

:: Création des tables applicatives
pushd "%INSTALL_DIR%\backend"
echo [ ] Execution des migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo [X] Echec des migrations
    popd
    set "ERRORS=1"
    goto :eof
)
echo [OK] Migrations executees
popd
goto :eof

:: ── Étape 6 : Scripts de démarrage/arrêt ────────────────────
:create_start_scripts
echo.
echo [Etape 6/%TOTAL_STEPS%] Creation des scripts de demarrage
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

:: start.cmd
(
echo @echo off
echo setlocal
echo set "SCRIPT_DIR=%%~dp0"
echo.
echo echo Demarrage de PGFE...
echo echo.
echo.
echo echo Demarrage du backend...
echo cd /d "%%SCRIPT_DIR%%backend"
echo start /b "" php artisan serve --host=0.0.0.0 --port=8000 ^> "%%TEMP%%\pgfe-backend.log" 2^>^&1
echo echo    Backend demarre — http://localhost:8000
echo.
echo echo Demarrage du frontend...
echo cd /d "%%SCRIPT_DIR%%frontend"
echo start /b "" cmd /c "pnpm dev --host --open ^> "%%TEMP%%\pgfe-frontend.log" 2^>^&1"
echo echo    Frontend demarre — http://localhost:5173
echo.
echo cd /d "%%SCRIPT_DIR%%"
echo echo.
echo echo PGFE est demarre!
echo echo.
echo echo Acces:
echo echo    Frontend: http://localhost:5173
echo echo    Backend:  http://localhost:8000
echo echo.
echo echo Pour arreter: stop.cmd
echo.
echo pause
) > "%INSTALL_DIR%\start.cmd"

:: stop.cmd
(
echo @echo off
echo echo Arret de PGFE...
echo.
echo taskkill /f /fi "WINDOWTITLE eq php artisan serve*" ^>nul 2^>^&1
echo taskkill /f /im "php.exe" /fi "WINDOWTITLE eq *artisan*" ^>nul 2^>^&1
echo.
echo :: Arrêt de tous les processus node liés à Vite/PGFE
echo for /f "tokens=2" %%%%p in ^('tasklist /fi "IMAGENAME eq node.exe" /fo csv /nh 2^^^>nul'^) do ^(
echo     wmic process where "ProcessId=%%%%~p" get CommandLine 2^^^>nul ^| findstr /i "vite" ^^^>nul ^&^& taskkill /f /pid %%%%~p ^^^>nul 2^^^>^&1
echo ^)
echo.
echo echo PGFE arrete
echo pause
) > "%INSTALL_DIR%\stop.cmd"

echo [OK] Scripts crees: start.cmd et stop.cmd
goto :eof

:: ── Génération du fichier d'informations ─────────────────────
:create_info_file
(
echo ═══════════════════════════════════════════════════════════════
echo   PGFE — Informations d'installation
echo   Date: %date% %time:~0,8%
echo ═══════════════════════════════════════════════════════════════
echo.
echo   Repertoire: %CD%\%INSTALL_DIR%
echo.
echo   Base de donnees:
echo     Nom:            %DB_NAME%
echo     Utilisateur:    %DB_USER%
echo     Mot de passe:   %DB_PASSWORD%
echo     Host:           127.0.0.1:3306
echo.
echo   Demarrage:     cd %INSTALL_DIR% ^& start.cmd
echo   Arret:         cd %INSTALL_DIR% ^& stop.cmd
echo.
echo   URLs:
echo     Frontend:  http://localhost:%FRONTEND_PORT%
echo     Backend:   http://localhost:%APP_PORT%
echo.
echo   Commandes utiles:
echo     php artisan migrate:fresh    - Reinitialiser les tables
echo     php artisan cache:clear      - Vider le cache
echo     php artisan config:clear     - Vider la config
echo.
echo   Logs:
echo     type %%TEMP%%\pgfe-backend.log
echo     type %%TEMP%%\pgfe-frontend.log
echo.
echo   Sauvegardez ce fichier — il contient vos identifiants.
echo ═══════════════════════════════════════════════════════════════
) > "%INSTALL_DIR%\INSTALLATION_INFO.txt"

echo [OK] Fichier d'informations cree: INSTALLATION_INFO.txt
goto :eof
