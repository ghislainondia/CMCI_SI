#!/bin/bash
# Script d'import des données existantes ChurchCRM

echo "=== Import des données existantes ChurchCRM ==="

# Vérifier si le fichier de backup existe
if [ -f "/docker-entrypoint-initdb.d/churchcrm-backup.sql.gz" ]; then
    echo "Import du fichier churchcrm-backup.sql.gz..."
    gunzip < /docker-entrypoint-initdb.d/churchcrm-backup.sql.gz | mysql -uroot -p"${MYSQL_ROOT_PASSWORD:-changeme}" "${MYSQL_DATABASE:-churchcrm}"
    if [ $? -eq 0 ]; then
        echo "✅ Import des données terminé avec succès"
    else
        echo "❌ Erreur lors de l'import des données"
    fi
else
    echo "⚠️  Fichier churchcrm-backup.sql.gz non trouvé, import ignoré"
fi

echo "=== Terminé ==="
