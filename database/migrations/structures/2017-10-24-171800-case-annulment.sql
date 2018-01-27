CREATE TABLE case_annulment (
	id_case_annulment BIGSERIAL PRIMARY KEY,
	annuled_case BIGINT NOT NULL,
	annuling_case BIGINT NULL,
	inserted TIMESTAMP NOT NULL DEFAULT NOW(),
	modified TIMESTAMP NULL,
	job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT,
	UNIQUE (annuled_case, annuling_case),
	UNIQUE (annuled_case),
	UNIQUE (annuling_case)
);

COMMENT ON TABLE case_annulment IS 'Table containing all annuled cases linking to cases that annuled them.';
COMMENT ON COLUMN case_annulment.annuled_case IS 'The mandatory foreign key into the table of cases';
COMMENT ON COLUMN case_annulment.annuling_case IS 'Optional foreign key in a table of cases';
COMMENT ON COLUMN case_annulment.inserted IS 'Timestamp of insertion of this case into our database.';
COMMENT ON COLUMN case_annulment.modified IS 'Timestamp of update of this case after changing.';
COMMENT ON COLUMN case_annulment.job_run_id IS 'ID of job run which added this annuled case.';

ALTER TABLE case_annulment ADD FOREIGN KEY (annuled_case) REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE case_annulment ADD FOREIGN KEY (annuling_case) REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT;

CREATE OR REPLACE VIEW vw_computed_case_annulment AS
	SELECT * FROM "case_annulment";


