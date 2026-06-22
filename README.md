# 🚗 Kaay Dem ! — Plateforme de covoiturage étudiant

> **"Kaay Dem !"** signifie *"Allons-y !"* en wolof.
> Application web PHP 8.2 · MVC natif · MySQL · Bootstrap 5

---

## 📋 Prérequis

| Outil | Version minimale |
|-------|----------------|
| PHP   | 8.2+ |
| MySQL | 8.0+ |
| Apache| 2.4+ (mod_rewrite activé) |
| Composer | — (aucune dépendance externe requise) |

---

## 🚀 Installation en 5 minutes

### 1. Cloner / décompresser le projet
```bash
cp -r kaaydem/ /var/www/html/
```

### 2. Créer la base de données
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p kaaydem_db < database/seed.sql
```

### 3. Configurer la connexion
Éditer `config/config.php` :
```php
'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'kaaydem_db',
    'user' => 'root',
    'pass' => 'votre_mot_de_passe',
],
```

### 4. Configurer Apache
```apache
<VirtualHost *:80>
    ServerName kaaydem.local
    DocumentRoot /var/www/html/kaaydem/public
    <Directory /var/www/html/kaaydem/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Permissions
```bash
chmod -R 755 /var/www/html/kaaydem/
chmod -R 777 /var/www/html/kaaydem/storage/
chmod -R 777 /var/www/html/kaaydem/public/uploads/
```

### 6. Accéder à l'application
```
http://localhost/kaaydem/public/
```

---

## 👥 Comptes de démonstration

> Mot de passe universel : **`password`**

| Email | Rôle | Description |
|-------|------|-------------|
| `admin@kaaydem.sn` | Admin | Accès complet à l'administration |
| `driver1@kaaydem.sn` | Conducteur | Ibrahima Sow, 18 trajets, note 4.5/5 |
| `driver2@kaaydem.sn` | Conducteur | Fatou Ndiaye, 12 trajets, note 5/5 |
| `passenger1@kaaydem.sn` | Passager | Aïssatou Diop, 8 réservations |
| `passenger2@kaaydem.sn` | Passager | Ousmane Ba, 4 réservations |

---

## 🏗️ Architecture

```
kaaydem/
├── app/
│   ├── Controllers/        # Contrôleurs MVC (Auth, Trip, Booking, Admin...)
│   │   └── Api/            # Endpoints REST JSON
│   ├── Models/             # Entités métier (User, Trip, Booking...)
│   ├── Repositories/       # Accès données via PDO (pattern Repository)
│   ├── Services/           # Logique métier (AuthService, BookingService...)
│   ├── Interfaces/         # Contrats DIP
│   ├── Traits/             # Timestampable, Hydratable, Loggable
│   ├── Enums/              # Role, RoleStatus, BookingStatus, TripStatus
│   ├── Exceptions/         # Hiérarchie d'exceptions métier
│   ├── Middlewares/        # Auth, CSRF, Role, Admin, Driver
│   ├── Helpers/            # Csrf, Validator, functions.php
│   └── Views/              # Templates PHP (layouts, partials, pages)
├── config/
│   ├── config.php          # Configuration centrale
│   └── container.php       # Injection de dépendances
├── core/
│   ├── Database.php        # Singleton PDO
│   ├── Session.php         # Gestion sessions sécurisées
│   ├── Request.php         # Wrapper $_GET/$_POST/$_FILES
│   ├── Response.php        # HTML / JSON / Redirect
│   ├── Router.php          # Routeur regex avec middlewares
│   ├── Container.php       # DI avec autowiring
│   ├── View.php            # Moteur de rendu PHP
│   ├── EventDispatcher.php # Pattern Observer
│   └── Logger.php          # Logging fichier
├── database/
│   ├── schema.sql          # Schéma complet (tables, index, vues, triggers)
│   └── seed.sql            # Données de démonstration
├── public/
│   ├── index.php           # Front Controller
│   ├── .htaccess           # Réécriture URL
│   └── assets/             # CSS, JS, images
├── routes/
│   └── web.php             # Toutes les routes
├── storage/logs/           # Fichiers de logs
├── tests/
│   └── Unit/               # Tests PHPUnit
└── autoload.php            # PSR-4 autoloader
```

---

## 🔐 Sécurité implémentée

