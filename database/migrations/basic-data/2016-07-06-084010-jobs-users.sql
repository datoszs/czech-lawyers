-- Properly set sequence increment value
ALTER SEQUENCE user_id_user_seq RESTART WITH 3;

-- Add data for crawler
INSERT INTO "user" (type, username, password, is_active, is_login_allowed) VALUES ('system', 'system-crawler', NULL, TRUE, FALSE);

INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\NSCrawler', 'Supreme Court Crawler', (SELECT currval('user_id_user_seq')));
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\NSSCrawler', 'Supreme Administrative Court Crawler', (SELECT currval('user_id_user_seq')));
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\CausaImport', 'Universal Court Crawler Data Import', (SELECT currval('user_id_user_seq')));
