# Guide de déploiement ChurchCRM sur Hostinger VPS

Ce guide explique comment déployer ChurchCRM avec les nouvelles fonctionnalités (Faiseur de disciple + Module Réunions) sur un VPS Hostinger.

## Prérequis

### 1. Type d'hébergement Hostinger

**Ce déploiement nécessite un VPS Hostinger** (pas d'hébergement partagé) :
- **VPS Plans** : KVM 1, KVM 2, KVM 4, ou supérieur
- **OS** : Ubuntu 20.04/22.04 ou Debian 11/12 recommandé
- **RAM** : Minimum 2GB (4GB recommandé)

L'hébergement partagé ne supporte pas Docker.

### 2. Accéder au VPS

```bash
# Via SSH avec les identifiants Hostinger
ssh root@votre-ip-vps

# Ou via le terminal Hostinger
```

## Étapes de déploiement

### Étape 1: Préparer le VPS

```bash
# Mise à jour du système
apt update && apt upgrade -y

# Installer Docker
curl -sSL https://get.docker.com | sh

# Vérifier l'installation
docker --version
docker compose version
```

### Étape 2: Télécharger ChurchCRM

```bash
# Cloner le dépôt (ou uploader votre version modifiée)
cd /var/www
git clone https://github.com/ChurchCRM/CRM.git churchcrm
cd churchcrm

# Si vous avez les modifications locales:
# - Uploadez les fichiers via SFTP
# - Ou créez un Git repository avec vos modifications
```

### Étape 3: Configurer l'environnement

```bash
# Modifier le fichier d'environnement
nano docker/.env.hostinger
```

**Modifiez OBLIGATOIREMENT ces valeurs :**

```env
MYSQL_ROOT_PASSWORD=votre_mot_de_passe_root_ici
MYSQL_DATABASE=churchcrm
MYSQL_USER=churchcrm
MYSQL_PASSWORD=votre_mot_de_passe_ici
```

Générez des mots de passe forts :
```bash
# Générer un mot de passe sécurisé
openssl rand -base64 32
```

### Étape 4: Lancer le déploiement

```bash
# Lancer le script de déploiement
./deploy-hostinger.sh
```

Ou manuellement :

```bash
# Build et démarrage
docker compose -f docker-compose.hostinger.yaml up -d --build

# Vérifier les containers
docker ps
```

### Étape 5: Finaliser l'installation

1. **Ouvrir le setup wizard** : `http://votre-ip-vps/setup`

2. **Suivre les étapes** :
   - Sélectionner la langue (Français)
   - Configuration de la base de données :
     - Host: `database`
     - Database: `churchcrm`
     - User: `churchcrm`
     - Password: (celui dans .env.hostinger)

3. **Créer l'administrateur**

4. **Appliquer la migration pour les nouvelles fonctionnalités** :

```bash
# Accéder au container
docker exec -it churchcrm-web bash

# Appliquer la migration
mysql -h database -u churchcrm -p churchcrm < /var/www/html/src/mysql/upgrade/7.3.3.sql

# Puis la migration des réunions
mysql -h database -u churchcrm -p churchcrm < /var/www/html/src/mysql/upgrade/7.3.4.sql
```

## Migrations SQL requises

Les nouvelles fonctionnalités nécessitent ces migrations :

### 1. Faiseur de disciple (7.3.3.sql)

Crée la colonne `per_DiscipleMakerID` dans la table person_per.

### 2. Module Réunions (7.3.4.sql)

Crée les tables :
- `meeting_mtg` - Réunions
- `meeting_attendance_mat` - Présences

Pour les appliquer après le déploiement :

```bash
# Depuis l'hôte
cat docker/migrations/7.3.4.sql | docker exec -i churchcrm-db mysql -u churchcrm -pchurchcrm churchcrm
```

## Configuration du domaine (optionnel)

### Avec un domaine Hostinger

1. **DNS** : Ajoutez un enregistrement A dans Hostinger DNS
   - Type: A
   - Name: @ (ou sous-domaine)
   - Value: IP de votre VPS

2. **Reverse Proxy** (recommandé) :

```bash
# Installer nginx sur le VPS
apt install nginx -y

# Configurer le reverse proxy
nano /etc/nginx/sites-available/churchcrm
```

```nginx
server {
    listen 80;
    server_name votre-domaine.com;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

```bash
# Activer le site
ln -s /etc/nginx/sites-available/churchcrm /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

3. **SSL avec Certbot** :

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d votre-domaine.com
```

## Maintenance

### Voir les logs

```bash
# Tous les logs
docker compose -f docker-compose.hostinger.yaml logs -f

# Logs web uniquement
docker compose -f docker-compose.hostinger.yaml logs -f webserver

# Logs base de données
docker compose -f docker-compose.hostinger.yaml logs -f database
```

### Sauvegardes

```bash
# Sauvegarde de la base de données
docker exec churchcrm-db mysqldump -u churchcrm -pchurchcrm churchcrm > backup_$(date +%Y%m%d).sql

# Sauvegarde des fichiers
tar -czf uploads_$(date +%Y%m%d).tar.gz src/Images/
```

### Mise à jour

```bash
# Arrêter les containers
docker compose -f docker-compose.hostinger.yaml down

# Mettre à jour le code
git pull origin master

# Relancer
./deploy-hostinger.sh
```

### Redémarrage

```bash
# Redémarrer tous les services
docker compose -f docker-compose.hostinger.yaml restart

# Redémarrer uniquement le web
docker compose -f docker-compose.hostinger.yaml restart webserver
```

## Nouvelles fonctionnalités

Après le déploiement et les migrations, vous aurez accès à :

### 1. Faiseur de disciple
- Dans **Membres → Modifier un membre**, champ "Faiseur de disciple"
- Sur la **fiche membre**, affichage du discipulat
- API REST `/api/disciples/...`

### 2. Module Réunions
- Menu **Réunions** dans la barre latérale
- Tableau de bord des réunions
- Création avec organisateur (famille/groupe/organisation)
- Gestion des présences
- Chargement automatique des membres par organisateur

## Sécurité

### Firewall

```bash
# Configurer le firewall
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw enable
```

### Base de données

- La base de données n'est accessible que depuis le réseau Docker interne
- Le port 3306 n'est pas exposé publiquement

### Mots de passe

- Changez TOUJOURS les mots de passe par défaut
- Utilisez des mots de passe forts (16+ caractères)
- Ne committez JAMAIS `.env` dans Git

## Dépannage

### Le site ne répond pas

```bash
# Vérifier les containers
docker ps

# Vérifier les logs
docker compose -f docker-compose.hostinger.yaml logs

# Redémarrer
docker compose -f docker-compose.hostinger.yaml restart
```

### Erreur de connexion à la base de données

```bash
# Vérifier que la base de données est prête
docker exec churchcrm-db mysqladmin ping -h localhost

# Entrer dans la base de données
docker exec -it churchcrm-db mysql -u churchcrm -p
```

### Permissions denied

```bash
# Corriger les permissions
docker exec churchcrm-web chown -R www-data:www-data /var/www/html/logs
docker exec churchcrm-web chmod -R 0775 /var/www/html/logs
```

## Support

- **Documentation ChurchCRM** : https://docs.churchcrm.io
- **Issues GitHub** : https://github.com/ChurchCRM/CRM/issues
- **Community Slack** : https://churchcrm.slack.com
