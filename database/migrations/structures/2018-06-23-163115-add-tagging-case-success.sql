CREATE TYPE case_success AS ENUM (
	'neutral', /* Not knowing, the case was stopped for some reason. */
	'positive', /* The court has taken the case into account (The advocate is not an idiot.) */
	'negative', /* The court hasn't taken the case into account (The advocate is an idiot.) */
	'unknown' /* The result could not be determined. */
);

CREATE TABLE tagging_case_success (
	id_tagging_case_success BIGSERIAL PRIMARY KEY,
	document_id BIGINT NOT NULL REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
	case_success case_success NULL,
	status tagging_status NOT NULL,
	is_final BOOLEAN NULL,
	debug TEXT NULL,
	inserted TIMESTAMP NOT NULL DEFAULT NOW(),
	inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
	job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE UNIQUE INDEX ON tagging_case_success(document_id);

COMMENT ON TABLE tagging_case_success IS 'Entries containing tagging of documents with their case result with their history (last inserted tagging of certain document is considered valid).';
COMMENT ON COLUMN tagging_case_success.status IS 'Status of real tagging, see its states.';
COMMENT ON COLUMN tagging_case_success.is_final IS 'Set to true when created by flawless human.';
COMMENT ON COLUMN tagging_advocate.job_run_id IS 'ID of job run which added this tagging.';

-- Add ID of case into tagging_case_success and make document_id optional
ALTER TABLE tagging_case_success ADD COLUMN  case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE tagging_case_success ALTER COLUMN document_id DROP NOT NULL;

DROP INDEX tagging_case_success_document_id_idx;
CREATE INDEX ON tagging_case_success(case_id);

COMMENT ON COLUMN tagging_case_success.case_id IS 'Case to which the tagging belongs';
COMMENT ON COLUMN tagging_case_success.document_id IS 'Document based on which the tagging was done... Or null when done by other means.';

-- Create "materialized" latest tagging of case successes
CREATE TABLE latest_tagging_case_success (
	case_id BIGINT NOT NULL UNIQUE REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
	tagging_case_success_id BIGINT NOT NULL UNIQUE REFERENCES tagging_case_success(id_tagging_case_success) ON UPDATE CASCADE ON DELETE CASCADE
);

COMMENT ON TABLE latest_tagging_case_success IS 'Table that for cases (with taggings) stores the current tagging';

-- Populate with current data
INSERT INTO latest_tagging_case_success (SELECT DISTINCT ON (case_id) case_id, id_tagging_case_success FROM tagging_case_success ORDER BY case_id, inserted DESC);

-- Create wrapper view
CREATE OR REPLACE VIEW vw_latest_tagging_case_success AS
	SELECT
		tagging_case_success.*
	FROM latest_tagging_case_success
		JOIN tagging_case_success ON latest_tagging_case_success.tagging_case_success_id = tagging_case_success.id_tagging_case_success;

COMMENT ON VIEW vw_latest_tagging_case_success IS 'View showing all latest case success taggings with all details';

CREATE TRIGGER trg_tagging_case_success_case_id_change BEFORE UPDATE OR INSERT ON tagging_case_success FOR EACH ROW
EXECUTE PROCEDURE fnc_trg_tagging_prevent_case_id_change();

CREATE TRIGGER trg_tagging_case_success_is_latest_consistency AFTER UPDATE OR INSERT OR DELETE ON tagging_case_success FOR EACH ROW
EXECUTE PROCEDURE fnc_trg_tagging_maintain_is_latest_flag();
