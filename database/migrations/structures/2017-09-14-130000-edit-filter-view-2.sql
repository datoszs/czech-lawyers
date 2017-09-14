CREATE OR REPLACE VIEW vw_case_for_advocates AS
	SELECT * FROM "case" WHERE
    --NS: Cdo, Cdon, ICdo, NSČR, Odo, Odon
    (court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('cdo', 'cdon', 'icdo', 'nsčr', 'nscr', 'odo', 'odon') AND year >= 2001)
    --NS: Tdo
    OR (court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('tdo') AND year >= 2002)
    --NSS: As, Ads, Afs, Ars, Azs, Ans, Aos, Aps
    OR (court_id = 1 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('as', 'ads', 'afs', 'ars', 'azs', 'ans', 'aos', 'aps') AND year >= 2006)
    --ÚS: pouze ústavní stížnosti od roku 2007
    OR (court_id = 3 AND "case".id_case IN
        (
        SELECT DISTINCT id_case
        FROM "case" c
        JOIN document ON (document.case_id = c.id_case)
        JOIN document_law_court ON (document_law_court.document_id = document.id_document)
        WHERE year >= 2007 AND proceedings_type ilike '%O ústavních stížnostech%'
        )
    )
    --ÚS: vše do konce roku 2006
    OR (court_id = 3 AND year < 2007);

COMMENT ON VIEW vw_case_for_advocates IS 'View that contains all cases which are relevant for czech advocates project (as they are in some way completed).';
