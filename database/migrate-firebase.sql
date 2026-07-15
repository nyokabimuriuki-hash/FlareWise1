-- =============================================================================
-- One-time migration for an existing FlareWise database
-- Keeps existing user records and makes the schema compatible with Firebase auth.
-- =============================================================================

ALTER TABLE users
    ADD COLUMN firebase_uid VARCHAR(128) NULL UNIQUE AFTER id;

-- Legacy password login is no longer used. Keeping it nullable preserves old rows
-- while allowing Firebase-created accounts to be stored without a PHP password.
ALTER TABLE users
    MODIFY password VARCHAR(255) NULL;
