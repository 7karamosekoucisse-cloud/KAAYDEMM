-- ============================================================
-- KAAY DEM ! — Données de démonstration v9 FINAL
-- Mot de passe universel : password
-- IMPORTANT : Exécuter BLOC PAR BLOC dans phpMyAdmin
-- ============================================================
USE kaaydem_db;

-- BLOC 1 : Vider les tables (exécuter ce bloc en premier)
DELETE FROM badges;
DELETE FROM notifications;
DELETE FROM messages;
DELETE FROM reviews;
DELETE FROM booking_status_history;
DELETE FROM bookings;
DELETE FROM waitlist;
DELETE FROM stops;
DELETE FROM trips;
DELETE FROM driver_profiles;
DELETE FROM user_roles;
DELETE FROM reports;
DELETE FROM users;

-- BLOC 2 : Utilisateurs (mot de passe : password)
INSERT INTO users (id, email, password_hash, first_name, last_name, phone, campus, trust_score, loyalty_points, total_trips, total_bookings, created_at) VALUES
(1, 'admin@kaaydem.sn',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mamadou',  'Diallo', '771234567', 'UCAD', 95.0, 500, 25, 0, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(2, 'driver1@kaaydem.sn',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ibrahima', 'Sow',    '782345678', 'UCAD', 88.5, 320, 18, 3, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(3, 'driver2@kaaydem.sn',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fatou',    'Ndiaye', '773456789', 'UGB',  82.0, 210, 12, 5, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(4, 'passenger1@kaaydem.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aissatou', 'Diop',   '704567890', 'UCAD', 72.0,  80,  0, 8, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(5, 'passenger2@kaaydem.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ousmane',  'Ba',     '775678901', 'UTB',  65.0,  40,  0, 4, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(6, 'student1@kaaydem.sn',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mariama',  'Sarr',   '786789012', 'UADB', 58.0,  20,  0, 2, DATE_SUB(NOW(), INTERVAL 10 DAY));

-- BLOC 3 : Rôles
INSERT INTO user_roles (user_id, role_code, status, granted_at, validated_by, validated_at) VALUES
(1, 'ADMIN',     'APPROVED', DATE_SUB(NOW(), INTERVAL 90 DAY), 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(1, 'PASSENGER', 'APPROVED', DATE_SUB(NOW(), INTERVAL 90 DAY), 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(2, 'PASSENGER', 'APPROVED', DATE_SUB(NOW(), INTERVAL 60 DAY), 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(2, 'DRIVER',    'APPROVED', DATE_SUB(NOW(), INTERVAL 58 DAY), 1, DATE_SUB(NOW(), INTERVAL 58 DAY)),
(3, 'PASSENGER', 'APPROVED', DATE_SUB(NOW(), INTERVAL 45 DAY), 1, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(3, 'DRIVER',    'APPROVED', DATE_SUB(NOW(), INTERVAL 43 DAY), 1, DATE_SUB(NOW(), INTERVAL 43 DAY)),
(4, 'PASSENGER', 'APPROVED', DATE_SUB(NOW(), INTERVAL 30 DAY), 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(5, 'PASSENGER', 'APPROVED', DATE_SUB(NOW(), INTERVAL 20 DAY), 1, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(6, 'PASSENGER', 'APPROVED', DATE_SUB(NOW(), INTERVAL 10 DAY), 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(6, 'DRIVER',    'PENDING',  DATE_SUB(NOW(), INTERVAL 2  DAY), NULL, NULL);

-- BLOC 4 : Profils conducteurs
INSERT INTO driver_profiles (user_id, license_number, vehicle_brand, vehicle_model, vehicle_color, plate_number, default_seats) VALUES
(2, 'DK-2019-001234', 'Toyota',  'Yaris', 'Blanc', 'DK-1234-AB', 3),
(3, 'TH-2020-005678', 'Peugeot', '207',   'Gris',  'TH-5678-CD', 4);

-- BLOC 5 : Trajets
INSERT INTO trips (id, driver_id, departure_city, arrival_city, departure_at, price_per_seat, seats_offered, seats_available, status, description, distance_km, co2_saved, created_at) VALUES
(1, 2, 'Dakar',       'Thiès',       DATE_ADD(NOW(), INTERVAL 2 DAY),   1100, 3, 2, 'OPEN',      'Départ depuis Liberté 6. Climatisé.', 70,   8.4,  DATE_SUB(NOW(), INTERVAL 5  DAY)),
(2, 2, 'Dakar',       'Saint-Louis', DATE_ADD(NOW(), INTERVAL 5 DAY),   4100, 3, 3, 'OPEN',      'Trajet direct sans arrêt.',           270,  32.4, DATE_SUB(NOW(), INTERVAL 4  DAY)),
(3, 3, 'Thiès',       'Dakar',       DATE_ADD(NOW(), INTERVAL 1 DAY),   1100, 4, 1, 'OPEN',      'Départ gare routière à 6h30.',        70,   16.8, DATE_SUB(NOW(), INTERVAL 3  DAY)),
(4, 2, 'Dakar',       'Mbour',       DATE_SUB(NOW(), INTERVAL 10 DAY),  1200, 3, 0, 'COMPLETED', NULL,                                  80,   9.6,  DATE_SUB(NOW(), INTERVAL 15 DAY)),
(5, 3, 'Saint-Louis', 'Dakar',       DATE_SUB(NOW(), INTERVAL 5 DAY),   4100, 4, 0, 'COMPLETED', 'Très bon trajet !',                   270,  64.8, DATE_SUB(NOW(), INTERVAL 8  DAY)),
(6, 2, 'Dakar',       'Kaolack',     DATE_ADD(NOW(), INTERVAL 3 DAY),   2900, 3, 3, 'OPEN',      NULL,                                  195,  23.4, DATE_SUB(NOW(), INTERVAL 1  DAY)),
(7, 3, 'Dakar',       'Ziguinchor',  DATE_ADD(NOW(), INTERVAL 7 DAY),   7100, 4, 4, 'OPEN',      'Trajet longue distance. Pause repas.',470,  112.8, NOW());

-- BLOC 6 : Arrêts
INSERT INTO stops (trip_id, city, position) VALUES
(2, 'Thiès',   1),
(5, 'Thiès',   1),
(7, 'Kaolack', 1);

-- BLOC 7 : Réservations
INSERT INTO bookings (id, trip_id, passenger_id, seats_booked, total_price, status, qr_code, checked_in, created_at) VALUES
(1, 4, 4, 1, 1200,  'COMPLETED', 'QRKD-AABB-1122-3344-5566-7788-AABB-CC11', 1, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(2, 4, 5, 2, 2400,  'COMPLETED', 'QRKD-BBCC-2233-4455-6677-8899-BBCC-DD22', 1, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(3, 5, 4, 2, 8200,  'COMPLETED', 'QRKD-CCDD-3344-5566-7788-9900-CCDD-EE33', 1, DATE_SUB(NOW(), INTERVAL 7  DAY)),
(4, 1, 4, 1, 1100,  'CONFIRMED', 'QRKD-DDEE-4455-6677-8899-0011-DDEE-FF44', 0, DATE_SUB(NOW(), INTERVAL 2  DAY)),
(5, 3, 5, 1, 1100,  'PENDING',   'QRKD-EEFF-5566-7788-9900-1122-EEFF-0055', 0, DATE_SUB(NOW(), INTERVAL 1  DAY));

-- BLOC 8 : Historique statuts
INSERT INTO booking_status_history (booking_id, from_status, to_status, changed_by, changed_at) VALUES
(1, NULL, 'PENDING', 4, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(1, 'PENDING', 'CONFIRMED', 2, DATE_SUB(NOW(), INTERVAL 13 DAY)),
(1, 'CONFIRMED', 'COMPLETED', 2, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, NULL, 'PENDING', 5, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(2, 'PENDING', 'CONFIRMED', 2, DATE_SUB(NOW(), INTERVAL 13 DAY)),
(2, 'CONFIRMED', 'COMPLETED', 2, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(3, NULL, 'PENDING', 4, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, 'PENDING', 'CONFIRMED', 3, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 'CONFIRMED', 'COMPLETED', 3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, NULL, 'PENDING', 4, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 'PENDING', 'CONFIRMED', 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, NULL, 'PENDING', 5, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- BLOC 9 : Évaluations
INSERT INTO reviews (booking_id, author_id, target_id, rating, comment, is_punctual, is_clean, is_friendly) VALUES
(1, 4, 2, 5, 'Excellent conducteur, très ponctuel et voiture impeccable !', 1, 1, 1),
(2, 5, 2, 4, 'Bon trajet, ambiance sympa. Recommandé.', 1, 1, 1),
(3, 4, 3, 5, 'Fatou est une conductrice exemplaire. Trajet très agréable.', 1, 1, 1),
(1, 2, 4, 4, 'Passagère sérieuse et respectueuse.', 1, 0, 1),
(2, 2, 5, 5, 'Très bon passager, aucun souci.', 1, 1, 1),
(3, 3, 4, 4, 'Passagère ponctuelle, agréable à transporter.', 1, 1, 1);

-- BLOC 10 : Messages
INSERT INTO messages (sender_id, recipient_id, content, is_read, booking_id) VALUES
(4, 2, 'Bonjour ! Pouvez-vous confirmer le point de rendez-vous pour le trajet Dakar-Thiès ?', 1, 4),
(2, 4, 'Bonjour ! Je vous attends devant la station Total de Liberté 6 à 7h00. À bientôt !', 1, 4),
(4, 2, 'Parfait, je serai là. Merci !', 1, 4),
(5, 3, 'Bonjour Fatou, c''est bien depuis la gare routière de Thiès ?', 0, 5);

-- BLOC 11 : Notifications
INSERT INTO notifications (user_id, type, title, body, link, is_read, created_at) VALUES
(2, 'booking', 'Nouvelle réservation',  'Aissatou Diop a réservé 1 place sur votre trajet Dakar → Thiès.', '/bookings/4', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 'booking', 'Réservation confirmée', 'Votre réservation pour Dakar → Thiès a été confirmée.',          '/bookings/4', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 'system',  'Demande en examen',     'Votre demande de rôle conducteur est en cours d''examen.',        '/dashboard',  0, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- BLOC 12 : Badges
INSERT INTO badges (user_id, code, name, icon, description) VALUES
(2, 'first_trip',     'Premier trajet',          'star-fill',      'A publié son premier trajet'),
(2, 'trusted_driver', 'Conducteur de confiance', 'shield-check',   'Score de confiance supérieur à 80'),
(3, 'first_trip',     'Premier trajet',          'star-fill',      'A publié son premier trajet'),
(4, 'early_adopter',  'Pionnier',                'lightning-fill', 'Parmi les premiers inscrits');
