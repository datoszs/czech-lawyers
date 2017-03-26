---------------------------------------------
-- Database schema for project Honest Lawyers
-- SQL: PostgreSQL
---------------------------------------------

------------------- Users -------------------
CREATE TYPE user_type AS ENUM (
  'person', /* Human */
  'system' /* Automated users, e.g. subpart of system */
);

CREATE TYPE user_role AS ENUM (
  'guest' /* All non privilege users */,
  'viewer' /* Users with privilege to see administration but not modify anything */,
  'admin' /* Omnipotent users */
);

CREATE TABLE "user" (
  id_user BIGSERIAL PRIMARY KEY,
  type user_type,
  username VARCHAR(255) NOT NULL UNIQUE,
  password TEXT NULL,
  role user_role NULL,
  is_active BOOLEAN NOT NULL DEFAULT FALSE,
  is_login_allowed BOOLEAN NOT NULL DEFAULT FALSE,
  inserted TIMESTAMP NOT NULL DEFAULT now(),
  updated TIMESTAMP NULL
);

CREATE UNIQUE INDEX ON "user"(username);

INSERT INTO "user" (id_user, type, username, password, is_active, is_login_allowed) VALUES (1, 'system', 'system-import', NULL, TRUE, FALSE);
INSERT INTO "user" (id_user, type, username, password, is_active, is_login_allowed) VALUES (2, 'system', 'system-tagging', NULL, TRUE, FALSE);
INSERT INTO "user" (id_user, type, username, password, is_active, is_login_allowed) VALUES (3, 'system', 'system-crawler', NULL, TRUE, FALSE);

COMMENT ON TABLE "user" IS 'Table with system users.';
COMMENT ON COLUMN "user".type IS 'Type of the user account to distinguish between users, especially the automated ones.';
COMMENT ON COLUMN "user".username IS 'Unique username in the system.';
COMMENT ON COLUMN "user".password IS 'Password of user salted and hashed.';
COMMENT ON COLUMN "user".role IS 'Role of given user. Applies to entities of type person only.';
COMMENT ON COLUMN "user".is_active IS 'States if user account is active (actions under account are allowed).';
COMMENT ON COLUMN "user".is_login_allowed IS 'States whether the user account is allowed to login (e.g. system accounts are not allowed to login).';
COMMENT ON COLUMN "user".inserted IS 'Timestamp when the user was created.';
COMMENT ON COLUMN "user".updated IS 'Timestamp when the user was updated.';

------------------- Courts -------------------
CREATE TABLE court (
  id_court BIGINT PRIMARY KEY,
  name TEXT NOT NULL
);

INSERT INTO court (id_court, name) VALUES (1, 'Nejvyšší správní soud');
INSERT INTO court (id_court, name) VALUES (2, 'Nejvyšší soud');
INSERT INTO court (id_court, name) VALUES (3, 'Ústavní soud');

COMMENT ON TABLE court IS 'List of all (relevant) courts for reference purposes within our system.';
COMMENT ON COLUMN court.name IS 'Human readable name of the court.';


------------------- Jobs -------------------

