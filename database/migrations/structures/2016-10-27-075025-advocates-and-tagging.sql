-- Refactor schema of advocates to support full content from Czech lawyers association

DROP TABLE tagging;
DROP TABLE advocate_name, advocate;

CREATE TABLE advocate_info (
	id_advocate_info BIGSERIAL PRIMARY KEY,
	advocate_id BIGINT NOT NULL,
	hash VARCHAR(128) NOT NULL,
	name TEXT NOT NULL,
	surname TEXT NOT NULL,
	degree_before TEXT,
	degree_after TEXT,
	email TEXT[],
	street TEXT NULL,
	city TEXT NULL,
	postal_area TEXT NULL,
	specialization TEXT[],
	local_path TEXT,
	valid_from TIMESTAMP NOT NULL,
	valid_to TIMESTAMP NULL,
	inserted TIMESTAMP NOT NULL DEFAULT NOW(),
	inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
	job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX ON advocate_info(advocate_id);
CREATE INDEX ON advocate_info(hash);

COMMENT ON TABLE advocate_info IS 'Contains tuples of volatile advocate data in time.';
COMMENT ON COLUMN advocate_info.advocate_id IS 'To which advocate this information belongs.';
COMMENT ON COLUMN advocate_info.hash IS 'Hash of all fields of this info to provide faster interface for duplicate lookup.';
COMMENT ON COLUMN advocate_info.name IS 'First name of advocate.';
COMMENT ON COLUMN advocate_info.surname IS 'Surname of advocate.';
COMMENT ON COLUMN advocate_info.degree_before IS 'Degree before name.';
COMMENT ON COLUMN advocate_info.degree_after IS 'Degree after name.';
COMMENT ON COLUMN advocate_info.email IS 'E-mail address.';
COMMENT ON COLUMN advocate_info.inserted IS 'Timestamp of creation of this tuple.';
COMMENT ON COLUMN advocate_info.valid_from IS 'Since when the tuple is valid.';
COMMENT ON COLUMN advocate_info.valid_to IS 'Until the tuple is valid, or null when to infinity.';
COMMENT ON COLUMN advocate_info.job_run_id IS 'ID of job run which added this advocate.';

CREATE TYPE advocate_status AS ENUM (
	'active', /* Advocate is active. */
	'suspended', /* Advocates activity is suspended. */
	'removed' /* Advocate was removed or is inactive. */
);

CREATE TABLE advocate (
	id_advocate BIGSERIAL PRIMARY KEY,
	remote_identificator VARCHAR(255) UNIQUE NOT NULL,
	identification_number VARCHAR(40) UNIQUE NULL,
	registration_number VARCHAR(40) NOT NULL,
	status advocate_status,
	advocate_info_id BIGINT NULL REFERENCES advocate_info(id_advocate_info) ON UPDATE CASCADE ON DELETE RESTRICT,
	inserted TIMESTAMP NOT NULL DEFAULT NOW(),
	inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
	job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT,
	updated TIMESTAMP NULL
);

COMMENT ON TABLE advocate IS 'List of advocates which was or can be found inside documents.';
COMMENT ON COLUMN advocate.remote_identificator IS 'ID used by the advocate association.';
COMMENT ON COLUMN advocate.identification_number IS 'Number of advocate.';
COMMENT ON COLUMN advocate.registration_number IS 'Registration number of advocate by the advocate association.';
COMMENT ON COLUMN advocate.advocate_info_id IS 'Active (most up to date) tuple with details.';
COMMENT ON COLUMN advocate.inserted IS 'Timestamp of introduction of advocate into our system.';
COMMENT ON COLUMN advocate.job_run_id IS 'ID of job run which added this advocate.';
COMMENT ON COLUMN advocate.updated IS 'Timestamp of last change of  advocate';

ALTER TABLE advocate_info ADD FOREIGN KEY (advocate_id) REFERENCES advocate(id_advocate) ON UPDATE CASCADE ON DELETE RESTRICT;

-- Refactor tagging schema to be more flexible: split tagging of advocates and case results into two tables to allow separate processing

CREATE TABLE tagging_advocate (
	id_tagging_advocate BIGSERIAL PRIMARY KEY,
	document_id BIGINT NOT NULL REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
	advocate_id BIGINT NULL REFERENCES advocate(id_advocate)  ON UPDATE CASCADE ON DELETE RESTRICT,
	status tagging_status NOT NULL,
	is_final BOOLEAN NULL,
	debug TEXT NULL,
	inserted TIMESTAMP NOT NULL DEFAULT NOW(),
	inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
	job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE UNIQUE INDEX ON tagging_advocate(document_id);
CREATE UNIQUE INDEX ON tagging_advocate(advocate_id);

COMMENT ON TABLE tagging_advocate IS 'Entries containing tagging of documents to advocates with their history (last inserted tagging of certain document is considered valid).';
COMMENT ON COLUMN tagging_advocate.status IS 'Status of tagging, see its states.';
COMMENT ON COLUMN tagging_advocate.is_final IS 'Set to true when created by flawless human.';
COMMENT ON COLUMN tagging_advocate.job_run_id IS 'ID of job run which added this tagging.';

CREATE TABLE tagging_case_result (
	id_tagging_case_result BIGSERIAL PRIMARY KEY,
	document_id BIGINT NOT NULL REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
	case_result case_result NULL,
	status tagging_status NOT NULL,
	is_final BOOLEAN NULL,
	debug TEXT NULL,
	inserted TIMESTAMP NOT NULL DEFAULT NOW(),
	inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
	job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE UNIQUE INDEX ON tagging_case_result(document_id);

COMMENT ON TABLE tagging_case_result IS 'Entries containing tagging of documents with their case result with their history (last inserted tagging of certain document is considered valid).';
COMMENT ON COLUMN tagging_case_result.status IS 'Status of tagging, see its states.';
COMMENT ON COLUMN tagging_case_result.is_final IS 'Set to true when created by flawless human.';
COMMENT ON COLUMN tagging_advocate.job_run_id IS 'ID of job run which added this tagging.';