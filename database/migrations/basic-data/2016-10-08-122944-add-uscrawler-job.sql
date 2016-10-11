-- Add data for US crawler
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\USCrawler', 'Constitutional Court Crawler', (SELECT currval('user_id_user_seq')));
