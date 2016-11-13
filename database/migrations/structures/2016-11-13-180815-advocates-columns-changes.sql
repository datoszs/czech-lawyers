ALTER TABLE advocate_info DROP COLUMN hash;
ALTER TABLE advocate_info ADD COLUMN status advocate_status;
ALTER TABLE advocate ADD COLUMN local_path TEXT;
ALTER TABLE advocate DROP COLUMN status;
ALTER TABLE advocate DROP COLUMN advocate_info_id;