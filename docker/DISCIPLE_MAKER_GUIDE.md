# Système de Faiseur de Disciple (Disciple Maker)

## 📖 Vue d'ensemble

Ce système permet de gérer les relations de discipulat au sein de l'église, où chaque membre peut avoir un "faiseur de disciple" qui l'accompagne spirituellement.

## 🎯 Concepts Clés

- **Disciple (Membre)** : Une personne qui est accompagnée spirituellement
- **Faiseur de Disciple (Disciple Maker)** : Une personne qui accompagne un ou plusieurs disciples
- **Relation** : Un disciple a un seul faiseur de disciple, mais un faiseur peut avoir plusieurs disciples

## 🗄️ Structure de la Base de Données

### Nouvelle colonne dans `person_per`
- `per_DiscipleMakerID` (mediumint(9) unsigned) - Référence étrangère vers `per_ID`
- Index : `idx_disciple_maker` pour optimiser les requêtes
- Contrainte : `fk_disciple_maker` avec suppression en cascade (SET NULL)

## 🔄 Relations Établies (Données de Test)

```
Jean Responsable (ID: 260)
├── Marie Membre (ID: 261)
└── Pierre Membre (ID: 262)

Paul Responsable (ID: 263)
├── Jacques Membre (ID: 264)
└── Thomas Membre (ID: 265)
```

## 🛠️ Service PHP : DiscipleMakerService

### Méthodes Disponibles

1. **setDiscipleMaker(int $personId, ?int $discipleMakerId): bool**
   - Définit le faiseur de disciple pour une personne
   - Empêche une personne d'être son propre faiseur
   - Met à jour les métadonnées (editedBy, dateLastEdited)

2. **getDiscipleMaker(int $personId): ?array**
   - Retourne les informations du faiseur de disciple d'une personne
   - Retourne null si aucun faiseur n'est assigné

3. **getDisciples(int $discipleMakerId): array**
   - Retourne tous les disciples d'un faiseur de disciple
   - Inclut photo, email, téléphone, famille

4. **getDisciplesStats(int $discipleMakerId): array**
   - Statistiques sur les disciples d'un faiseur
   - Nombre total, nombre actif, dernière mise à jour

5. **getPotentialDiscipleMakers(): array**
   - Liste toutes les personnes possibles comme faiseurs
   - Inclut le nombre de disciples actuels pour chaque personne

6. **transferDisciples(int $fromId, int $toId): int**
   - Transfère tous les disciples d'un faiseur à un autre
   - Retourne le nombre de disciples transférés

7. **removeDiscipleMaker(int $personId): bool**
   - Supprime le faiseur de disciple d'une personne

## 🌐 API Endpoints

### Base URL : `/api/disciples`

#### GET `/disciples/makers`
- Retourne la liste de tous les faiseurs de disciple potentiels
- Inclut le nombre de disciples pour chaque personne

#### POST `/disciples/person/{personId}/maker`
- Assigne ou met à jour le faiseur de disciple d'une personne
- Body : `{ "discipleMakerId": 123 }` ou `null` pour supprimer

#### GET `/disciples/person/{personId}/maker`
- Retourne le faiseur de disciple actuel d'une personne

#### GET `/disciples/maker/{makerId}/disciples`
- Retourne tous les disciples d'un faiseur de disciple

#### GET `/disciples/maker/{makerId}/stats`
- Retourne les statistiques de discipulat d'un faiseur

#### POST `/disciples/transfer`
- Transfère des disciples d'un faiseur à un autre
- Body : `{ "fromDiscipleMakerId": 1, "toDiscipleMakerId": 2 }`

#### DELETE `/disciples/person/{personId}/maker`
- Supprime le faiseur de disciple d'une personne

## 📡 Exemples d'Utilisation cURL

### Assigner un faiseur de disciple
```bash
curl -X POST http://localhost:8080/api/disciples/person/261/maker \
  -H "Content-Type: application/json" \
  -d '{"discipleMakerId": 260}'
```

### Obtenir le faiseur de disciple d'une personne
```bash
curl http://localhost:8080/api/disciples/person/261/maker
```

### Obtenir tous les disciples d'un faiseur
```bash
curl http://localhost:8080/api/disciples/maker/260/disciples
```

### Transférer des disciples
```bash
curl -X POST http://localhost:8080/api/disciples/transfer \
  -H "Content-Type: application/json" \
  -d '{"fromDiscipleMakerId": 260, "toDiscipleMakerId": 263}'
```

### Supprimer un faiseur de disciple
```bash
curl -X DELETE http://localhost:8080/api/disciples/person/261/maker
```

## 🎨 Intégration UI (À Implémenter)

### Page Profil Membre
- Afficher le faiseur de disciple actuel
- Bouton pour changer de faiseur de disciple
- Modal de sélection avec recherche

### Page Profil Faiseur
- Liste de tous ses disciples
- Statistiques de discipulat
- Bouton pour transférer tous les disciples

### Dashboard
- Widget "Mes Disciples" pour les faiseurs de disciple
- Statistiques générales de discipulat

## 🔐 Permissions

- **Consultation** : Tous les utilisateurs connectés
- **Modification** : Nécessite le rôle `EditRecords`
- **Application du scope** : Un faiseur ne voit que ses disciples (selon le groupe)

## 📊 Rapports Possibles

1. **Liste de tous les disciples sans faiseur**
2. **Faiseurs avec trop de disciples**
3. **Arbre de discipulat** (hiérarchie)
4. **Statistiques par groupe/departement**

## 🚀 Prochaines Étapes

1. Interface utilisateur pour assigner/changer les faiseurs
2. Widget dashboard pour les faiseurs de disciple
3. Système de notifications pour les faiseurs
4. Rapports et statistiques avancées
5. Historique des changements de faiseur de disciple
