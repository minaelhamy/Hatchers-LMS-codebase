-- Hatchers core MVP refinements
-- Run after the earlier Hatchers updates.

ALTER TABLE founder_tasks
    ADD COLUMN completed_at DATETIME NULL AFTER status,
    ADD COLUMN completed_by_usertypeID INT(11) NULL AFTER completed_at;

ALTER TABLE founder_meetings
    ADD COLUMN title VARCHAR(255) NULL AFTER mentor_id,
    ADD COLUMN description TEXT NULL AFTER title,
    ADD COLUMN requested_by_usertypeID INT(11) NULL AFTER meeting_type,
    ADD COLUMN request_status VARCHAR(30) NOT NULL DEFAULT 'scheduled' AFTER requested_by_usertypeID,
    ADD COLUMN join_link VARCHAR(255) NULL AFTER request_status,
    ADD COLUMN notification_sent_at DATETIME NULL AFTER updated_at;

ALTER TABLE founder_learning
    ADD COLUMN description TEXT NULL AFTER subtitle,
    ADD COLUMN resource_url VARCHAR(255) NULL AFTER description,
    ADD COLUMN resource_type VARCHAR(50) NULL AFTER resource_url;

CREATE TABLE IF NOT EXISTS hatchers_messages (
    hatchers_message_id INT(11) NOT NULL AUTO_INCREMENT,
    founder_id INT(11) NOT NULL,
    mentor_id INT(11) NOT NULL,
    sender_id INT(11) NOT NULL,
    sender_usertypeID INT(11) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    PRIMARY KEY (hatchers_message_id),
    KEY idx_hatchers_messages_founder (founder_id),
    KEY idx_hatchers_messages_mentor (mentor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS learning_library (
    learning_library_id INT(11) NOT NULL AUTO_INCREMENT,
    founder_id INT(11) NULL,
    mentor_id INT(11) NULL,
    created_by_userID INT(11) NOT NULL,
    created_by_usertypeID INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    resource_type VARCHAR(50) NOT NULL DEFAULT 'link',
    resource_url VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (learning_library_id),
    KEY idx_learning_library_founder (founder_id),
    KEY idx_learning_library_mentor (mentor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
