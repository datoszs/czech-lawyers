-- Create "materialized" latest tagging of case results
CREATE TABLE latest_tagging_case_result (
	case_id BIGINT NOT NULL UNIQUE REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
	tagging_case_result_id BIGINT NOT NULL UNIQUE REFERENCES tagging_case_result(id_tagging_case_result) ON UPDATE CASCADE ON DELETE CASCADE
);

COMMENT ON TABLE latest_tagging_case_result IS 'Table that for cases (with taggings) stores the current tagging';

-- Populate with current data
INSERT INTO latest_tagging_case_result (SELECT DISTINCT ON (case_id) case_id, id_tagging_case_result FROM tagging_case_result ORDER BY case_id, inserted DESC);

-- Create wrapper view
CREATE OR REPLACE VIEW vw_latest_tagging_case_result AS
	SELECT
		tagging_case_result.*
	FROM latest_tagging_case_result
		JOIN tagging_case_result ON latest_tagging_case_result.tagging_case_result_id = tagging_case_result.id_tagging_case_result;

COMMENT ON VIEW vw_latest_tagging_case_result IS 'View showing all latest case result taggings with all details';



-- Create "materialized" latest tagging of advocates
CREATE TABLE latest_tagging_advocate (
	case_id BIGINT NOT NULL UNIQUE REFERENCES "case"(id_case) ON UPDATE CASCADE ON DELETE RESTRICT,
	tagging_advocate_id BIGINT NOT NULL UNIQUE REFERENCES tagging_advocate(id_tagging_advocate) ON UPDATE CASCADE ON DELETE CASCADE
);

COMMENT ON TABLE latest_tagging_advocate IS 'Table that for cases (with taggings) stores the current tagging';

-- Populate with current data
INSERT INTO latest_tagging_advocate (SELECT DISTINCT ON (case_id) case_id, id_tagging_advocate FROM tagging_advocate ORDER BY case_id, inserted DESC);

-- Create wrapper view
CREATE OR REPLACE VIEW vw_latest_tagging_advocate AS
	SELECT
		tagging_advocate.*
	FROM latest_tagging_advocate
		JOIN tagging_advocate ON latest_tagging_advocate.tagging_advocate_id = tagging_advocate.id_tagging_advocate;

COMMENT ON VIEW vw_latest_tagging_advocate IS 'View showing all latest advocate taggings with all details';


-- Implement triggers for maintaining consistency
CREATE OR REPLACE FUNCTION fnc_trg_tagging_prevent_case_id_change() RETURNS TRIGGER AS $$
BEGIN
	IF TG_OP != 'INSERT' AND OLD.case_id != NEW.case_id THEN
		RAISE EXCEPTION 'Case ID cannot be changed due to consistency of is_latest flag. Please create new row.';
	END IF;
	RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_tagging_case_result_case_id_change BEFORE UPDATE OR INSERT ON tagging_case_result FOR EACH ROW
EXECUTE PROCEDURE fnc_trg_tagging_prevent_case_id_change();

CREATE TRIGGER trg_tagging_case_result_case_id_change BEFORE UPDATE OR INSERT ON tagging_advocate FOR EACH ROW
EXECUTE PROCEDURE fnc_trg_tagging_prevent_case_id_change();

CREATE OR REPLACE FUNCTION fnc_trg_tagging_maintain_is_latest_flag() RETURNS TRIGGER AS $$
DECLARE
	latest_tagging_id BIGINT;
	case_id BIGINT;
BEGIN
	-- Ensure that is_latest is always true on latest row in given case_id group

	-- Get ID according to opration
	IF TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN
		case_id := OLD.case_id;
	ELSE
		case_id := NEW.case_id;
	END IF;

	-- Get ID of latest tagging of given case
	EXECUTE 'SELECT id_' || TG_TABLE_NAME || ' FROM ' || TG_TABLE_NAME || ' WHERE case_id = $1 ORDER BY inserted DESC LIMIT 1' INTO latest_tagging_id USING case_id;

	-- Delete if latest tagging was deleted
	IF latest_tagging_id IS NULL THEN
		EXECUTE 'DELETE FROM latest_' || TG_TABLE_NAME || ' WHERE case_id = $1' USING case_id;
		RETURN NEW;
	END IF;

	-- Insert or update
	EXECUTE 'INSERT INTO latest_' || TG_TABLE_NAME || ' (case_id, ' || TG_TABLE_NAME || '_id) VALUES ($1, $2) ON CONFLICT (case_id) DO UPDATE SET ' || TG_TABLE_NAME || '_id = $3' USING case_id, latest_tagging_id, latest_tagging_id;

	RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER trg_tagging_case_result_is_latest_consistency AFTER UPDATE OR INSERT OR DELETE ON tagging_case_result FOR EACH ROW
EXECUTE PROCEDURE fnc_trg_tagging_maintain_is_latest_flag();

CREATE TRIGGER trg_tagging_advocate_is_latest_consistency AFTER UPDATE OR INSERT OR DELETE ON tagging_advocate FOR EACH ROW
EXECUTE PROCEDURE fnc_trg_tagging_maintain_is_latest_flag();
