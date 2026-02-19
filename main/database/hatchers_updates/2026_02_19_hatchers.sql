-- Hatchers LMS: founder/mentor system + AI assistant tables
-- Run once on production DB (no installer rerun required).

CREATE TABLE IF NOT EXISTS mentor_founder (
    mentor_founder_id INT(11) NOT NULL AUTO_INCREMENT,
    mentor_id INT(11) NOT NULL,
    founder_id INT(11) NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (mentor_founder_id),
    UNIQUE KEY uniq_mentor_founder (mentor_id, founder_id),
    UNIQUE KEY uniq_founder (founder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS founder_tasks (
    founder_task_id INT(11) NOT NULL AUTO_INCREMENT,
    founder_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    milestone_id INT(11) NULL,
    status TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (founder_task_id),
    KEY idx_founder_tasks_founder (founder_id),
    KEY idx_founder_tasks_milestone (milestone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS milestone_meta (
    milestone_meta_id INT(11) NOT NULL AUTO_INCREMENT,
    milestone_id INT(11) NOT NULL,
    founder_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    status TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (milestone_meta_id),
    KEY idx_milestone_meta_milestone (milestone_id),
    KEY idx_milestone_meta_founder (founder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS founder_meetings (
    founder_meeting_id INT(11) NOT NULL AUTO_INCREMENT,
    founder_id INT(11) NOT NULL,
    mentor_id INT(11) NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NULL,
    meeting_type VARCHAR(50) NOT NULL DEFAULT 'mentoring',
    status TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (founder_meeting_id),
    KEY idx_founder_meetings_founder (founder_id),
    KEY idx_founder_meetings_mentor (mentor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS founder_learning (
    founder_learning_id INT(11) NOT NULL AUTO_INCREMENT,
    founder_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255) NULL,
    starts_at DATETIME NULL,
    status TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (founder_learning_id),
    KEY idx_founder_learning_founder (founder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hatcher_ai_settings (
    hatcher_ai_settings_id INT(11) NOT NULL AUTO_INCREMENT,
    system_prompt TEXT NOT NULL,
    guidelines TEXT NULL,
    model VARCHAR(50) NOT NULL DEFAULT 'gpt-4o-mini',
    temperature DECIMAL(3,2) NOT NULL DEFAULT 0.7,
    max_tokens INT(11) NOT NULL DEFAULT 600,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (hatcher_ai_settings_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hatcher_ai_conversations (
    hatcher_ai_conversation_id INT(11) NOT NULL AUTO_INCREMENT,
    founder_id INT(11) NOT NULL,
    role VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (hatcher_ai_conversation_id),
    KEY idx_ai_conversations_founder (founder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hatcher_ai_context (
    hatcher_ai_context_id INT(11) NOT NULL AUTO_INCREMENT,
    founder_id INT(11) NOT NULL,
    goals TEXT NULL,
    current_sprint TEXT NULL,
    blockers TEXT NULL,
    progress_summary TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (hatcher_ai_context_id),
    UNIQUE KEY uniq_ai_context_founder (founder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hatchers_nav_items (
    hatchers_nav_item_id INT(11) NOT NULL AUTO_INCREMENT,
    label VARCHAR(120) NOT NULL,
    icon VARCHAR(120) NULL,
    link VARCHAR(255) NULL,
    location VARCHAR(50) NOT NULL DEFAULT 'left',
    sort_order INT(11) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (hatchers_nav_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Home', 'fa-home', 'dashboard/index', 'left', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Home' AND location='left');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Launch Plan', 'fa-rocket', 'launchplan/index', 'left', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Launch Plan' AND location='left');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'AI Tools', 'fa-bolt', 'aitools/index', 'left', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='AI Tools' AND location='left');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Learning Plan', 'fa-graduation-cap', 'learningplan/index', 'left', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Learning Plan' AND location='left');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Mentoring', 'fa-comments', 'mentoring/index', 'left', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Mentoring' AND location='left');

INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Landing Pages', 'fa-file-text-o', NULL, 'right_ai', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Landing Pages' AND location='right_ai');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Forms', 'fa-wpforms', NULL, 'right_ai', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Forms' AND location='right_ai');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Social Media', 'fa-thumbs-o-up', NULL, 'right_ai', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Social Media' AND location='right_ai');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'CRM', 'fa-address-book-o', NULL, 'right_ai', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='CRM' AND location='right_ai');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Payments & Bookings', 'fa-credit-card', NULL, 'right_ai', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Payments & Bookings' AND location='right_ai');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'SEO', 'fa-search', NULL, 'right_ai', 6, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='SEO' AND location='right_ai');
INSERT INTO hatchers_nav_items (label, icon, link, location, sort_order, active)
SELECT 'Messaging Automation', 'fa-commenting-o', NULL, 'right_ai', 7, 1
WHERE NOT EXISTS (SELECT 1 FROM hatchers_nav_items WHERE label='Messaging Automation' AND location='right_ai');

INSERT INTO permissions (name, description)
SELECT 'mentor', 'Mentor module'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='mentor');
INSERT INTO permissions (name, description)
SELECT 'mentor_view', 'Mentor view founder'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='mentor_view');
INSERT INTO permissions (name, description)
SELECT 'mentor_add_meeting', 'Mentor add meeting'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='mentor_add_meeting');
INSERT INTO permissions (name, description)
SELECT 'mentor_add_learning', 'Mentor add learning'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='mentor_add_learning');
INSERT INTO permissions (name, description)
SELECT 'mentor_add_task', 'Mentor add task'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='mentor_add_task');
INSERT INTO permissions (name, description)
SELECT 'mentor_add_milestone', 'Mentor add milestone'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='mentor_add_milestone');
INSERT INTO permissions (name, description)
SELECT 'hatchersadmin', 'Hatchers super admin'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='hatchersadmin');
INSERT INTO permissions (name, description)
SELECT 'hatchersadmin_assignments', 'Hatchers assignments'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='hatchersadmin_assignments');
INSERT INTO permissions (name, description)
SELECT 'hatchersadmin_nav', 'Hatchers navigation settings'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='hatchersadmin_nav');
INSERT INTO permissions (name, description)
SELECT 'hatchersadmin_ai', 'Hatchers AI settings'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='hatchersadmin_ai');
INSERT INTO permissions (name, description)
SELECT 'aiassistant', 'Hatchers AI assistant'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='aiassistant');
INSERT INTO permissions (name, description)
SELECT 'aiassistant_chat', 'Hatchers AI assistant chat'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='aiassistant_chat');

INSERT INTO permission_relationships (permission_id, usertype_id)
SELECT p.permissionID, 3
FROM permissions p
WHERE p.name IN ('aiassistant', 'aiassistant_chat')
AND NOT EXISTS (
    SELECT 1 FROM permission_relationships pr
    WHERE pr.permission_id = p.permissionID AND pr.usertype_id = 3
);

INSERT INTO permission_relationships (permission_id, usertype_id)
SELECT p.permissionID, 2
FROM permissions p
WHERE p.name IN ('mentor', 'mentor_view', 'mentor_add_meeting', 'mentor_add_learning', 'mentor_add_task', 'mentor_add_milestone')
AND NOT EXISTS (
    SELECT 1 FROM permission_relationships pr
    WHERE pr.permission_id = p.permissionID AND pr.usertype_id = 2
);
