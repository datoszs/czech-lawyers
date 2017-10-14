DROP MATERIALIZED VIEW vm_advocate_score;
CREATE MATERIALIZED VIEW vm_advocate_score AS
	SELECT
		*,
		positive - negative AS score,
		NTILE(10) OVER (PARTITION BY court_id ORDER BY positive - negative DESC) AS decile
	FROM (
		SELECT
			advocate.id_advocate,
			"case".court_id,
			SUM(CASE WHEN tagging_result.case_result = 'positive' THEN 1 - court_stats.positive_ratio ELSE 0 END) AS positive,
			SUM(CASE WHEN tagging_result.case_result = 'negative' THEN 1 - court_stats.negative_ratio ELSE 0 END) AS negative
		FROM advocate
		INNER JOIN vw_latest_tagging_advocate AS tagging_advocate
			ON tagging_advocate.advocate_id = advocate.id_advocate
			AND tagging_advocate.status = 'processed'
		INNER JOIN vw_latest_tagging_case_result AS tagging_result
			ON tagging_result.case_id = tagging_advocate.case_id
			AND tagging_result.status = 'processed'
		INNER JOIN vw_case_for_advocates AS "case"
			ON "case".id_case = tagging_result.case_id
		INNER JOIN (
			SELECT
				court_id,
				AVG(CAST((result.case_result = 'positive') AS INT)) as positive_ratio,
				AVG(CAST((result.case_result = 'negative') AS INT)) as negative_ratio
			FROM vw_latest_tagging_case_result AS result
			INNER JOIN vw_case_for_advocates AS "case" ON result.case_id = "case".id_case
			WHERE result.status = 'processed'
			GROUP BY 1
		) AS court_stats ON "case".court_id = court_stats.court_id
		GROUP BY advocate.id_advocate, "case".court_id
	) AS t;

CREATE UNIQUE INDEX ON vm_advocate_score(id_advocate, court_id);
CREATE INDEX ON vm_advocate_score(decile);

COMMENT ON MATERIALIZED VIEW vm_advocate_score IS 'Contains scores of advocates having at least one tagged case.';
