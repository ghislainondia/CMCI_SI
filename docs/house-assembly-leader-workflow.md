# Workflow des dirigeants d’assemblée de maison

Ce document est une note de passation pour toute personne ou IA qui reprend le
travail sur les dirigeants d’assemblée de maison (ADM).

## État de cette intervention

Appliqué le 12 août 2026 sur le code monté par Docker dans l’environnement de
développement local.

- Les fichiers PHP concernés ont passé `php -l` dans le conteneur
  `webserver-dev`.
- `git diff --check` ne signale aucune erreur d’espacement.
- Le test de parcours connecté reste à faire avec le compte Firmin dans le
  navigateur : cette étape est explicitement listée à la fin de ce document.

## Modèle de données retenu

- Une ADM est une **Family** (`family_fam`).
- Les membres de cette ADM sont les personnes dont `person_per.per_fam_ID`
  correspond à son identifiant.
- L’ADM confiée à un dirigeant est enregistrée dans `user_usr.fam_id`.
- Une portée de groupe, indépendante, est enregistrée dans `user_usr.group_id`.

`fam_id` est donc la seule source d’autorité qui définit un *dirigeant d’ADM*.
Il ne faut pas inverser ces deux colonnes dans la base.

## Configuration dans l’éditeur utilisateur

Dans `src/UserEditor.php`, l’ordre et le contenu attendus sont :

1. **Assemblée de maison (Family)** : liste des ADM (`family_fam`), sauvegardée
   dans `user_usr.fam_id`. Sélectionner une ADM rend l’utilisateur dirigeant de
   cette ADM.
2. **Scoped House Assembly (Groupe)** : liste des groupes, sauvegardée dans
   `user_usr.group_id`. Cette portée est distincte de l’ADM.

Les valeurs existantes ne sont pas migrées ni échangées automatiquement :
elles sont déjà enregistrées dans les colonnes correspondant à leur type.

## Parcours de connexion retenu

Pour un utilisateur non administrateur ayant un `fam_id` :

1. Après authentification, il arrive sur `/people/house-assembly`, le tableau
   de bord de son ADM.
2. Le logo, `/people`, `/people/dashboard` et `/v2/dashboard` ramènent à ce
   tableau de bord. Le menu « Mon assemblée de maison » mène à
   `/people/family/{fam_id}`.
3. `/people/family` ne liste jamais les autres ADM pour un dirigeant : il
   redirige directement vers son ADM.
4. La page ADM ne propose ni retour vers la liste globale ni navigation
   précédent/suivant.

Le tableau de bord présente les programmes des sept prochains jours, les
profils récemment mis à jour de l’ADM et une bannière défilante prête à
recevoir les communications de l’église.

## Sécurité et correction de l’erreur « Assemblée introuvable »

Les routes HTML autorisent le dirigeant si l’ADM demandée correspond à son
`fam_id`. Les API de famille doivent appliquer exactement la même règle.

Les fichiers suivants contrôlent désormais l’accès par **groupe OU famille** :

- `src/api/routes/people/people-family.php` pour les avatars et photos ;
- `src/CMCI Life/Slim/Middleware/Api/FamilyMiddleware.php` pour les autres API
  de famille.

Avant cette correction, la page `/people/family/{fam_id}` était autorisée,
mais son appel d’avatar était refusé parce qu’il ne vérifiait que `group_id`.
Le navigateur affichait alors brièvement « Assemblée de maison introuvable ».

## Fichiers modifiés dans cette reprise

- `src/UserEditor.php`
- `src/CMCI Life/dto/ChurchVocabulary.php`
- `src/CMCI Life/Service/HouseAssemblyLeaderService.php`
- `src/CMCI Life/Authentication/AuthenticationManager.php`
- `src/Include/Header.php`
- `src/CMCI Life/Config/Menu/Menu.php`
- `src/people/routes/dashboard.php`
- `src/v2/routes/root.php`
- `src/people/routes/house-assembly.php`
- `src/people/routes/family.php`
- `src/people/views/family-view.php`
- `src/api/routes/people/people-family.php`
- `src/CMCI Life/Slim/Middleware/Api/FamilyMiddleware.php`

## Vérifications à effectuer après une modification future

1. Dans l’éditeur de Firmin, vérifier que l’ADM « Assemblée chez les METINOU »
   est choisie dans **Assemblée de maison (Family)**.
2. Se connecter comme Firmin et vérifier l’URL
   `/people/family/1010` sans notification « introuvable ».
3. Vérifier qu’un clic sur le logo et sur « Mon assemblée de maison » conserve
   cette URL.
4. Ouvrir `/people/family` : elle doit rediriger vers `/people/family/1010`.
5. Contrôler qu’un autre identifiant, par exemple `/people/family/1024`, est
   refusé pour Firmin.
6. Se connecter comme administrateur et vérifier que les listes globales des
   ADM et des groupes restent disponibles.
