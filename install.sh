#!/bin/bash
# ─────────────────────────────────────────────────────────────
# PGFE — Script d'installation pour Debian/Ubuntu
#
# Télécharge la dernière release, installe les prérequis,
# configure le backend Laravel, le frontend Vue.js,
# la base MariaDB et génère les scripts start/stop.
# ─────────────────────────────────────────────────────────────
set -e

# ── Configuration ────────────────────────────────────────────
REPO_OWNER="IN-AFRICA"
REPO_NAME="PGFE_INSTALL"
INSTALL_DIR="pgfe"
DB_NAME="pgfe_db"
DB_USER="pgfe_user"
DB_PASSWORD="pgfe_$(openssl rand -hex 8)"
APP_PORT=8000
FRONTEND_PORT=5173

# ── Couleurs et affichage ────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error()   { echo -e "${RED}✗ $1${NC}"; exit 1; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info()    { echo -e "${BLUE}ℹ $1${NC}"; }

# Exécute une commande en arrière-plan avec un spinner animé.
# Usage : run_spinner "message" commande [args...]
run_spinner() {
    local msg="$1"; shift
    local spin='⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏'
    local i=0
    "$@" &
    local pid=$!
    echo -ne "${BLUE}  ${spin:0:1} ${msg}...${NC}"
    while kill -0 "$pid" 2>/dev/null; do
        i=$(( (i+1) % ${#spin} ))
        echo -ne "\r${BLUE}  ${spin:$i:1} ${msg}...${NC}"
        sleep 0.1
    done
    wait "$pid"
    local code=$?
    if [ $code -eq 0 ]; then
        echo -e "\r${GREEN}  ✓ ${msg}${NC}          "
    else
        echo -e "\r${RED}  ✗ ${msg} (erreur)${NC}"
        exit $code
    fi
}

TOTAL_STEPS=6
print_step() { echo -e "\n${BLUE}[▶ Étape $1/$TOTAL_STEPS]${NC} $2"; }

# ── Étape 1 : Prérequis système ─────────────────────────────
install_prerequisites() {
    print_step 1 "Installation des prérequis"
    print_header "Installation des prérequis"

    run_spinner "Mise à jour des paquets" sudo apt update -qq
    run_spinner "Installation des outils de base" sudo apt install -y -qq curl wget unzip git ca-certificates gnupg lsb-release

    if ! command -v php &> /dev/null; then
        run_spinner "Ajout du dépôt PHP (ondrej/php)" bash -c 'sudo apt install -y -qq software-properties-common && sudo add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1 && sudo apt update -qq'
        run_spinner "Installation de PHP 8.2" sudo apt install -y -qq php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath php8.2-tokenizer
    fi
    print_success "PHP installé: $(php -r 'echo PHP_VERSION;')"

    if ! command -v composer &> /dev/null; then
        run_spinner "Installation de Composer" bash -c 'curl -sS https://getcomposer.org/installer | php > /dev/null 2>&1 && sudo mv composer.phar /usr/local/bin/composer && sudo chmod +x /usr/local/bin/composer'
    fi
    print_success "Composer installé: $(composer --version --no-ansi | head -1 | cut -d' ' -f3)"

    if ! command -v node &> /dev/null; then
        run_spinner "Installation de Node.js 20" bash -c 'curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - > /dev/null 2>&1 && sudo apt install -y -qq nodejs > /dev/null 2>&1'
    fi
    print_success "Node.js installé: $(node -v)"

    if ! command -v pnpm &> /dev/null; then
        run_spinner "Installation de pnpm" sudo npm install -g pnpm
    fi
    print_success "pnpm installé: $(pnpm -v)"

    if ! command -v mysql &> /dev/null && ! command -v mariadb &> /dev/null; then
        run_spinner "Installation de MariaDB" sudo apt install -y -qq mariadb-server mariadb-client
        sudo systemctl start mariadb
        sudo systemctl enable mariadb > /dev/null 2>&1
        print_success "MariaDB installé"
    else
        print_success "MariaDB déjà installé"
    fi
}

# ── Étape 2 : Téléchargement et extraction ──────────────────
download_and_extract() {
    print_step 2 "Téléchargement de PGFE"
    print_header "Téléchargement de PGFE"

    if [ -d "$INSTALL_DIR" ]; then
        print_warning "Le répertoire $INSTALL_DIR existe déjà"
        read -p "Supprimer et continuer? (o/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Oo]$ ]]; then
            rm -rf "$INSTALL_DIR"
        else
            print_error "Installation annulée"
        fi
    fi

    # Récupération de la dernière release via l'API GitHub
    print_info "Détection de la dernière version (Dépôt: $REPO_OWNER/$REPO_NAME)..."
    LATEST_REL_JSON=$(curl -s "https://api.github.com/repos/$REPO_OWNER/$REPO_NAME/releases?per_page=1")
    DOWNLOAD_URL=$(echo "$LATEST_REL_JSON" | grep -o 'browser_download_url": *"[^"]*"' | grep '\.zip"' | head -n 1 | cut -d '"' -f 3)

    # Fallback sur zipball_url si aucun asset .zip attaché
    if [[ -z "$DOWNLOAD_URL" ]]; then
        DOWNLOAD_URL=$(echo "$LATEST_REL_JSON" | grep -o '"zipball_url": *"[^"]*"' | head -n 1 | cut -d '"' -f 4)
    fi

    [[ -z "$DOWNLOAD_URL" ]] && print_error "Impossible de détecter la dernière release. Vérifiez qu'une release existe sur le dépôt."

    print_info "Dernière release trouvée: $DOWNLOAD_URL"

    TMP_ZIP="/tmp/pgfe_$(date +%s).zip"

    if curl -L -# "$DOWNLOAD_URL" -o "$TMP_ZIP"; then
        print_success "Téléchargement terminé"
    else
        print_error "Échec du téléchargement"
    fi

    print_info "Extraction..."
    unzip -q "$TMP_ZIP" -d /tmp/pgfe_extract
    EXTRACTED=$(find /tmp/pgfe_extract -mindepth 1 -maxdepth 1 -type d | head -1)
    mv "$EXTRACTED" "$INSTALL_DIR"
    rm -rf "$TMP_ZIP" /tmp/pgfe_extract

    print_success "Extraction terminée dans $INSTALL_DIR"
}

# ── Étape 3 : Backend Laravel ────────────────────────────────
install_backend() {
    print_step 3 "Installation du Backend (Laravel)"
    print_header "Installation du Backend (Laravel)"

    cd "$INSTALL_DIR/backend"

    run_spinner "Installation des dépendances Composer" composer install --no-interaction --prefer-dist --optimize-autoloader --quiet

    # Création et configuration du .env depuis le template
    if [ ! -f ".env" ]; then
        [ ! -f ".env.example" ] && print_error "Fichier .env.example manquant dans le backend"
        cp .env.example .env
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
        sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
        sed -i "s/DB_HOST=.*/DB_HOST=127.0.0.1/" .env
        sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
        print_success "Fichier .env configuré"
    fi

    php artisan key:generate --force > /dev/null
    php artisan storage:link > /dev/null 2>&1 || true
    chmod -R 775 storage bootstrap/cache

    print_success "Backend configuré"
    cd ../..
}

# ── Étape 4 : Frontend Vue.js ───────────────────────────────
install_frontend() {
    print_step 4 "Installation du Frontend (Vue.js)"
    print_header "Installation du Frontend (Vue.js)"

    cd "$INSTALL_DIR/frontend"
    run_spinner "Installation des dépendances frontend" pnpm install --silent
    print_success "Dépendances frontend installées"
    cd ../..
}

# ── Étape 5 : Base de données MariaDB ───────────────────────
setup_database() {
    print_step 5 "Configuration de la base de données"
    print_header "Configuration de la base de données"

    # Création automatique de la base et de l'utilisateur
    if sudo mysql -e "SELECT 1;" > /dev/null 2>&1; then
        sudo mysql <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
        print_success "Base de données créée"
    else
        # Fallback manuel si la connexion root échoue
        print_warning "Impossible de se connecter automatiquement à MariaDB"
        print_info "Exécutez manuellement ces commandes SQL:"
        echo ""
        echo "  sudo mysql -u root -p"
        echo "  CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        echo "  CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
        echo "  GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
        echo "  FLUSH PRIVILEGES;"
        echo "  EXIT;"
        echo ""
        read -p "Appuyez sur Entrée une fois la base créée..."
    fi

    # Création des tables applicatives
    cd "$INSTALL_DIR/backend"
    php artisan migrate --force
    print_success "Migrations exécutées"
    cd ../..
}

# ── Étape 6 : Scripts de démarrage/arrêt ────────────────────
create_start_scripts() {
    print_step 6 "Création des scripts de démarrage"
    print_header "Création des scripts de démarrage"

    cat > "$INSTALL_DIR/start.sh" << 'EOFSTART'
#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "🚀 Démarrage de PGFE..."
echo ""

cd "$SCRIPT_DIR/backend"
php artisan serve --host=0.0.0.0 --port=8000 > /tmp/pgfe-backend.log 2>&1 &
echo $! > /tmp/pgfe-backend.pid
echo "   Backend démarré (PID: $!) — http://localhost:8000"

cd "$SCRIPT_DIR/frontend"
pnpm dev --host --open > /tmp/pgfe-frontend.log 2>&1 &
echo $! > /tmp/pgfe-frontend.pid
echo "   Frontend démarré (PID: $!) — http://localhost:5173"

cd "$SCRIPT_DIR"

echo ""
echo "✅ PGFE est démarré!"
echo ""
echo "📊 Accès:"
echo "   Frontend: http://localhost:5173"
echo "   Backend:  http://localhost:8000"
echo ""
echo "🛑 Pour arrêter: ./stop.sh"
EOFSTART

    cat > "$INSTALL_DIR/stop.sh" << 'EOFSTOP'
#!/bin/bash
echo "🛑 Arrêt de PGFE..."

for pidfile in /tmp/pgfe-backend.pid /tmp/pgfe-frontend.pid; do
    if [ -f "$pidfile" ]; then
        pid=$(cat "$pidfile")
        ps -p "$pid" > /dev/null 2>&1 && kill "$pid"
        rm -f "$pidfile"
    fi
done

# Nettoyage des processus orphelins
pkill -f "php artisan serve" 2>/dev/null || true
pkill -f "vite.*pgfe" 2>/dev/null || true

echo "✅ PGFE arrêté"
EOFSTOP

    chmod +x "$INSTALL_DIR/start.sh" "$INSTALL_DIR/stop.sh"
    print_success "Scripts créés: start.sh et stop.sh"
}

# ── Génération du fichier d'informations ─────────────────────
create_info_file() {
    cat > "$INSTALL_DIR/INSTALLATION_INFO.txt" << EOF
═══════════════════════════════════════════════════════════════
  PGFE — Informations d'installation
  Date: $(date '+%Y-%m-%d %H:%M:%S')
═══════════════════════════════════════════════════════════════

📁 Répertoire: $(pwd)/$INSTALL_DIR

🗄️ Base de données:
   Nom:            $DB_NAME
   Utilisateur:    $DB_USER
   Mot de passe:   $DB_PASSWORD
   Host:           127.0.0.1:3306

🚀 Démarrage:     cd $INSTALL_DIR && ./start.sh
🛑 Arrêt:         cd $INSTALL_DIR && ./stop.sh

📊 URLs:
   Frontend:  http://localhost:$FRONTEND_PORT
   Backend:   http://localhost:$APP_PORT

📝 Commandes utiles:
   php artisan migrate:fresh    # Réinitialiser les tables
   php artisan cache:clear      # Vider le cache
   php artisan config:clear     # Vider la config

📚 Logs:
   tail -f /tmp/pgfe-backend.log
   tail -f /tmp/pgfe-frontend.log

⚠️ Sauvegardez ce fichier — il contient vos identifiants.
═══════════════════════════════════════════════════════════════
EOF

    print_success "Fichier d'informations créé: INSTALLATION_INFO.txt"
}

# ── Point d'entrée ───────────────────────────────────────────
main() {
    clear
    echo -e "${BLUE}"
    cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║   ██████╗  ██████╗ ███████╗███████╗                          ║
║   ██╔══██╗██╔════╝ ██╔════╝██╔════╝                          ║
║   ██████╔╝██║  ███╗█████╗  █████╗                            ║
║   ██╔═══╝ ██║   ██║██╔══╝  ██╔══╝                            ║
║   ██║     ╚██████╔╝██║     ███████╗                          ║
║   ╚═╝      ╚═════╝ ╚═╝     ╚══════╝                          ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"

    install_prerequisites
    download_and_extract
    install_backend
    install_frontend
    setup_database
    create_start_scripts
    create_info_file

    print_header "✅ Installation terminée!"

    echo -e "${GREEN}PGFE a été installé avec succès!${NC}\n"
    echo "📁 Répertoire: $(pwd)/$INSTALL_DIR"
    echo ""
    echo "🚀 Pour démarrer PGFE:"
    echo -e "   ${BLUE}cd $INSTALL_DIR${NC}"
    echo -e "   ${BLUE}./start.sh${NC}"
    echo ""
    echo -e "${YELLOW}⚠️  Sauvegardez vos identifiants de base de données:${NC}"
    echo "   Base: $DB_NAME"
    echo "   User: $DB_USER"
    echo "   Pass: $DB_PASSWORD"
    echo ""
}

main "$@"
