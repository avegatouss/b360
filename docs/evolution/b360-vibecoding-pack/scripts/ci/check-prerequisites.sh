#!/usr/bin/env bash
# scripts/ci/check-prerequisites.sh
# Vérifie que les prérequis du pack sont disponibles.

set -euo pipefail

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

OK=0
KO=0

check() {
    local name="$1"
    local cmd="$2"
    local min_version="${3:-}"

    if command -v "$cmd" >/dev/null 2>&1; then
        local version
        version=$($cmd --version 2>&1 | head -n 1 || echo "version inconnue")
        echo -e "${GREEN}[OK]${NC} $name : $version"
        OK=$((OK + 1))
    else
        echo -e "${RED}[KO]${NC} $name absent (commande : $cmd)"
        [ -n "$min_version" ] && echo "      Minimum requis : $min_version"
        KO=$((KO + 1))
    fi
}

echo "════════════════════════════════════════════════════════════════"
echo " Vérification des prérequis B360 vibecoding pack"
echo "════════════════════════════════════════════════════════════════"
echo ""

check "PHP"          php       "8.2"
check "Composer"     composer  "2.x"
check "Git"          git       "2.40"
check "Node.js"      node      "20"
check "npm"          npm       ""
check "make"         make      ""
check "bash"         bash      "5.0"

echo ""

# Extensions PHP critiques
echo "Extensions PHP critiques :"
for ext in bcmath ctype curl dom fileinfo gd intl json mbstring mysql openssl pdo pdo_mysql redis tokenizer xml zip; do
    if php -m | grep -qi "^$ext$"; then
        echo -e "  ${GREEN}✓${NC} $ext"
    else
        echo -e "  ${RED}✗${NC} $ext (requise)"
        KO=$((KO + 1))
    fi
done

echo ""

# Optionnels mais recommandés
echo "Outils optionnels (recommandés) :"
for cmd in xdebug-config commitlint mkdocs graphviz; do
    if command -v "$cmd" >/dev/null 2>&1; then
        echo -e "  ${GREEN}✓${NC} $cmd"
    else
        echo -e "  ${YELLOW}~${NC} $cmd (absent — optionnel)"
    fi
done

echo ""
echo "════════════════════════════════════════════════════════════════"

if [ "$KO" -gt 0 ]; then
    echo -e "${RED}✗ $KO prérequis manquant(s).${NC}"
    echo ""
    echo "Installation suggérée (Ubuntu/Debian) :"
    echo "  sudo apt update"
    echo "  sudo apt install -y php8.2-cli php8.2-{bcmath,ctype,curl,dom,fileinfo,gd,intl,json,mbstring,mysql,openssl,pdo,redis,tokenizer,xml,zip}"
    echo "  curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer"
    echo "  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash - && sudo apt install -y nodejs"
    echo ""
    echo "Sur macOS :"
    echo "  brew install php@8.2 composer node"
    exit 1
fi

echo -e "${GREEN}✓ Tous les prérequis sont satisfaits.${NC}"
exit 0
