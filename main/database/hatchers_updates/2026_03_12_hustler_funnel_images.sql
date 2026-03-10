-- Add funnel image storage for existing Hustler databases
ALTER TABLE hustler_market_assets
ADD COLUMN IF NOT EXISTS funnel_images_json LONGTEXT NULL AFTER funnel_suggestions_json;
