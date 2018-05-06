-- Add redownload NSS document files job
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\DocumentFileRedownloader', 'Redownloads document files for NSS court.', 3);