CREATE TABLE job (
  id_job BIGSERIAL PRIMARY KEY,
  name TEXT UNIQUE,
  description TEXT,
  database_user_id BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

COMMENT ON TABLE job IS 'Entries of every automatic job associated with the system.';
COMMENT ON COLUMN job.name IS 'Name of the job matching the fully qualified name of the command class.';
COMMENT ON COLUMN job.description IS 'Description of the job.';
COMMENT ON COLUMN job.database_user_id IS 'User ID used for database operations in the job.';

CREATE TABLE job_run (
  id_job_run BIGSERIAL PRIMARY KEY,
  job_id BIGINT NOT NULL REFERENCES job(id_job) ON UPDATE CASCADE ON DELETE RESTRICT,
  return_code SMALLINT NULL,
  output TEXT NULL,
  message TEXT NULL,
  executed TIMESTAMP NOT NULL,
  finished TIMESTAMP NULL
);

COMMENT ON TABLE job_run IS 'Trace of every job execution with complete status usable for potential debugging.';
COMMENT ON COLUMN job_run.job_id IS 'ID of job which was executed';
COMMENT ON COLUMN job_run.return_code IS 'Returned code';
COMMENT ON COLUMN job_run.output IS 'Console output';
COMMENT ON COLUMN job_run.message IS 'Message from the job wrapper, such as error messages about error states etc.';
COMMENT ON COLUMN job_run.executed IS 'Time when the execution was started.';
COMMENT ON COLUMN job_run.finished IS 'Time when the execution was finished.';

INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\NSCrawler', 'Supreme Court Crawler', 1);
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\NSSCrawler', 'Supreme Administrative Court Crawler', 2);
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\CausaImport', 'Universal Court Crawler Data Import', 3);
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\USCrawler', 'Constitutional Court Crawler', 4);

------------------- Cases -------------------
/* Our results of cases (beware these are not true results of cases, only their projection) */
CREATE TYPE case_result AS ENUM (
  'neutral', /* Not knowing, the case was stopped for some reason. */
  'positive', /* The court has taken the case into account (The advocate is not an idiot.) */
  'negative', /* The court hasn't taken the case into account (The advocate is an idiot.) */
  'unknown' /* The result could not be determined. */
);
CREATE TABLE "case" (
  id_case BIGSERIAL PRIMARY KEY,
  registry_sign VARCHAR(255) NOT NULL UNIQUE,
  court_id BIGINT NOT NULL REFERENCES court(id_court) ON UPDATE CASCADE ON DELETE RESTRICT,
  inserted TIMESTAMP DEFAULT NOW() NOT NULL
);

CREATE INDEX ON "case"(court_id);

COMMENT ON TABLE "case" IS 'List of cases';
COMMENT ON COLUMN "case".registry_sign IS 'Unique sign under which the case is managed.';
COMMENT ON COLUMN "case".inserted IS 'Timestamp of insertion of this case into our database.';


------------------- Documents -------------------
CREATE TABLE document (
  id_document BIGSERIAL PRIMARY KEY,
  record_id VARCHAR(255) NOT NULL UNIQUE,
  court_id BIGINT NOT NULL REFERENCES court(id_court) ON UPDATE CASCADE ON DELETE RESTRICT,
  case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
  decision_date DATE NOT NULL,
  local_path TEXT,
  web_path TEXT,
  inserted TIMESTAMP DEFAULT NOW() NOT NULL,
  job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX ON document(court_id);
CREATE INDEX ON document(case_id);

COMMENT ON TABLE document IS 'All downloaded documents which are processed by our system.';
COMMENT ON COLUMN document.record_id IS 'System wide-unique identificator of document.';
COMMENT ON COLUMN document.court_id IS 'Court from which the document was obtained.';
COMMENT ON COLUMN document.case_id IS 'Reference to relevant case.';
COMMENT ON COLUMN document.decision_date IS 'Date of decision, obtained from the document.';
COMMENT ON COLUMN document.local_path IS 'Relative path to file within our document folder.';
COMMENT ON COLUMN document.web_path IS 'Absolute path to the document on the webpage of the court.';
COMMENT ON COLUMN document.inserted IS 'Timestamp of insertion of the document into our database.';
COMMENT ON COLUMN document.job_run_id IS 'ID of job run which added this document.';

CREATE TABLE document_supreme_court (
  id_document_supreme_court BIGSERIAL PRIMARY KEY,
  document_id BIGINT REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
  ecli VARCHAR(255) NOT NULL UNIQUE,
  decision_type TEXT NULL
);

CREATE UNIQUE INDEX ON document_supreme_court(document_id);

COMMENT ON TABLE document_supreme_court IS 'Extra information about the document relevant only to supreme court documents.';
COMMENT ON COLUMN document_supreme_court.ecli IS 'ECLI identification of the document.';
COMMENT ON COLUMN document_supreme_court.decision_type IS 'Type of decision of the document.';

CREATE TABLE document_law_court (
  id_document_law_court BIGSERIAL PRIMARY KEY,
  document_id BIGINT REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
  ecli VARCHAR(255) NOT NULL UNIQUE
);

CREATE UNIQUE INDEX ON document_law_court(document_id);

COMMENT ON TABLE document_law_court IS 'Extra information about the document relevant only to law court documents.';
COMMENT ON COLUMN document_law_court.ecli IS 'ECLI identification of the document';

CREATE TABLE document_supreme_administrative_court (
  id_document_supreme_administrative_court BIGSERIAL PRIMARY KEY,
  document_id BIGINT REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
  order_number VARCHAR(255) NOT NULL UNIQUE,
  decision VARCHAR(255)
);

CREATE UNIQUE INDEX ON document_supreme_administrative_court(document_id);

COMMENT ON TABLE document_supreme_administrative_court IS 'Extra information about the document relevant only to supreme administrative court documents.';
COMMENT ON COLUMN document_supreme_administrative_court.order_number IS 'Order number of the document.';
COMMENT ON COLUMN document_supreme_administrative_court.decision IS 'Type of decision parsed from document metadata.';



------------------- Advocates -------------------

CREATE TYPE advocate_status AS ENUM (
  'active', /* Advocate is active. */
  'suspended', /* Advocates activity is suspended. */
  'removed' /* Advocate was removed or is inactive. */
);

CREATE TABLE advocate_info (
  id_advocate_info BIGSERIAL PRIMARY KEY,
  advocate_id BIGINT NOT NULL,
  status advocate_status,
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

COMMENT ON TABLE advocate_info IS 'Contains tuples of volatile advocate data in time.';
COMMENT ON COLUMN advocate_info.advocate_id IS 'To which advocate this information belongs.';
COMMENT ON COLUMN advocate_info.name IS 'First name of advocate.';
COMMENT ON COLUMN advocate_info.surname IS 'Surname of advocate.';
COMMENT ON COLUMN advocate_info.degree_before IS 'Degree before name.';
COMMENT ON COLUMN advocate_info.degree_after IS 'Degree after name.';
COMMENT ON COLUMN advocate_info.email IS 'E-mail address.';
COMMENT ON COLUMN advocate_info.inserted IS 'Timestamp of creation of this tuple.';
COMMENT ON COLUMN advocate_info.valid_from IS 'Since when the tuple is valid.';
COMMENT ON COLUMN advocate_info.valid_to IS 'Until the tuple is valid, or null when to infinity.';
COMMENT ON COLUMN advocate_info.job_run_id IS 'ID of job run which added this advocate.';

CREATE TABLE advocate (
  id_advocate BIGSERIAL PRIMARY KEY,
  remote_identificator VARCHAR(255) UNIQUE NOT NULL,
  identification_number VARCHAR(40) UNIQUE NULL,
  registration_number VARCHAR(40) NOT NULL,
  inserted TIMESTAMP NOT NULL DEFAULT NOW(),
  inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
  job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT,
  updated TIMESTAMP NULL,
  local_path TEXT
);

COMMENT ON TABLE advocate IS 'List of advocates which was or can be found inside documents.';
COMMENT ON COLUMN advocate.remote_identificator IS 'ID used by the advocate association.';
COMMENT ON COLUMN advocate.identification_number IS 'Number of advocate.';
COMMENT ON COLUMN advocate.registration_number IS 'Registration number of advocate by the advocate association.';
COMMENT ON COLUMN advocate.inserted IS 'Timestamp of introduction of advocate into our system.';
COMMENT ON COLUMN advocate.job_run_id IS 'ID of job run which added this advocate.';
COMMENT ON COLUMN advocate.updated IS 'Timestamp of last change of  advocate';

ALTER TABLE advocate_info ADD FOREIGN KEY (advocate_id) REFERENCES advocate(id_advocate) ON UPDATE CASCADE ON DELETE RESTRICT;

------------------- Tagging -------------------

CREATE TYPE tagging_status AS ENUM (
  'failed', /* processing failed due to some error state (exception, missing file etc, to many matches) */
  'ignored' /* tagging of this entity as not relevant (no advocate present,...) */,
  'processed' /* entity was successfulLy tagged */
);

CREATE TABLE tagging_advocate (
  id_tagging_advocate BIGSERIAL PRIMARY KEY,
  case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
  document_id BIGINT NULL REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
  advocate_id BIGINT NULL REFERENCES advocate(id_advocate)  ON UPDATE CASCADE ON DELETE RESTRICT,
  status tagging_status NOT NULL,
  is_final BOOLEAN NULL,
  debug TEXT NULL,
  inserted TIMESTAMP NOT NULL DEFAULT NOW(),
  inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
  job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX ON tagging_advocate(case_id);
CREATE INDEX ON tagging_advocate(advocate_id);

COMMENT ON TABLE tagging_advocate IS 'Entries containing tagging of documents to advocates with their history (last inserted tagging of certain document is considered valid).';
COMMENT ON COLUMN tagging_advocate.case_id IS 'Case to which the tagging belongs';
COMMENT ON COLUMN tagging_advocate.document_id IS 'Document based on which the tagging was done... Or null when done by other means.';
COMMENT ON COLUMN tagging_advocate.status IS 'Status of tagging, see its states.';
COMMENT ON COLUMN tagging_advocate.is_final IS 'Set to true when created by flawless human.';
COMMENT ON COLUMN tagging_advocate.job_run_id IS 'ID of job run which added this tagging.';

CREATE TABLE tagging_case_result (
  id_tagging_case_result BIGSERIAL PRIMARY KEY,
  case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
  document_id BIGINT NULL REFERENCES document(id_document) ON UPDATE CASCADE ON DELETE RESTRICT,
  case_result case_result NULL,
  status tagging_status NOT NULL,
  is_final BOOLEAN NULL,
  debug TEXT NULL,
  inserted TIMESTAMP NOT NULL DEFAULT NOW(),
  inserted_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
  job_run_id BIGINT NULL REFERENCES job_run(id_job_run) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX ON tagging_case_result(case_id);
CREATE INDEX ON tagging_case_result(document_id);

COMMENT ON TABLE tagging_case_result IS 'Entries containing tagging of documents with their case result with their history (last inserted tagging of certain document is considered valid).';
COMMENT ON COLUMN tagging_case_result.case_id IS 'Case to which the tagging belongs';
COMMENT ON COLUMN tagging_case_result.document_id IS 'Document based on which the tagging was done... Or null when done by other means.';
COMMENT ON COLUMN tagging_case_result.status IS 'Status of tagging, see its states.';
COMMENT ON COLUMN tagging_case_result.is_final IS 'Set to true when created by flawless human.';
COMMENT ON COLUMN tagging_advocate.job_run_id IS 'ID of job run which added this tagging.';

CREATE TABLE latest_tagging_case_result (
  case_id BIGINT NOT NULL UNIQUE REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
  tagging_case_result_id BIGINT NOT NULL UNIQUE REFERENCES tagging_case_result(id_tagging_case_result) ON UPDATE CASCADE ON DELETE CASCADE
);

COMMENT ON TABLE latest_tagging_case_result IS 'Table that for cases (with taggings) stores the current tagging';

CREATE TABLE latest_tagging_advocate (
  case_id BIGINT NOT NULL UNIQUE REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
  tagging_advocate_id BIGINT NOT NULL UNIQUE REFERENCES tagging_advocate(id_tagging_advocate) ON UPDATE CASCADE ON DELETE CASCADE
);

COMMENT ON TABLE latest_tagging_advocate IS 'Table that for cases (with taggings) stores the current tagging';


CREATE TABLE case_disputation (
  id_case_disputation BIGSERIAL PRIMARY KEY,
  email TEXT NOT NULL,
  case_id BIGINT NOT NULL REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE CASCADE,
  tagging_case_result_id BIGINT NULL REFERENCES tagging_case_result (id_tagging_case_result) ON UPDATE CASCADE ON DELETE CASCADE,
  tagging_advocate_id BIGINT NULL REFERENCES tagging_advocate (id_tagging_advocate) ON UPDATE CASCADE ON DELETE CASCADE,
  reason TEXT NOT NULL,
  inserted TIMESTAMP NOT NULL DEFAULT NOW(),
  validated TIMESTAMP NULL,
  response TEXT NULL,
  resolved TIMESTAMP NULL,
  resolved_by BIGINT NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

COMMENT ON TABLE case_disputation IS 'Table containing all disputed cases, each dispustation is validated via e-mail.';
COMMENT ON COLUMN case_disputation.case_id IS 'Disputed case ID';
COMMENT ON COLUMN case_disputation.tagging_advocate_id IS 'Disputed tagging of case result or NULL.';
COMMENT ON COLUMN case_disputation.tagging_advocate_id IS 'Disputed tagging of advocate or NULL.';
COMMENT ON COLUMN case_disputation.reason IS 'Mandatory reasoning';
COMMENT ON COLUMN case_disputation.inserted IS 'Timestamp when the disputation was inserted.';
COMMENT ON COLUMN case_disputation.validated IS 'Timestamp of validation, or NULL when not validated';
COMMENT ON COLUMN case_disputation.resolved IS 'Timestamp when the disputation was resolved.';
COMMENT ON COLUMN case_disputation.resolved_by IS 'ID of user which resolved the dispustation.';