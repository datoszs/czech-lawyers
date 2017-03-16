-- Remove used fuzzy status (consider it as failed)
UPDATE tagging_advocate SET status = 'failed' WHERE status = 'fuzzy';
UPDATE tagging_case_result SET status = 'failed' WHERE status = 'fuzzy';


-- Prepare new enum type
ALTER TYPE tagging_status RENAME TO tagging_status_old;

CREATE TYPE tagging_status AS ENUM (
	'failed', /* processing failed due to some error state (exception, missing file etc, to many matches) */
	'ignored' /* tagging of this entity as not relevant (no advocate present,...) */,
	'processed' /* entity was successfulLy tagged */
);

-- Drop dependant views
DROP VIEW vw_latest_tagging_advocate;
DROP VIEW vw_latest_tagging_case_result;

-- Change column types
ALTER TABLE tagging_advocate ALTER COLUMN status TYPE tagging_status USING (status::text)::tagging_status;
ALTER TABLE tagging_case_result ALTER COLUMN status TYPE tagging_status USING (status::text)::tagging_status;

-- Remove old (and now unused enum type)
DROP TYPE tagging_status_old;

-- Recreate views
CREATE OR REPLACE VIEW vw_latest_tagging_case_result AS
	SELECT
		tagging_case_result.*
	FROM latest_tagging_case_result
		JOIN tagging_case_result ON latest_tagging_case_result.tagging_case_result_id = tagging_case_result.id_tagging_case_result;

COMMENT ON VIEW vw_latest_tagging_case_result IS 'View showing all latest case result taggings with all details';

CREATE OR REPLACE VIEW vw_latest_tagging_advocate AS
	SELECT
		tagging_advocate.*
	FROM latest_tagging_advocate
		JOIN tagging_advocate ON latest_tagging_advocate.tagging_advocate_id = tagging_advocate.id_tagging_advocate;

COMMENT ON VIEW vw_latest_tagging_advocate IS 'View showing all latest advocate taggings with all details';
