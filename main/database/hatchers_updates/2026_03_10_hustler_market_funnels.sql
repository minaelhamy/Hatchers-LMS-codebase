-- Add instagram profile and funnel suggestions storage for existing Hustler databases
ALTER TABLE hustler_market_assets
ADD COLUMN instagram_profile_json LONGTEXT NULL AFTER ideal_customer_profile,
ADD COLUMN funnel_suggestions_json LONGTEXT NULL AFTER post_images_json;
