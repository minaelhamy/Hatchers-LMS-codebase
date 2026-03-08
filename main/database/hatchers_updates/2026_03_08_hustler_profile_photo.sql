-- Add founder profile picture support for existing Hustler databases
ALTER TABLE hustler_founder_profiles
ADD COLUMN profile_photo_url VARCHAR(255) NULL AFTER founder_email;
