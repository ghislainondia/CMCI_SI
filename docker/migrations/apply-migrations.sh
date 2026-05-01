#!/bin/bash
# Script d'application des migrations SQL au démarrage de la base de données

echo "=== Application des migrations SQL ChurchCRM ==="

# Attendre que MariaDB soit prêt
until mysql -uroot -p"${MYSQL_ROOT_PASSWORD:-changeme}" -e "SELECT 1" >/dev/null 2>&1; do
    echo "En attente de MariaDB..."
    sleep 2
done

echo "MariaDB est prêt, application des migrations..."

# Appliquer les migrations si elles n'ont pas déjà été appliquées
for migration_file in /docker-entrypoint-initdb.d/99-migrations/*.sql; do
    if [ -f "$migration_file" ]; then
        migration_name=$(basename "$migration_file")

        # Vérifier si la migration a déjà été appliquée
        applied=$(mysql -uroot -p"${MYSQL_ROOT_PASSWORD:-changeme}" "${MYSQL_DATABASE:-churchcrm}" -sN \
            -e "SELECT COUNT(*) FROM version_ver WHERE ver_version = '$migration_name'" 2>/dev/null)

        if [ "$applied" = "0" ]; then
            echo "Application de la migration: $migration_name"
            mysql -uroot -p"${MYSQL_ROOT_PASSWORD:-changeme}" "${MYSQL_DATABASE:-churchcrm}" < "$migration_file"

            if [ $? -eq 0 ]; then
                echo "✅ Migration $migration_name appliquée avec succès"

                # Enregistrer la migration dans la table des versions
                mysql -uroot -p"${MYSQL_ROOT_PASSWORD:-changeme}" "${MYSQL_DATABASE:-churchcrm}" \
                    -e "INSERT INTO version_ver (ver_version, ver_update_start, ver_update_end) VALUES ('$migration_name', NOW(), NOW())"
            else
                echo "❌ Erreur lors de l'application de la migration $migration_name"
            fi
        else
            echo "⏭️  Migration $migration_name déjà appliquée, skipping"
        fi
    fi
done

echo "=== Terminé ==="
