# 🚗 Kaay Dem ! — Guide d'installation

## Comptes de démonstration

| Email | Mot de passe | Rôle |
|-------|-------------|------|
| admin@kaaydem.sn | password | Administrateur |
| driver1@kaaydem.sn | password | Conducteur |
| driver2@kaaydem.sn | password | Conducteur |
| passenger1@kaaydem.sn | password | Passager |
| passenger2@kaaydem.sn | password | Passager |
| student1@kaaydem.sn | password | Passager (demande conducteur en attente) |

---

## Installation (XAMPP)

### 1. Copier le dossier
Copier le dossier `kaaydem/` dans `C:\xampp\htdocs\`

### 2. Base de données
1. Ouvrir phpMyAdmin : http://localhost/phpmyadmin
2. Créer une base de données : `kaaydem_db` (collation : utf8mb4_unicode_ci)
3. Cliquer sur `kaaydem_db` → onglet **SQL**
4. Coller le contenu de `database/schema.sql` → Exécuter
5. Onglet **SQL** à nouveau → exécuter `database/seed.sql` **BLOC PAR BLOC**

### 3. Configuration base de données
Ouvrir `config/config.php`, section `db` :
```php
'user'     => 'root',     // votre user MySQL XAMPP
'password' => '',         // vide par défaut sur XAMPP
```

### 4. Accès
Ouvrir : http://localhost/kaaydem/public/

---

## ✉️ Configuration Email Gmail (3 étapes)

L'email est pré-configuré avec `7karamosekoucisse@gmail.com`.
Il reste juste à générer un **Mot de passe d'application** Google.

### Étape 1 — Activer la validation en 2 étapes
1. Aller sur https://myaccount.google.com/security
2. Cliquer **"Validation en 2 étapes"**
3. Suivre les instructions pour l'activer

### Étape 2 — Générer un mot de passe d'application
1. Sur https://myaccount.google.com/security
2. Chercher **"Mots de passe des applications"** (en bas de page)
3. Cliquer dessus
4. Dans le champ "Nom", taper : **Kaay Dem**
5. Cliquer **Créer**
6. Google affiche un code de **16 caractères** (ex: `abcd efgh ijkl mnop`)

### Étape 3 — Coller le code dans config.php
Ouvrir `config/config.php`, trouver la ligne :
```php
'password' => 'VOTRE_MOT_DE_PASSE_APP',
```
Remplacer par le code Google (sans espaces) :
```php
'password' => 'abcdefghijklmnop',
```

### ✅ Tester l'envoi
1. Créer un compte sur le site
2. Un email de bienvenue doit arriver dans `7karamosekoucisse@gmail.com`
3. En cas de problème, vérifier `storage/emails/` — les emails sont toujours sauvegardés là

---

## API REST (lecture seule)
- `GET /api/trips` — liste des trajets
- `GET /api/trips/{id}` — détail d'un trajet
- `GET /api/distances` — distances et prix suggérés
