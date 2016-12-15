-- Add ID of case into tagging_advocate and make document_id optional
ALTER TABLE tagging_advocate ADD COLUMN  case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE tagging_advocate ALTER COLUMN document_id DROP NOT NULL;

DROP INDEX tagging_advocate_advocate_id_idx;
DROP INDEX tagging_advocate_document_id_idx;
CREATE INDEX ON tagging_advocate(case_id);

COMMENT ON COLUMN tagging_advocate.case_id IS 'Case to which the tagging belongs';
COMMENT ON COLUMN tagging_advocate.document_id IS 'Document based on which the tagging was done... Or null when done by other means.';

-- Add ID of case into tagging_case_result and make document_id optional
ALTER TABLE tagging_case_result ADD COLUMN  case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE tagging_case_result ALTER COLUMN document_id DROP NOT NULL;

DROP INDEX tagging_case_result_document_id_idx;
CREATE INDEX ON tagging_case_result(case_id);

COMMENT ON COLUMN tagging_case_result.case_id IS 'Case to which the tagging belongs';
COMMENT ON COLUMN tagging_case_result.document_id IS 'Document based on which the tagging was done... Or null when done by other means.';
