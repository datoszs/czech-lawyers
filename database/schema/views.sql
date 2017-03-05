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
