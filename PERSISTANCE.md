# 🗂️ Persistance des Données - ChurchCRM Docker

## ✅ Ce qui est DEJÀ persistant (sur votre machine)

### 1. 💻 Code Source (100% persistant)
Tous les fichiers de code sont montés en volume depuis votre machine hôte :
- **`src/`** → `/var/www/html` (code PHP complet)
- **`orm/`** → Schémas de base de données Propel
- **`src/ChurchCRM/Service/`** → Services PHP (y compris DiscipleMakerService)
- **`src/api/routes/`** → Endpoints API
- **`src/js/`** → JavaScript frontend
- **`locale/`** → Fichiers de traduction

**Ces modifications sont directement dans vos fichiers, pas seulement dans Docker !**

### 2. 🖼️ Images et Données de Test
- **`cypress/data/images/`** → Photos de profil (personnes et familles)

## 🔄 Nouvelle Persistance Ajoutée

### 3. 🗄️ Base de Données MariaDB (MAINTENANT persistante)
- **Volume Docker nommé** : `mariadb_data`
- **Emplacement** : Géré par Docker (sur votre machine)
- **Contenu persistant** :
  - Toutes les données utilisateurs (personnes, familles, groupes)
  - Relations de discipulat (`per_DiscipleMakerID`)
  - Groupes et rôles personnalisés
  - Paramètres de configuration

## 📋 Fichiers de Configuration Persistants

### Fichiers de code modifiés :
```
/Users/mac/ChurchCRM copie/churchcrm-local/
├── orm/schema.xml                                 # ✅ Schéma ORM avec disciple maker
├── src/mysql/upgrade/7.3.2.sql                   # ✅ Migration group_id
├── src/mysql/upgrade/7.3.3.sql                   # ✅ Migration disciple maker
├── src/ChurchCRM/Service/
│   ├── UserGroupScopeService.php                  # ✅ Service de scope de groupe
│   └── DiscipleMakerService.php                   # ✅ Service de faiseur de disciple
├── src/api/routes/people/
│   ├── people-disciples.php                       # ✅ API disciples
│   └── index.php (modifié)                        # ✅ Enregistrement API
├── src/ChurchCRM/View/DiscipleMakerView.php       # ✅ Vue faiseur disciple
├── src/js/DiscipleMakerManager.js                 # ✅ Module JavaScript
└── locale/
    ├── messages.po (modifié)                      # ✅ Traductions françaises
    └── fr/LC_MESSAGES/messages.mo (compilé)       # ✅ Fichier binaire de traduction
```

### Fichiers de configuration Docker :
```
/Users/mac/ChurchCRM copie/churchcrm-local/docker/
├── docker-compose.yaml (modifié)                  # ✅ Avec volume mariadb_data
├── .env.local                                     # ✅ Configuration des ports
├── docker-compose.override.yaml                   # ✅ Override des ports
└── migrations/                                    # ✅ Scripts de migration automatique
    ├── 7.3.2.sql                                  # ✅ Migration group_id
    ├── 7.3.3.sql                                  # ✅ Migration disciple maker
    ├── apply-migrations.sh                        # ✅ Script d'application automatique
    └── 99-test-disciples.sql                      # ✅ Données de test
```

## 🔄 Comment la Persistance Fonctionne

### Au démarrage des conteneurs :

1. **Base de données vide** → Script d'initialisation s'exécute
2. **Migrations automatiques** → Les fichiers SQL dans `migrations/` sont appliqués
3. **Données de test** → Le script `99-test-disciples.sql` crée les relations
4. **Volumes montés** → Tout votre code est directement accessible

### À l'arrêt des conteneurs :

- ✅ **Code** : Toujours dans vos fichiers (modifications conservées)
- ✅ **Base de données** : Sauvegardée dans le volume Docker `mariadb_data`
- ✅ **Images** : Photos dans `cypress/data/images/`
- ✅ **Configuration** : Fichiers `.env` et `docker-compose.yaml`

## 🚀 Commandes pour Gérer la Persistance

### Sauvegarder la base de données
```bash
# Sauvegarde complète
docker exec docker-database-1 mariadb-dump -uroot -pchangeme churchcrm > backup_$(date +%Y%m%d).sql

# Restaurer si nécessaire
docker exec -i docker-database-1 mariadb -uroot -pchangeme churchcrm < backup_20260501.sql
```

### Voir les volumes Docker
```bash
docker volume ls | grep mariadb
docker volume inspect docker_mariadb_data
```

### Supprimer tout et recommencer (si nécessaire)
```bash
cd /Users/mac/ChurchCRM\ copie/churchcrm-local/docker
docker-compose --env-file .env --env-file .env.local --profile dev down -v
# Le -v supprime aussi les volumes (données incluses)
```

## ✅ Validation de la Persistance

Après avoir arrêté et redémarré les conteneurs, vous devriez retrouver :

1. **Colonne database** : `per_DiscipleMakerID` existe
2. **Relations de discipulat** : Toujours présentes
3. **Données utilisateurs** : Conservées
4. **Code modifié** : Toujours à jour

### Test de validation :
```bash
# Après redémarrage
docker exec docker-database-1 mariadb -uroot -pchangeme churchcrm -e "
    SELECT COUNT(*) as relations_discipulat 
    FROM person_per 
    WHERE per_DiscipleMakerID IS NOT NULL;
"
```

## 📝 Résumé

| Type | Emplacement | Persistance | Sauvegarde Git |
|------|-------------|-------------|----------------|
| **Code PHP** | `src/` | ✅ Fichiers locaux | ✅ Recommandé |
| **ORM Schema** | `orm/` | ✅ Fichiers locaux | ✅ Recommandé |
| **API Routes** | `src/api/` | ✅ Fichiers locaux | ✅ Recommandé |
| **JavaScript** | `src/js/` | ✅ Fichiers locaux | ✅ Recommandé |
| **Traductions** | `locale/` | ✅ Fichiers locaux | ✅ Recommandé |
| **Config Docker** | `docker/` | ✅ Fichiers locaux | ✅ Recommandé |
| **Base de données** | Volume Docker | ✅ Volume persistant | ❌ Non (utiliser dump) |
| **Images** | `cypress/data/` | ✅ Fichiers locaux | ❌ Non (binaires) |

## 🎯 Conclusion

**Toutes vos modifications de code sont 100% persistantes** car elles sont dans vos fichiers locaux, pas seulement dans les conteneurs Docker. Les seules choses qui pourraient être perdues sont :
- Les données de la base de données si vous supprimez le volume Docker
- Les images de profil si vous supprimez le dossier `cypress/data/`

Pour.backup complet, sauvegardez simplement tout le dossier du projet !
