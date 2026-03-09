-- Hustler MVP: isolated investor login + founder diagnostic engine tables
-- Run this on the database used by the Hatchers deployment before opening hustler.hatchers.ai.

CREATE TABLE IF NOT EXISTS hustler_investors (
    hustler_investor_id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(190) NULL,
    username VARCHAR(120) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (hustler_investor_id),
    UNIQUE KEY uniq_hustler_investor_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hustler_founder_profiles (
    hustler_founder_profile_id INT(11) NOT NULL AUTO_INCREMENT,
    hustler_investor_id INT(11) NOT NULL,
    founder_name VARCHAR(180) NULL,
    founder_email VARCHAR(190) NULL,
    profile_photo_url VARCHAR(255) NULL,
    company_name VARCHAR(190) NULL,
    idea_summary TEXT NULL,
    stage_label VARCHAR(120) NOT NULL DEFAULT 'Needs diagnosis',
    skills_summary TEXT NULL,
    weekly_time_commitment VARCHAR(120) NULL,
    capital_available VARCHAR(120) NULL,
    traction_summary TEXT NULL,
    constraints_summary TEXT NULL,
    competitor_notes TEXT NULL,
    memory_summary TEXT NULL,
    last_diagnosis_json LONGTEXT NULL,
    last_plan_json LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (hustler_founder_profile_id),
    UNIQUE KEY uniq_hustler_profile_investor (hustler_investor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hustler_conversations (
    hustler_conversation_id INT(11) NOT NULL AUTO_INCREMENT,
    hustler_founder_profile_id INT(11) NOT NULL,
    role VARCHAR(20) NOT NULL,
    message LONGTEXT NOT NULL,
    message_kind VARCHAR(50) NOT NULL DEFAULT 'chat',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (hustler_conversation_id),
    KEY idx_hustler_conversations_profile (hustler_founder_profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hustler_action_items (
    hustler_action_item_id INT(11) NOT NULL AUTO_INCREMENT,
    hustler_founder_profile_id INT(11) NOT NULL,
    item_type VARCHAR(30) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'todo',
    sort_order INT(11) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (hustler_action_item_id),
    KEY idx_hustler_action_items_profile (hustler_founder_profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hustler_market_assets (
    hustler_market_asset_id INT(11) NOT NULL AUTO_INCREMENT,
    hustler_founder_profile_id INT(11) NOT NULL,
    market_overview LONGTEXT NULL,
    ideal_customer_profile LONGTEXT NULL,
    competitor_patterns_json LONGTEXT NULL,
    distribution_angles_json LONGTEXT NULL,
    social_posts_json LONGTEXT NULL,
    post_images_json LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (hustler_market_asset_id),
    UNIQUE KEY uniq_hustler_market_profile (hustler_founder_profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Replace the example values below with your investor test credentials as needed.
-- Password hash generated with: sha512(encryption_key + plain_password)
INSERT INTO hustler_investors (name, email, username, password, is_active)
SELECT 'Investor Demo', 'demo@hatchers.ai', 'investor-demo', '763a92eef16454b78593d4e264d64ee1', 1
WHERE NOT EXISTS (
    SELECT 1 FROM hustler_investors WHERE username = 'investor-demo'
);
