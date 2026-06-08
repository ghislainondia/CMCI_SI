#!/bin/bash
# Script de migration ChurchCRM Local → Hostinger
# Usage: ./migrate-to-hostinger.sh

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "=========================================="
echo "ChurchCRM - Migration vers Hostinger"
echo "=========================================="

# ============================================
# CONFIGURATION - À MODIFIER
# ============================================
LOCAL_DB_CONTAINER="docker-database-1"
LOCAL_DB_PASSWORD="changeme"

# Hostinger - MODIFIEZ CES VALEURS
HOSTINGER_USER="root"              # Votre utilisateur SSH
HOSTINGER_HOST="76.13.36.126"      # IP ou domaine du serveur
HOSTINGER_PATH="/root/churchcrm-assemblees"  # Chemin du projet
HOSTINGER_DB_PASSWORD="Ghisadmin@2k26"  # Mot de passe production (à changer !)

# ============================================

BACKUP_FILE="churchcrm-backup-$(date +%Y%m%d-%H%M%S).sql.gz"

# 1. Export de la base locale (structure + données COMPLETES)
echo -e "${YELLOW}[1/4] Export de la base locale...${NC}"

docker exec ${LOCAL_DB_CONTAINER} mysqldump \
  -u churchcrm \
  -p${LOCAL_DB_PASSWORD} \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  churchcrm | gzip > ${BACKUP_FILE}

echo -e "${GREEN}✓ Base exportée: ${BACKUP_FILE} ($(du -h ${BACKUP_FILE} | cut -f1))${NC}"

# 2. Transférer vers Hostinger
echo -e "${YELLOW}[2/4] Transfert vers Hostinger (${HOSTINGER_HOST})...${NC}"
echo "→ Entrez votre mot de passe SSH :"

scp ${BACKUP_FILE} ${HOSTINGER_USER}@${HOSTINGER_HOST}:/tmp/
echo -e "${GREEN}✓ Fichier transféré${NC}"

# 3. Exécuter les commandes sur Hostinger
echo -e "${YELLOW}[3/4] Import de la base complète...${NC}"

ssh ${HOSTINGER_USER}@${HOSTINGER_HOST} << EOF
echo "→ Suppression et recréation de la base..."
docker exec -i churchcrm-db mysql -u churchcrm -p${HOSTINGER_DB_PASSWORD} -e "DROP DATABASE IF EXISTS churchcrm; CREATE DATABASE churchcrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "→ Import de la structure et des données..."
gunzip -c /tmp/${BACKUP_FILE} | \
  docker exec -i churchcrm-db mysql -u churchcrm -p${HOSTINGER_DB_PASSWORD} churchcrm

echo "→ Application migration 7.3.2..."
cat ${HOSTINGER_PATH}/docker/migrations/7.3.2.sql | \
  docker exec -i churchcrm-db mysql -u churchcrm -p${HOSTINGER_DB_PASSWORD} churchcrm 2>/dev/null || echo "Migration 7.3.2 déjà appliquée"

echo "→ Application migration 7.3.3 (Faiseur de disciple)..."
cat ${HOSTINGER_PATH}/docker/migrations/7.3.3.sql | \
  docker exec -i churchcrm-db mysql -u churchcrm -p${HOSTINGER_DB_PASSWORD} churchcrm 2>/dev/null || echo "Migration 7.3.3 déjà appliquée"

echo "→ Application migration 7.3.4 (Réunions)..."
cat ${HOSTINGER_PATH}/docker/migrations/7.3.4.sql | \
  docker exec -i churchcrm-db mysql -u churchcrm -p${HOSTINGER_DB_PASSWORD} churchcrm 2>/dev/null || echo "Migration 7.3.4 déjà appliquée"

echo "→ Application migration 7.3.5 (Message de Bertoua)..."
cat ${HOSTINGER_PATH}/docker/migrations/7.3.5.sql | \
  docker exec -i churchcrm-db mysql -u churchcrm -p${HOSTINGER_DB_PASSWORD} churchcrm 2>/dev/null || echo "Migration 7.3.5 déjà appliquée"

echo "→ Nettoyage..."
rm /tmp/${BACKUP_FILE}

echo "✓ Terminé"
EOF

echo -e "${GREEN}✓ Import terminé${NC}"

# 4. Nettoyer local
echo -e "${YELLOW}[4/4] Nettoyage...${NC}"
rm ${BACKUP_FILE}
echo -e "${GREEN}✓ Fichier local supprimé${NC}"

echo ""
echo "=========================================="
echo -e "${GREEN}✓ Migration terminée !${NC}"
echo "=========================================="
echo ""
echo "Vérifiez : https://crm.cmcisn.com"
echo ""
