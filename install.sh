#!/bin/bash
# ============================================================
# KAAY DEM ! — Script d'installation automatique
# ============================================================
echo "🚗 Kaay Dem! — Installation"
echo "================================"

# Vérifier MySQL
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL non trouvé. Installez MySQL 8.0+ d'abord."
    exit 1
fi

# Demander les credentials
read -p "MySQL utilisateur [root]: " DB_USER
DB_USER=${DB_USER:-root}
read -s -p "MySQL mot de passe: " DB_PASS
echo

# Créer la base
echo "📦 Création de la base de données..."
mysql -u "$DB_USER" -p"$DB_PASS" < database/schema.sql && \
mysql -u "$DB_USER" -p"$DB_PASS" kaaydem_db < database/seed.sql && \
echo "✅ Base de données créée avec succès" || \
{ echo "❌ Erreur lors de la création de la base"; exit 1; }

# Mettre à jour la config
sed -i "s/'user' => 'root'/'user' => '$DB_USER'/" config/config.php
sed -i "s/'pass' => ''/'pass' => '$DB_PASS'/" config/config.php

# Permissions
chmod -R 755 .
chmod -R 777 storage/ public/uploads/

echo ""
echo "✅ Installation terminée !"
echo "👉 Configurez Apache pour pointer vers : $(pwd)/public"
echo "👉 URL par défaut : http://localhost/kaaydem/public"
echo ""
echo "Comptes de démo (mot de passe: password)"
echo "  Admin    : admin@kaaydem.sn"
echo "  Driver 1 : driver1@kaaydem.sn"
echo "  Passager : passenger1@kaaydem.sn"
