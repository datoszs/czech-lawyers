-- Add database user to the job table
ALTER TABLE job ADD COLUMN database_user_id BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE job ADD CONSTRAINT only_once UNIQUE (name);

COMMENT ON COLUMN job.name IS 'Name of the job matching the fully qualified name of the command class.';
COMMENT ON COLUMN job.database_user_id IS 'User ID used for database operations in the job.';

-- Add message to the job_run table
ALTER TABLE job_run ADD COLUMN message TEXT NULL;
ALTER TABLE job_run ALTER finished DROP NOT NULL;
ALTER TABLE job_run ALTER return_code DROP NOT NULL;

COMMENT ON COLUMN job_run.message IS 'Message from the job wrapper, such as error messages about error states etc.';