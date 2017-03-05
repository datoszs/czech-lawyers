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
