# ChurchCRM UI

Interface moderne pour ChurchCRM, construite avec Next.js 14, React et Tailwind CSS.

## Caractéristiques

- 🎨 Design sombre moderne inspiré de Tasko UI
- 📱 Responsive (mobile, tablette, desktop)
- 🔒 Authentification via cookies de session ChurchCRM
- 🚀 API REST existante de ChurchCRM
- 🌍 Traduction française

## Pages

- **Tableau de bord** - Vue d'ensemble avec statistiques
- **Membres** - Liste et recherche des membres
- **Dons** - Historique et gestion des contributions
- **Calendrier** - À venir

## Installation

```bash
# Installer les dépendances
npm install

# Configurer l'URL de ChurchCRM
cp .env.example .env
# Éditer .env et définir NEXT_PUBLIC_CHURCHCRM_URL

# Lancer le serveur de développement
npm run dev
```

Ouvrir [http://localhost:3000](http://localhost:3000) dans le navigateur.

## Configuration

Créez un fichier `.env` à la racine du projet :

```
NEXT_PUBLIC_CHURCHCRM_URL=http://localhost
```

Remplacez `http://localhost` par l'URL de votre instance ChurchCRM si nécessaire.

## APIs utilisées

Le frontend se connecte aux APIs existantes de ChurchCRM :

- `/api/persons/*` - Gestion des membres
- `/api/payments/*` - Dons et paiements
- `/api/deposits/*` - Dépôts bancaires
- `/api/donationFunds/*` - Fonds de dons

## Stack technique

- **Framework** : Next.js 14 (App Router)
- **UI** : Tailwind CSS + shadcn/ui
- **Icons** : Lucide React
- **HTTP** : Axios
- **Charts** : Recharts
- **Langage** : TypeScript
