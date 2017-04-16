CREATE TABLE case_disputation (
	id_case_disputation BIGSERIAL PRIMARY KEY,
	email TEXT NOT NULL,
	code VARCHAR(128) NOT NULL,
	case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE CASCADE,
	tagging_case_result_id BIGINT NULL REFERENCES tagging_case_result (id_tagging_case_result) ON UPDATE CASCADE ON DELETE CASCADE,
	tagging_advocate_id BIGINT NULL REFERENCES tagging_advocate (id_tagging_advocate) ON UPDATE CASCADE ON DELETE CASCADE,
	reason TEXT NOT NULL,
	inserted TIMESTAMP NOT NULL DEFAULT NOW(),
	valid_until TIMESTAMP NOT NULL,
	validated_at TIMESTAMP NULL,
	response TEXT NULL,
	resolved TIMESTAMP NULL,
	resolved_by BIGINT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
	UNIQUE (email, code)
);

COMMENT ON TABLE case_disputation IS 'Table containing all disputed cases, each dispustation is validated via e-mail.';
COMMENT ON COLUMN case_disputation.case_id IS 'Disputed case ID';
COMMENT ON COLUMN case_disputation.code IS 'Code for verification';
COMMENT ON COLUMN case_disputation.tagging_advocate_id IS 'Disputed tagging of case result or NULL.';
COMMENT ON COLUMN case_disputation.tagging_advocate_id IS 'Disputed tagging of advocate or NULL.';
COMMENT ON COLUMN case_disputation.reason IS 'Mandatory reasoning';
COMMENT ON COLUMN case_disputation.inserted IS 'Timestamp when the disputation was inserted.';
COMMENT ON COLUMN case_disputation.valid_until IS 'Timestamp until when the request is valid.';
COMMENT ON COLUMN case_disputation.validated_at IS 'Timestamp of validation, or NULL when not validated.';
COMMENT ON COLUMN case_disputation.resolved IS 'Timestamp when the disputation was resolved.';
COMMENT ON COLUMN case_disputation.resolved_by IS 'ID of user which resolved the dispustation.';