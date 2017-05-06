CREATE MATERIALIZED VIEW vm_advocate_score AS
	SELECT
		id_advocate,
		COUNT(CASE WHEN vw_latest_tagging_case_result.case_result = 'positive' THEN 1 END) AS positive,
		COUNT(CASE WHEN vw_latest_tagging_case_result.case_result = 'negative' THEN 1 END) AS negative,
		COUNT(CASE WHEN vw_latest_tagging_case_result.case_result = 'positive' THEN 1 END) - COUNT(CASE WHEN vw_latest_tagging_case_result.case_result = 'negative' THEN 1 END) AS score,
		ntile(10) OVER (ORDER BY COUNT(CASE WHEN vw_latest_tagging_case_result.case_result = 'positive' THEN 1 END) - COUNT(CASE WHEN vw_latest_tagging_case_result.case_result = 'negative' THEN 1 END) DESC) AS decile
	FROM advocate
		JOIN vw_latest_tagging_advocate ON vw_latest_tagging_advocate.advocate_id = id_advocate AND vw_latest_tagging_advocate.status = 'processed'
		JOIN vw_latest_tagging_case_result ON vw_latest_tagging_case_result.case_id = vw_latest_tagging_advocate.case_id AND vw_latest_tagging_case_result.status = 'processed'
		JOIN vw_case_for_advocates ON vw_case_for_advocates.id_case = vw_latest_tagging_case_result.case_id
	GROUP BY id_advocate;

CREATE UNIQUE INDEX ON vm_advocate_score(id_advocate);
CREATE INDEX ON vm_advocate_score(decile);

COMMENT ON MATERIALIZED VIEW vm_advocate_score IS 'Contains scores of advocates having at least one tagged case.';
