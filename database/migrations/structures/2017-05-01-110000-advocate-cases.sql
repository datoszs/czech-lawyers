CREATE INDEX case_catalogue ON "case" (SUBSTRING(registry_sign FROM '\m(\D+)\M'));

CREATE OR REPLACE VIEW vw_case_for_advocates AS
	SELECT * FROM "case" WHERE
		-- NS: Cdo, Cdon, ICdo, NSČR, Odo, Odon
		(court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('cdo', 'cdon', 'icdo', 'nsčr', 'nscr', 'odo', 'odon') AND year >= 2001)
		-- NS: Tdo
		OR (court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('tdo') AND year >= 2002)
		-- NSS: As, Ads, Afs, Ars, Azs
		OR (court_id = 1 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('as', 'ads', 'afs', 'ars', 'azs') AND year >= 2006)
		OR (court_id = 3);

COMMENT ON VIEW vw_case_for_advocates IS 'View that contains all cases which are relevant for czech advocates project (as they are in some way completed).';
