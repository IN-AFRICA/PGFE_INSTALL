#!/bin/bash
set -e


REPO_URL="https://github.com/IN-AFRICA/PGFE_INSTALL"
DOWNLOAD_URL="${REPO_URL}/archive/refs/heads/main.zip"
INSTALL_DIR="pgfe"
DB_NAME="pgfe_db"
DB_USER="pgfe_user"
DB_PASSWORD="pgfe_$(openssl rand -hex 8)"
APP_PORT=8000
FRONTEND_PORT=5173

# Couleurs
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
print_error() { echo -e "${RED}✗ $1${NC}"; exit 1; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }

check_ubuntu() {
    print_header "Vérification du système"
    
    if [ ! -f /etc/os-release ]; then
        print_error "Impossible de détecter l'OS"
    fi
    
    . /etc/os-release
    print_info "OS détecté: $NAME $VERSION"
    
    if [[ ! "$ID" =~ ^(ubuntu|debian)$ ]]; then
        print_warning "Ce script est optimisé pour Ubuntu/Debian"
        read -p "Continuer quand même? (o/n) " -n 1 -r
        echo
        [[ ! $REPLY =~ ^[Oo]$ ]] && exit 0
    fi
}

install_prerequisites() {
    print_header "Installation des prérequis"
    
    print_info "Mise à jour des paquets..."
    sudo apt update -qq
    
    print_info "Installation des outils de base..."
    sudo apt install -y -qq curl wget unzip git ca-certificates gnupg lsb-release > /dev/null 2>&1
    
    # PHP
    print_info "Vérification de PHP..."
    if ! command -v php &> /dev/null; then
        print_info "Installation de PHP 8.2..."
        sudo apt install -y -qq software-properties-common
        sudo add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
        sudo apt update -qq
        sudo apt install -y -qq php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
            php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
            php8.2-intl php8.2-bcmath php8.2-tokenizer > /dev/null 2>&1
    fi
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    print_success "PHP installé: $PHP_VERSION"
    
    # Composer
    print_info "Vérification de Composer..."
    if ! command -v composer &> /dev/null; then
        print_info "Installation de Composer..."
        curl -sS https://getcomposer.org/installer | php > /dev/null 2>&1
        sudo mv composer.phar /usr/local/bin/composer
        sudo chmod +x /usr/local/bin/composer
    fi
    print_success "Composer installé: $(composer --version --no-ansi | head -1 | cut -d' ' -f3)"
    
    # Node
    print_info "Vérification de Node.js..."
    if ! command -v node &> /dev/null; then
        print_info "Installation de Node.js 20..."
        curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - > /dev/null 2>&1
        sudo apt install -y -qq nodejs > /dev/null 2>&1
    fi
    print_success "Node.js installé: $(node -v)"
    
    # pnpm
    print_info "Vérification de pnpm..."
    if ! command -v pnpm &> /dev/null; then
        print_info "Installation de pnpm..."
        sudo npm install -g pnpm > /dev/null 2>&1
    fi
    print_success "pnpm installé: $(pnpm -v)"
    
    # MariaDB
    print_info "Vérification de MariaDB..."
    if ! command -v mysql &> /dev/null && ! command -v mariadb &> /dev/null; then
        print_info "Installation de MariaDB..."
        sudo apt install -y -qq mariadb-server mariadb-client > /dev/null 2>&1
        sudo systemctl start mariadb
        sudo systemctl enable mariadb > /dev/null 2>&1
        print_success "MariaDB installé"
    else
        print_success "MariaDB déjà installé"
    fi
}

