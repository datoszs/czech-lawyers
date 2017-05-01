CREATE OR REPLACE VIEW vw_latest_tagging_case_result AS
	SELECT
		tagging_case_result.*
	FROM latest_tagging_case_result
	JOIN tagging_case_result ON latest_tagging_case_result.tagging_case_result_id = tagging_case_result.id_tagging_case_result;

COMMENT ON VIEW vw_latest_tagging_case_result IS 'View showing all latest case result taggings with all details';

CREATE OR REPLACE VIEW vw_latest_tagging_advocate AS
	SELECT
		tagging_advocate.*
	FROM latest_tagging_advocate
		JOIN tagging_advocate ON latest_tagging_advocate.tagging_advocate_id = tagging_advocate.id_tagging_advocate;

COMMENT ON VIEW vw_latest_tagging_advocate IS 'View showing all latest advocate taggings with all details';

CREATE OR REPLACE VIEW vw_case_for_advocates AS
	SELECT * FROM "case" WHERE
	-- NS: Cdo, Cdon, ICdo, NSČR, Odo, Odon
	(court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('cdo', 'cdon', 'icdo', 'nsčr', 'odo', 'odon') AND year >= 2001)
	-- NS: Tdo
	OR (court_id = 2 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('tdo') AND year >= 2002)
	-- NSS: As, Ads, Afs, Ars, Azs
	OR (court_id = 1 AND SUBSTRING(registry_sign FROM '\m(\D+)\M') IN ('as', 'ads', 'afs', 'ars', 'azs') AND year >= 2006)
	OR (court_id = 3);

COMMENT ON VIEW vw_case_for_advocates IS 'View that contains all cases which are relevant for czech advocates project (as they are in some way completed).';
