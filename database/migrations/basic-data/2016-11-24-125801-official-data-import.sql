-- Add job entry for official data importer
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\OfficialDataImport', 'Official court data importer (to be used manually).', 1);

ALTER TABLE "case" ADD COLUMN official_data JSONB NULL;

COMMENT ON COLUMN "case".official_data IS 'Array of unique values loaded form officialy provided datasets';
