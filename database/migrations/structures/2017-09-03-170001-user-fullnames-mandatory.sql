UPDATE "user" SET fullname = 'System Import' where id_user = 1;
UPDATE "user" SET fullname = 'System Tagging' where id_user = 2;
UPDATE "user" SET fullname = 'System Crawler' where id_user = 3;

ALTER TABLE "user" ALTER COLUMN fullname SET NOT NULL;
