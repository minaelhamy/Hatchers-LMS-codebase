-- Add generated social image storage for existing Hustler databases
ALTER TABLE hustler_market_assets
ADD COLUMN post_images_json LONGTEXT NULL AFTER social_posts_json;
