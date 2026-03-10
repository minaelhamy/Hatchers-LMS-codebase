-- Add profile fields used by unlock logic and media uploads for existing Hustler databases
ALTER TABLE hustler_founder_profiles
ADD COLUMN IF NOT EXISTS company_logo_url VARCHAR(255) NULL AFTER profile_photo_url,
ADD COLUMN IF NOT EXISTS target_customer_profile TEXT NULL AFTER idea_summary;