| Menace | Protection |
|--------|-----------|
| XSS | `e()` = `htmlspecialchars()` sur toutes les sorties |
| CSRF | Token de session + `hash_equals()` sur chaque POST |
| SQLi | 100% requêtes préparées PDO |
| Brute-force | Verrouillage compte après 5 tentatives (15 min) |
| Fixation session | `session_regenerate_id(true)` à la connexion |
| IDOR | Double contrôle vertical (rôle) + horizontal (propriétaire) |
| Upload | Vérification MIME type + taille |
| Path exposure | DocumentRoot → `/public` uniquement |

---

## 🌐 API REST

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/trips` | GET | Liste des trajets (filtres, pagination) |
| `/api/trips/{id}` | GET | Détail d'un trajet |
| `/api/trips/popular` | GET | Routes populaires |

**Exemple :**
```bash
curl "http://localhost/kaaydem/public/api/trips?from=Dakar&to=Thiès"
```

---

## 🎯 Fonctionnalités

### Authentification
- Inscription, connexion, déconnexion
- Remember me (cookie sécurisé 30 jours)
- Réinitialisation mot de passe par email
- Protection brute-force (lockout)

### Gestion des rôles (RBAC)
- Double rôle simultané conducteur / passager
- Validation administrative des conducteurs
- Middleware de contrôle d'accès par rôle

### Trajets
- Publication avec arrêts intermédiaires
- Recherche multicritères (départ, arrivée, date, prix, places)
- Pagination et tri dynamique
- Calcul CO₂ économisé automatique
- Suggestion de prix au km

### Réservations
- Machine à états : PENDING → CONFIRMED → COMPLETED / CANCELLED
- Gestion de la concurrence : UPDATE conditionnel atomique
- QR code d'embarquement
- Historique complet des transitions

### Évaluations
- Note globale (1-5 étoiles)
- Critères : ponctualité, propreté, sympathie
- Score de confiance calculé automatiquement
- Anti-double-évaluation (3 couches)

### Administration
- Dashboard avec statistiques en temps réel
- Validation / refus des demandes conducteur
- Gestion utilisateurs (ban/unban)
- Gestion des signalements
- Export CSV des réservations
- Graphiques Chart.js

### Fonctionnalités bonus
- Messagerie interne (conversations)
- Notifications in-app
- Programme fidélité (points)
- Badges utilisateurs
- Liste d'attente (waitlist)
- API REST JSON
- Carte interactive Leaflet.js
- Classement des conducteurs
- Version mobile en scannat le qr code
- Suivi en temps reel du trajet
- Calcul automatique du prix selon les trajets
- Mode sombre
- Export CSV etc...

---

## 🧪 Tests

```bash
# Depuis le dossier tests/
php ../vendor/bin/phpunit --testdox
```

Tests couverts : BookingService, Validator, User, BookingStatus.

---

## 📊 Modèle de données

**Tables principales :**
`users` · `user_roles` · `driver_profiles` · `trips` · `stops` · `bookings` · `booking_status_history` · `reviews` · `messages` · `notifications` · `reports` · `waitlist` · `badges`

**Vues SQL :**
`v_driver_stats` · `v_popular_routes` · `v_monthly_revenue`

**Triggers :**
- `trg_trip_completed` → incrémente `total_trips` conducteur
- `trg_booking_created` → incrémente `total_bookings` passager
- `trg_booking_completed` → crédite points fidélité

---



### Points forts à démontrer

1. **Architecture MVC** — montrer le flux Request → Router → Middleware → Controller → Service → Repository → View
2. **RBAC double rôle** — se connecter en tant que `driver1@kaaydem.sn` (conducteur ET passager simultanément)
3. **Race condition** — expliquer l'UPDATE atomique dans `TripRepository::decrementSeats()`
4. **Machine à états** — montrer l'historique des transitions dans le détail d'une réservation
5. **Sécurité** — inspecter le token CSRF dans le HTML source, montrer les requêtes préparées
6. **API REST** — ouvrir `/api/trips` dans le navigateur

### Scénario de démonstration 

```
1. Accueil          → recherche Dakar → Thiès
2. Inscription      → créer un compte étudiant
3. Connexion admin  → valider le conducteur driver2
4. Connexion driver → publier un nouveau trajet
5. Connexion pass.  → réserver 1 place → voir le QR code
6. Retour driver    → confirmer la réservation
7. Admin dashboard  → statistiques + export CSV
8. API REST         → GET /api/trips
```

---