download_and_extract() {
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
    
    print_info "Téléchargement depuis GitHub..."
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

install_backend() {
    print_header "Installation du Backend (Laravel)"
    
    cd "$INSTALL_DIR/backend"
    
    print_info "Installation des dépendances Composer (peut prendre quelques minutes)..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --quiet
    print_success "Dépendances Composer installées"
    

    if [ ! -f ".env" ]; then
        print_info "Création du fichier .env..."
        cp .env.example .env
        
        # Configuration automatique de la base de données
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
        sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
        sed -i "s/DB_HOST=.*/DB_HOST=127.0.0.1/" .env
        sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
        
        print_success "Fichier .env configuré"
    fi
    
    print_info "Génération de la clé d'application..."
    php artisan key:generate --force > /dev/null
    
    print_info "Création du lien storage..."
    php artisan storage:link > /dev/null 2>&1 || true
    
    print_info "Configuration des permissions..."
    chmod -R 775 storage bootstrap/cache
    
    print_success "Backend configuré"
    
    cd ../..
}

install_frontend() {
    print_header "Installation du Frontend (Vue.js)"
    
    cd "$INSTALL_DIR/frontend"
    
    print_info "Installation des dépendances pnpm (peut prendre quelques minutes)..."
    pnpm install --silent > /dev/null 2>&1
    print_success "Dépendances frontend installées"
    
    cd ../..
}

setup_database() {
    print_header "Configuration de la base de données"
    
    print_info "Création de la base de données MariaDB..."
    

    if sudo mysql -e "SELECT 1;" > /dev/null 2>&1; then
        sudo mysql <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
        print_success "Base de données créée"
    else
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
    
    print_info "Exécution des migrations..."
    cd "$INSTALL_DIR/backend"
    php artisan migrate --force
    print_success "Migrations exécutées"
    
    read -p "Exécuter les seeders (données de test)? (o/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Oo]$ ]]; then
        print_info "Exécution des seeders..."
        php artisan db:seed --force
        print_success "Base de données peuplée"
    fi
    
    cd ../..
}

create_start_scripts() {
    print_header "Création des scripts de démarrage"
    

    cat > "$INSTALL_DIR/start.sh" << 'EOFSTART'
#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "🚀 Démarrage de PGFE..."
echo ""

# Backend
echo "📦 Démarrage du backend..."
cd "$SCRIPT_DIR/backend"
php artisan serve --host=0.0.0.0 --port=8000 > /tmp/pgfe-backend.log 2>&1 &
BACKEND_PID=$!
echo $BACKEND_PID > /tmp/pgfe-backend.pid
echo "   Backend démarré (PID: $BACKEND_PID)"
echo "   URL: http://localhost:8000"

# Frontend
echo "📦 Démarrage du frontend..."
cd "$SCRIPT_DIR/frontend"
pnpm dev --host > /tmp/pgfe-frontend.log 2>&1 &
FRONTEND_PID=$!
echo $FRONTEND_PID > /tmp/pgfe-frontend.pid
echo "   Frontend démarré (PID: $FRONTEND_PID)"
echo "   URL: http://localhost:5173"

cd "$SCRIPT_DIR"

echo ""
echo "✅ PGFE est démarré!"
echo ""
echo "📊 Accès:"
echo "   Frontend: http://localhost:5173"
echo "   Backend:  http://localhost:8000"
echo "   API Docs: http://localhost:8000/docs/api"
echo ""
echo "🔑 Connexion par défaut:"
echo "   Email: admin@example.com"
echo "   Mot de passe: password"
echo ""
echo "🛑 Pour arrêter: ./stop.sh"
EOFSTART
    

    cat > "$INSTALL_DIR/stop.sh" << 'EOFSTOP'
#!/bin/bash

echo "🛑 Arrêt de PGFE..."

if [ -f /tmp/pgfe-backend.pid ]; then
    BACKEND_PID=$(cat /tmp/pgfe-backend.pid)
    if ps -p $BACKEND_PID > /dev/null; then
        kill $BACKEND_PID
        echo "   Backend arrêté"
    fi
    rm -f /tmp/pgfe-backend.pid
fi

if [ -f /tmp/pgfe-frontend.pid ]; then
    FRONTEND_PID=$(cat /tmp/pgfe-frontend.pid)
    if ps -p $FRONTEND_PID > /dev/null; then
        kill $FRONTEND_PID
        echo "   Frontend arrêté"
    fi
    rm -f /tmp/pgfe-frontend.pid
fi

# Nettoyer les processus Node et PHP-FPM de PGFE
pkill -f "php artisan serve" 2>/dev/null || true
pkill -f "vite.*pgfe" 2>/dev/null || true

echo "✅ PGFE arrêté"
EOFSTOP
    
    chmod +x "$INSTALL_DIR/start.sh" "$INSTALL_DIR/stop.sh"
    print_success "Scripts créés: start.sh et stop.sh"
}

create_info_file() {
    cat > "$INSTALL_DIR/INSTALLATION_INFO.txt" << EOF
═══════════════════════════════════════════════════════════════
  PGFE - Informations d'installation
  Date: $(date '+%Y-%m-%d %H:%M:%S')
═══════════════════════════════════════════════════════════════

📁 Répertoire d'installation:
   $(pwd)/$INSTALL_DIR

🗄️ Base de données:
   Nom: $DB_NAME
   Utilisateur: $DB_USER
   Mot de passe: $DB_PASSWORD
   Host: localhost (127.0.0.1)
   Port: 3306

🚀 Démarrage:
   cd $INSTALL_DIR
   ./start.sh

🛑 Arrêt:
   cd $INSTALL_DIR
   ./stop.sh

📊 URLs:
   Frontend: http://localhost:$FRONTEND_PORT
   Backend:  http://localhost:$APP_PORT
   API Docs: http://localhost:$APP_PORT/docs/api

🔑 Compte par défaut (après seeding):
   Email: admin@example.com
   Mot de passe: password

📝 Commandes utiles:

   # Réinitialiser la base
   cd $INSTALL_DIR/backend
   php artisan migrate:fresh --seed

   # Vider les caches
   php artisan cache:clear
   php artisan config:clear

   # Voir les logs
   tail -f /tmp/pgfe-backend.log
   tail -f /tmp/pgfe-frontend.log

📚 Documentation:
   - README.md
   - UBUNTU-INSTALL.md
   - DOCKER-README.md

⚠️ IMPORTANT:
   Sauvegardez ce fichier! Il contient vos identifiants de base de données.

═══════════════════════════════════════════════════════════════
EOF
    
    print_success "Fichier d'informations créé: INSTALLATION_INFO.txt"
}

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
║   Installation simplifiée pour Ubuntu Server                 ║
║   Sans Docker | MariaDB                                      ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    check_ubuntu
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
    echo "📄 Informations importantes sauvegardées dans:"
    echo "   $INSTALL_DIR/INSTALLATION_INFO.txt"
    echo ""
    echo -e "${YELLOW}⚠️  Sauvegardez vos identifiants de base de données:${NC}"
    echo "   Base: $DB_NAME"
    echo "   User: $DB_USER"
    echo "   Pass: $DB_PASSWORD"
    echo ""
}

main "$@"
