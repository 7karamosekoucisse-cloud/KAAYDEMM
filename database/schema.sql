-- ============================================================
-- KAAY DEM ! — Schéma de base de données
-- MySQL 8.0+ | utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS kaaydem_db;
CREATE DATABASE kaaydem_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kaaydem_db;

-- ────────────────────────────────────────────────────────────
-- TABLE : users
-- ────────────────────────────────────────────────────────────
CREATE TABLE users (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email              VARCHAR(255) NOT NULL UNIQUE,
  password_hash      VARCHAR(255) NOT NULL,
  first_name         VARCHAR(100) NOT NULL,
  last_name          VARCHAR(100) NOT NULL,
  phone              VARCHAR(20)  NOT NULL,
  campus             VARCHAR(100) NOT NULL,
  avatar             VARCHAR(255) NULL,
  is_active          TINYINT(1)   NOT NULL DEFAULT 1,
  is_banned          TINYINT(1)   NOT NULL DEFAULT 0,
  trust_score        DECIMAL(5,2) NOT NULL DEFAULT 50.00,
  loyalty_points     INT UNSIGNED NOT NULL DEFAULT 0,
  total_trips        INT UNSIGNED NOT NULL DEFAULT 0,
  total_bookings     INT UNSIGNED NOT NULL DEFAULT 0,
  login_attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until       DATETIME     NULL,
  remember_token     VARCHAR(128) NULL,
  reset_token        VARCHAR(128) NULL UNIQUE,
  reset_token_expiry DATETIME     NULL,
  created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email        (email),
  INDEX idx_trust_score  (trust_score),
  INDEX idx_created_at   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : user_roles
-- ────────────────────────────────────────────────────────────
CREATE TABLE user_roles (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id          INT UNSIGNED NOT NULL,
  role_code        ENUM('PASSENGER','DRIVER','ADMIN') NOT NULL,
  status           ENUM('PENDING','APPROVED','REJECTED','REVOKED') NOT NULL DEFAULT 'PENDING',
  granted_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  validated_by     INT UNSIGNED NULL,
  validated_at     DATETIME     NULL,
  rejection_reason TEXT         NULL,
  UNIQUE KEY uq_user_role (user_id, role_code),
  INDEX idx_status (status),
  CONSTRAINT fk_ur_user      FOREIGN KEY (user_id)      REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ur_validator FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : driver_profiles
-- ────────────────────────────────────────────────────────────
CREATE TABLE driver_profiles (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NOT NULL UNIQUE,
  license_number VARCHAR(50)  NOT NULL,
  vehicle_brand  VARCHAR(100) NOT NULL,
  vehicle_model  VARCHAR(100) NOT NULL,
  vehicle_color  VARCHAR(50)  NOT NULL,
  plate_number   VARCHAR(30)  NOT NULL,
  default_seats  TINYINT UNSIGNED NOT NULL DEFAULT 3,
  license_doc    VARCHAR(255) NULL,
  CONSTRAINT fk_dp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : trips
-- ────────────────────────────────────────────────────────────
CREATE TABLE trips (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  driver_id        INT UNSIGNED NOT NULL,
  departure_city   VARCHAR(100) NOT NULL,
  arrival_city     VARCHAR(100) NOT NULL,
  departure_at     DATETIME     NOT NULL,
  price_per_seat   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  seats_offered    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  seats_available  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  status           ENUM('OPEN','FULL','CANCELLED','COMPLETED') NOT NULL DEFAULT 'OPEN',
  description      TEXT         NULL,
  distance_km      DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  co2_saved        DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  is_recurring     TINYINT(1)   NOT NULL DEFAULT 0,
  recurring_days   VARCHAR(50)  NULL,
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_driver       (driver_id),
  INDEX idx_status       (status),
  INDEX idx_departure_at (departure_at),
  INDEX idx_cities       (departure_city, arrival_city),
  INDEX idx_price        (price_per_seat),
  CONSTRAINT fk_trips_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : stops
-- ────────────────────────────────────────────────────────────
CREATE TABLE stops (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_id     INT UNSIGNED NOT NULL,
  city        VARCHAR(100) NOT NULL,
  position    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  extra_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  INDEX idx_trip_id (trip_id),
  CONSTRAINT fk_stops_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : bookings
-- ────────────────────────────────────────────────────────────
CREATE TABLE bookings (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_id      INT UNSIGNED NOT NULL,
  passenger_id INT UNSIGNED NOT NULL,
  seats_booked TINYINT UNSIGNED NOT NULL DEFAULT 1,
  total_price  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status       ENUM('PENDING','CONFIRMED','COMPLETED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  qr_code      VARCHAR(64)  NULL UNIQUE,
  checked_in   TINYINT(1)   NOT NULL DEFAULT 0,
  checked_in_at DATETIME    NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_trip_id      (trip_id),
  INDEX idx_passenger_id (passenger_id),
  INDEX idx_status       (status),
  INDEX idx_qr_code      (qr_code),
  CONSTRAINT fk_bk_trip      FOREIGN KEY (trip_id)      REFERENCES trips(id) ON DELETE RESTRICT,
  CONSTRAINT fk_bk_passenger FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : booking_status_history
-- ────────────────────────────────────────────────────────────
CREATE TABLE booking_status_history (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id   INT UNSIGNED NOT NULL,
  from_status  ENUM('PENDING','CONFIRMED','COMPLETED','CANCELLED') NULL,
  to_status    ENUM('PENDING','CONFIRMED','COMPLETED','CANCELLED') NOT NULL,
  changed_by   INT UNSIGNED NULL,
  reason       TEXT         NULL,
  changed_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_booking_id (booking_id),
  CONSTRAINT fk_bsh_booking    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_bsh_changed_by FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : reviews
-- ────────────────────────────────────────────────────────────
CREATE TABLE reviews (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id  INT UNSIGNED NOT NULL,
  author_id   INT UNSIGNED NOT NULL,
  target_id   INT UNSIGNED NOT NULL,
  rating      TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment     TEXT         NULL,
  is_punctual TINYINT(1)   NOT NULL DEFAULT 1,
  is_clean    TINYINT(1)   NOT NULL DEFAULT 1,
  is_friendly TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review (booking_id, author_id),
  INDEX idx_target_id (target_id),
  CONSTRAINT fk_rev_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_rev_author  FOREIGN KEY (author_id)  REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_rev_target  FOREIGN KEY (target_id)  REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : messages
-- ────────────────────────────────────────────────────────────
CREATE TABLE messages (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id    INT UNSIGNED NOT NULL,
  recipient_id INT UNSIGNED NOT NULL,
  content      TEXT         NOT NULL,
  is_read      TINYINT(1)   NOT NULL DEFAULT 0,
  booking_id   INT UNSIGNED NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sender    (sender_id),
  INDEX idx_recipient (recipient_id),
  INDEX idx_created   (created_at),
  CONSTRAINT fk_msg_sender    FOREIGN KEY (sender_id)    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_booking   FOREIGN KEY (booking_id)   REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : notifications
-- ────────────────────────────────────────────────────────────
CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  type       VARCHAR(50)  NOT NULL,
  title      VARCHAR(255) NOT NULL,
  body       TEXT         NOT NULL,
  link       VARCHAR(255) NULL,
  payload    JSON         NULL,
  is_read    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_is_read (is_read),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : reports
-- ────────────────────────────────────────────────────────────
CREATE TABLE reports (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT UNSIGNED NOT NULL,
  target_id   INT UNSIGNED NOT NULL,
  target_type ENUM('user','trip') NOT NULL DEFAULT 'user',
  reason      VARCHAR(100) NOT NULL,
  details     TEXT         NULL,
  status      ENUM('PENDING','RESOLVED','DISMISSED') NOT NULL DEFAULT 'PENDING',
  resolved_by INT UNSIGNED NULL,
  resolution  TEXT         NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status    (status),
  INDEX idx_reporter  (reporter_id),
  CONSTRAINT fk_rep_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_rep_resolver FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : waitlist
-- ────────────────────────────────────────────────────────────
CREATE TABLE waitlist (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_id      INT UNSIGNED NOT NULL,
  passenger_id INT UNSIGNED NOT NULL,
  seats_wanted TINYINT UNSIGNED NOT NULL DEFAULT 1,
  position     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_waitlist (trip_id, passenger_id),
  CONSTRAINT fk_wl_trip      FOREIGN KEY (trip_id)      REFERENCES trips(id) ON DELETE CASCADE,
  CONSTRAINT fk_wl_passenger FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- TABLE : badges
-- ────────────────────────────────────────────────────────────
CREATE TABLE badges (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  code        VARCHAR(50)  NOT NULL,
  name        VARCHAR(100) NOT NULL,
  icon        VARCHAR(50)  NOT NULL DEFAULT 'award',
  description VARCHAR(255) NOT NULL DEFAULT '',
  granted_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_badge (user_id, code),
  CONSTRAINT fk_badge_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- VUES SQL
-- ────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_driver_stats AS
SELECT
  u.id, u.first_name, u.last_name, u.campus, u.trust_score,
  COUNT(DISTINCT t.id)   AS total_trips_published,
  COUNT(DISTINCT b.id)   AS total_passengers,
  ROUND(AVG(r.rating),2) AS avg_rating,
  COUNT(r.id)            AS review_count,
  IFNULL(SUM(b.total_price),0) AS total_earnings
FROM users u
JOIN user_roles ur ON ur.user_id = u.id AND ur.role_code = 'DRIVER' AND ur.status = 'APPROVED'
LEFT JOIN trips t ON t.driver_id = u.id AND t.status = 'COMPLETED'
LEFT JOIN bookings b ON b.trip_id = t.id AND b.status = 'COMPLETED'
LEFT JOIN reviews r ON r.target_id = u.id
GROUP BY u.id;

CREATE OR REPLACE VIEW v_popular_routes AS
SELECT
  departure_city, arrival_city,
  COUNT(*) AS trip_count,
  ROUND(AVG(price_per_seat),0) AS avg_price,
  ROUND(AVG(seats_offered - seats_available),1) AS avg_passengers
FROM trips
WHERE status IN ('OPEN','FULL','COMPLETED')
GROUP BY departure_city, arrival_city
ORDER BY trip_count DESC;

CREATE OR REPLACE VIEW v_monthly_revenue AS
SELECT
  DATE_FORMAT(created_at,'%Y-%m') AS month,
  COUNT(*) AS booking_count,
  SUM(total_price) AS total_revenue
FROM bookings
WHERE status IN ('CONFIRMED','COMPLETED')
GROUP BY DATE_FORMAT(created_at,'%Y-%m')
ORDER BY month;

-- ────────────────────────────────────────────────────────────
-- TRIGGERS
-- ────────────────────────────────────────────────────────────
DELIMITER //

-- Incrémente total_trips du conducteur à la complétion d'un trajet
CREATE TRIGGER trg_trip_completed
AFTER UPDATE ON trips
FOR EACH ROW
BEGIN
  IF NEW.status = 'COMPLETED' AND OLD.status != 'COMPLETED' THEN
    UPDATE users SET total_trips = total_trips + 1 WHERE id = NEW.driver_id;
  END IF;
END//

-- Incrémente total_bookings du passager à la création d'une réservation
CREATE TRIGGER trg_booking_created
AFTER INSERT ON bookings
FOR EACH ROW
BEGIN
  UPDATE users SET total_bookings = total_bookings + 1 WHERE id = NEW.passenger_id;
END//

-- Attribution automatique de points de fidélité à la complétion d'une réservation
CREATE TRIGGER trg_booking_completed
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
  IF NEW.status = 'COMPLETED' AND OLD.status != 'COMPLETED' THEN
    -- Passager gagne 10 pts par place réservée
    UPDATE users SET loyalty_points = loyalty_points + (NEW.seats_booked * 10)
    WHERE id = NEW.passenger_id;
  END IF;
END//

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
