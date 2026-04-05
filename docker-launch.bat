@echo off
REM ── PGFE Docker Launch Script pour Windows ──
REM Gère le démarrage des services en mode développement ou production

setlocal enabledelayedexpansion

REM ── Configuration ──
set MODE=%1
if "%MODE%"=="" set MODE=prod

set COMMAND=%2
if "%COMMAND%"=="" set COMMAND=up

REM ── Validation du MODE ──
if /i "%MODE%"=="prod" goto mode_ok
if /i "%MODE%"=="dev" goto mode_ok
if /i "%MODE%"=="help" goto show_help
if /i "%MODE%"=="-h" goto show_help
if /i "%MODE%"=="--help" goto show_help
echo ❌ Mode invalide: %MODE%
echo Modes accepts: prod, dev
echo.
goto show_help

:mode_ok
if /i "%MODE%"=="prod" set MODE=prod
if /i "%MODE%"=="dev" set MODE=dev

REM ── Vérifier .env ──
if not exist ".env" (
    echo ⚠️  .env not found, creating from .env.example...
    if exist ".env.example" (
        copy .env.example .env
        echo ✓ .env created
    ) else (
        echo ❌ .env.example not found!
        exit /b 1
    )
)

REM ── Vérifier Docker ──
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not installed or not in PATH
    exit /b 1
)

echo.
call :log_info "Starting services in %MODE% mode..."
echo.

REM ── Compiler la commande docker compose appropriée ──
if /i "%MODE%"=="dev" (
    set "COMPOSE_FILES=-f docker-compose.yml -f docker-compose.dev.yml"
    set "COMPOSE_CMD=docker compose %COMPOSE_FILES%"
) else (
    set "COMPOSE_CMD=docker compose"
)

REM ── Exécuter la commande ──
goto cmd_%COMMAND%

:cmd_up
    echo 🚀 Starting services (mode: %MODE%)...
    %COMPOSE_CMD% up -d
    call :log_success "Services started"
    echo.
    echo Waiting for healthchecks to pass...
    timeout /t 3 /nobreak
    %COMPOSE_CMD% ps
    echo.
    call :show_info
    goto :eof

:cmd_up-debug
    echo 🚀 Starting services in debug mode (logs in foreground)...
    %COMPOSE_CMD% up
    goto :eof

:cmd_down
    echo 🛑 Stopping services...
    %COMPOSE_CMD% down
    call :log_success "Services stopped"
    goto :eof

:cmd_restart
    echo 🔄 Restarting services...
    %COMPOSE_CMD% restart
    call :log_success "Services restarted"
    goto :eof

:cmd_logs
    %COMPOSE_CMD% logs --tail=50
    goto :eof

:cmd_logs-f
    call :log_info "Displaying logs (Ctrl+C to exit)..."
    %COMPOSE_CMD% logs -f
    goto :eof

:cmd_ps
    %COMPOSE_CMD% ps
    goto :eof

:cmd_exec-api
    call :log_info "Bash access to backend Laravel..."
    %COMPOSE_CMD% exec backend bash
    goto :eof

:cmd_exec-db
    call :log_info "MySQL access..."
    for /f "tokens=*" %%a in ('findstr /r "^DB_PASSWORD=" .env') do (
        set "%%a"
    )
    %COMPOSE_CMD% exec database mysql -u pgfe_user -p%DB_PASSWORD% pgfe_db
    goto :eof

:cmd_help
    goto show_help

:invalid_cmd
    echo ❌ Invalid command: %COMMAND%
    echo.
    goto show_help

:show_help
    echo.
    echo ╔════════════════════════════════════════════════════════════════╗
    echo ║          PGFE Docker Compose Launch Script (Windows)           ║
    echo ╚════════════════════════════════════════════════════════════════╝
    echo.
    echo USAGE:
    echo     docker-launch.bat [MODE] [COMMAND]
    echo.
    echo MODES:
    echo     prod        Production (immutable images, no volumes)
    echo     dev         Development (mounted volumes, debug enabled)
    echo.
    echo COMMANDS:
    echo     up          Start services in background (default)
    echo     up-debug    Start with logs in foreground
    echo     down        Stop services
    echo     restart     Restart services
    echo     logs        Show last 50 lines
    echo     logs-f      Show continuous logs
    echo     ps          Show container status
    echo     exec-api    Bash access to backend
    echo     exec-db     MySQL CLI access
    echo     help        Show this help message
    echo.
    echo EXAMPLES:
    echo     docker-launch.bat prod up
    echo     docker-launch.bat dev up
    echo     docker-launch.bat dev logs-f
    echo.
    echo INFO AFTER START:
    echo     Frontend:   http://localhost (port 80)
    echo     API:        http://localhost:8400/api (port 8400)
    echo     Database:   localhost:3306
    echo.
    exit /b 0

:log_info
    setlocal
    set "msg=%~1"
    echo [INFO] %msg%
    endlocal
    goto :eof

:log_success
    setlocal
    set "msg=%~1"
    echo [OK] %msg%
    endlocal
    goto :eof

:show_info
    echo.
    echo ╔════════════════════════════════════════════════════════════════╗
    echo ║         PGFE Applications started successfully                ║
    echo ╚════════════════════════════════════════════════════════════════╝
    echo.
    echo Access services on:
    echo     🌐 Frontend:  http://localhost
    echo     🔧 API:       http://localhost:8400/api
    echo     🗄️  Database:  localhost:3306
    echo.
    echo View logs:
    echo     docker-launch.bat %MODE% logs-f
    echo.
    echo Stop services:
    echo     docker-launch.bat %MODE% down
    echo.
    goto :eof

REM ── Si on arrive ici avec une commande non reconnue ──
goto invalid_cmd
