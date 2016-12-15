-- Add court ID to cases
ALTER TABLE "case" ADD COLUMN court_id BIGINT NULL REFERENCES court(id_court) ON UPDATE CASCADE ON DELETE RESTRICT;
CREATE INDEX ON "case"(court_id);

-- Populate data from documents
UPDATE "case" SET court_id = (SELECT DISTINCT court_id FROM document WHERE document.case_id = "case".id_case);
-- Populate data from registry signs
UPDATE "case" SET court_id =
	CASE WHEN split_part(registry_sign, ' ', 2) = ANY (ARRAY['cdo', 'nscr', 'icdo', 'tdo', 'odo']) THEN 2 /* NS */
	WHEN split_part(registry_sign, ' ', 2) = ANY (ARRAY['as', 'ads', 'afs', 'ars', 'azs', 'aps', 'aos', 'ans']) OR split_part(registry_sign, ' ', 1) = 'ars' THEN 1 /* NSS */
	WHEN split_part(registry_sign, ' ', 2) = ANY (ARRAY['ús']) OR strpos(registry_sign, 'ús ') > 0 OR strpos(registry_sign, 'ús-') > 0  THEN 3 /* US */
	ELSE NULL
	END
WHERE court_id IS NULL;

ALTER TABLE "case" ALTER COLUMN court_id SET NOT NULL;