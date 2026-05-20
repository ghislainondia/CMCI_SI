# Guide de déploiement ChurchCRM avec Traefik

Ce guide explique comment déployer ChurchCRM avec un reverse proxy Traefik existant, rendant l'application disponible sur `https://crm.cmcisn.com`.

## Prérequis

- **Traefik déjà configuré** sur votre serveur Docker
- **DNS configuré** : `crm.cmcisn.com` pointe vers l'IP de votre serveur
- **Réseau Traefik** : `traefik-public` (ou modifiez le nom dans docker-compose)

## Configuration Traefik requise

Votre Traefik doit avoir :

1. **Entrypoints** configurés :
   - `web` (port 80)
   - `websecure` (port 443)

2. **CertResolver Let's Encrypt** nommé `letsencrypt` :
   ```yaml
   # Dans traefik.yml ou docker-compose de Traefik
   certificatesResolvers:
     letsencrypt:
       acme:
         email: your-email@example.com
         storage: /letsencrypt/acme.json
         httpChallenge:
           entryPoint: web
   ```

## Déploiement rapide

### 1. Cloner le dépôt

```bash
cd /var/www
git clone https://github.com/ghislainondia/churchcrm-assemblees.git churchcrm
cd churchcrm
```

### 2. Configurer les mots de passe

```bash
nano docker/.env.hostinger
```

Modifiez OBLIGATOIREMENT :
```env
MYSQL_ROOT_PASSWORD=votre_mot_de_passe_root
MYSQL_PASSWORD=votre_mot_de_passe
```

### 3. Lancer le déploiement

```bash
./deploy-hostinger.sh
```

### 4. Accéder à l'application

Ouvrez : **https://crm.cmcisn.com/setup**

Suivez le wizard avec :
- **Host** : `database` (nom du container Docker)
- **Database** : `churchcrm`
- **User** : `churchcrm`
- **Password** : (celui configuré dans .env.hostinger)

## Fichiers modifiés pour Traefik

| Fichier | Changement |
|---------|-----------|
| `docker-compose.hostinger.yaml` | Ports exposés supprimés, labels Traefik ajoutés |
| `deploy-hostinger.sh` | Vérification réseau Traefik ajoutée |

## Labels Traefik utilisés

```yaml
labels:
  # Activer Traefik pour ce container
  - "traefik.enable=true"

  # Router HTTP → redirection HTTPS
  - "traefik.http.routers.churchcrm.rule=Host(`crm.cmcisn.com`)"
  - "traefik.http.routers.churchcrm.entrypoints=web"
  - "traefik.http.routers.churchcrm.middlewares=redirect-to-https"

  # Router HTTPS avec certificat Let's Encrypt
  - "traefik.http.routers.churchcrm-secure.rule=Host(`crm.cmcisn.com`)"
  - "traefik.http.routers.churchcrm-secure.entrypoints=websecure"
  - "traefik.http.routers.churchcrm-secure.tls=true"
  - "traefik.http.routers.churchcrm-secure.tls.certresolver=letsencrypt"

  # Service backend
  - "traefik.http.services.churchcrm.loadbalancer.server.port=80"
```

## Personnalisation

### Changer le domaine

Remplacez `crm.cmcisn.com` par votre domaine dans `docker-compose.hostinger.yaml` :

```yaml
- "traefik.http.routers.churchcrm.rule=Host(`votre-domaine.com`)"
- "traefik.http.routers.churchcrm-secure.rule=Host(`votre-domaine.com`)"
```

### Changer le réseau Traefik

Si votre réseau Traefik a un autre nom :

```yaml
networks:
  traefik-public:
    external: true
    name: votre-reseau-traefik
```

### Changer le certResolver

Si votre certresolver Let's Encrypt a un autre nom :

```yaml
- "traefik.http.routers.churchcrm-secure.tls.certresolver=votre-certresolver"
```

## Migrations SQL

Après le premier déploiement, appliquez les migrations :

```bash
# Migration Faiseur de disciple
cat src/mysql/upgrade/7.3.3.sql | docker exec -i churchcrm-db mysql -u churchcrm -p churchcrm

# Migration Réunions
cat src/mysql/upgrade/7.3.4.sql | docker exec -i churchcrm-db mysql -u churchcrm -p churchcrm
```

## Maintenance

### Voir les logs

```bash
# Tous les logs
docker compose -f docker-compose.hostinger.yaml logs -f

# Logs web uniquement
docker compose -f docker-compose.hostinger.yaml logs -f webserver

# Logs Traefik (si dans un container séparé)
docker logs traefik
```

### Redémarrer

```bash
docker compose -f docker-compose.hostinger.yaml restart
```

### Mettre à jour

```bash
git pull origin master
docker compose -f docker-compose.hostinger.yaml up -d --build
```

## Dépannage

### Le site n'est pas accessible

1. **Vérifiez le DNS** :
   ```bash
   dig crm.cmcisn.com
   # Doit retourner l'IP de votre serveur
   ```

2. **Vérifiez Traefik** :
   ```bash
   docker logs traefik
   # Cherchez des erreurs liées à churchcrm
   ```

3. **Vérifiez le container** :
   ```bash
   docker ps | grep churchcrm
   docker logs churchcrm-web
   ```

### Erreur de certificat SSL

Vérifiez que Traefik a le certresolver `letsencrypt` configuré :

```bash
# Dans les logs Traefik, cherchez
grep "letsencrypt" <(docker logs traefik)
```

### Port already allocated

Plusieurs possibilités :
1. Un autre container utilise le port 80/443 → Normal avec Traefik, utilisez la config Traefik
2. Vous utilisez l'ancienne config → Supprimez les ports mappés
3. Redémarrez Traefik après avoir créé le réseau

## Architecture

```
Internet (80/443)
    ↓
Traefik (SSL termination, routing)
    ↓
churchcrm-web (port 80 interne)
    ↓
churchcrm-db (réseau privé)
```

Avantages :
- ✅ Gestion centralisée des certificats SSL
- ✅ Un seul point d'entrée
- ✅ Pas de ports exposés inutilement
- ✅ Facile d'ajouter d'autres services
