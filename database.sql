-- Run this SQL in phpMyAdmin or MySQL CLI before using the site
-- This replaces the old schema: it adds the applicant/e-bike fields, the
-- documentation picture path, and the generated reference number.

CREATE DATABASE IF NOT EXISTS ebike_registry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ebike_registry;

CREATE TABLE IF NOT EXISTS registrations (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    reference_no       VARCHAR(20)   NOT NULL UNIQUE,           -- e.g. EB-2026-00001
    applicant_name     VARCHAR(150)  NOT NULL,
    address            VARCHAR(255)  NOT NULL,                  -- Address / Barangay
    contact_number     VARCHAR(30)   NOT NULL,
    email              VARCHAR(150)  NOT NULL,
    ebike_brand_model  VARCHAR(150)  NOT NULL,
    serial_no          VARCHAR(100)  NOT NULL UNIQUE,
    year_bought        YEAR          NOT NULL,
    registration_type  ENUM('New Registration','Renewal','Transfer of Ownership') NOT NULL DEFAULT 'New Registration',
    doc_picture        VARCHAR(255)  NOT NULL,                  -- filename stored in /uploads
    admin_response     TEXT,                                    -- admin's remark
    status              ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Optional: seed one demo record (approved, so you can preview the certificate)
INSERT INTO registrations
    (reference_no, applicant_name, address, contact_number, email, ebike_brand_model,
     serial_no, year_bought, registration_type, doc_picture, admin_response, status)
VALUES
    ('EB-2026-00001', 'Juan Dela Cruz', 'Purok 2, Barangay Cadulawan, Talisay City', '+63 912 345 6789',
     'juan@email.com', 'Kyoto Surge X1', 'EB-2024-00001', 2024, 'New Registration',
     'sample-placeholder.jpg', 'Welcome! Your e-bike is now officially registered.', 'Approved');
