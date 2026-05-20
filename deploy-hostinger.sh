#!/bin/bash
# Script de déploiement ChurchCRM avec Traefik
# Usage: ./deploy-hostinger.sh

set -e

echo "=========================================="
echo "ChurchCRM - Déploiement avec Traefik"
echo "=========================================="

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérifications
echo -e "${YELLOW}[1/8] Vérification des prérequis...${NC}"

if ! command -v docker &> /dev/null; then
    echo -e "${RED}Erreur: Docker n'est pas installé${NC}"
    echo "Installez Docker: curl -sSL https://get.docker.com | sh"
    exit 1
fi

if ! command -v docker compose &> /dev/null; then
    echo -e "${RED}Erreur: Docker Compose n'est pas installé${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker et Docker Compose sont installés${NC}"

# Vérifier le réseau Traefik
echo -e "${YELLOW}[2/8] Vérification du réseau Traefik...${NC}"

if ! docker network ls | grep -q traefik-public; then
    echo -e "${YELLOW}Création du réseau traefik-public...${NC}"
    docker network create traefik-public --driver=bridge
fi

echo -e "${GREEN}✓ Réseau traefik-public prêt${NC}"

# Configuration de l'environnement
echo -e "${YELLOW}[3/8] Configuration de l'environnement...${NC}"

if [ ! -f "docker/.env.hostinger" ]; then
    echo -e "${RED}Erreur: docker/.env.hostinger n'existe pas${NC}"
    echo "Créez le fichier à partir de docker/.env.hostinger"
    exit 1
fi

# Vérifier que les mots de passe ont été changés
if grep -q "CHANGE_THIS" docker/.env.hostinger; then
    echo -e "${RED}Erreur: Veuillez modifier les mots de passe par défaut dans docker/.env.hostinger${NC}"
    exit 1
fi

cp docker/.env.hostinger .env

echo -e "${GREEN}✓ Environnement configuré${NC}"

# Build de l'image Docker
echo -e "${YELLOW}[4/8] Construction de l'image Docker...${NC}"
docker compose -f docker-compose.hostinger.yaml build webserver
echo -e "${GREEN}✓ Image construite${NC}"

# Arrêter les containers existants
echo -e "${YELLOW}[5/8] Arrêt des containers existants...${NC}"
docker compose -f docker-compose.hostinger.yaml down 2>/dev/null || true
echo -e "${GREEN}✓ Containers arrêtés${NC}"

# Démarrer les containers
echo -e "${YELLOW}[6/8] Démarrage des containers...${NC}"
docker compose -f docker-compose.hostinger.yaml up -d
echo -e "${GREEN}✓ Containers démarrés${NC}"

# Attendre que la base de données soit prête
echo -e "${YELLOW}[7/8] Attente de la base de données...${NC}"
for i in {1..30}; do
    if docker exec churchcrm-db mysqladmin ping -h localhost --silent; then
        echo -e "${GREEN}✓ Base de données prête${NC}"
        break
    fi
    echo "Attente... ($i/30)"
    sleep 2
done

# Permissions
echo -e "${YELLOW}[8/8] Configuration des permissions...${NC}"
docker exec churchcrm-web chown -R www-data:www-data /var/www/html/logs
docker exec churchcrm-web chmod -R 0775 /var/www/html/logs
echo -e "${GREEN}✓ Permissions configurées${NC}"

# Afficher l'état
echo ""
echo "=========================================="
echo -e "${GREEN}✓ Déploiement terminé !${NC}"
echo "=========================================="
echo ""
echo "Accès à l'application:"
echo "  URL: https://crm.cmcisn.com"
echo "  Setup wizard: https://crm.cmcisn.com/setup"
echo ""
echo "IMPORTANT: Assurez-vous que:"
echo "  1. Le DNS crm.cmcisn.com pointe vers ce serveur"
echo "  2. Traefik est configuré avec le certresolver 'letsencrypt'"
echo ""
echo "Commandes utiles:"
echo "  Voir les logs: docker compose -f docker-compose.hostinger.yaml logs -f"
echo "  Arrêter: docker compose -f docker-compose.hostinger.yaml down"
echo "  Redémarrer: docker compose -f docker-compose.hostinger.yaml restart"
echo ""
echo "Base de données:"
echo "  Container: churchcrm-db"
echo "  Accès: docker exec -it churchcrm-db mysql -u churchcrm -p churchcrm"
echo ""
