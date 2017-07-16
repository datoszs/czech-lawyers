CREATE OR REPLACE VIEW vw_case_for_advocates AS
	SELECT * FROM "case" WHERE
		-- NS: Cdo, Cdon, ICdo, NSČR, Odo, Odon
		(court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('cdo', 'cdon', 'icdo', 'nsčr', 'odo', 'odon') AND year >= 2001)
		-- NS: Tdo
		OR (court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('tdo') AND year >= 2002)
		-- NSS: As, Ads, Afs, Ars, Azs, Ans, Aos, Aps
		OR (court_id = 1 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('as', 'ads', 'afs', 'ars', 'azs', 'ans', 'aos', 'aps') AND year >= 2006)
		OR (court_id = 3 AND year >= 2007 AND "case".id_case NOT IN 
			(
			SELECT case_id
			FROM document JOIN document_law_court ON document.id_document = document_law_court.document_id
			WHERE document_law_court.proceedings_type NOT LIKE 'O ústavních stížnostech'
			)
		);

COMMENT ON VIEW vw_case_for_advocates IS 'View that contains all cases which are relevant for czech advocates project (as they are in some way completed).';
