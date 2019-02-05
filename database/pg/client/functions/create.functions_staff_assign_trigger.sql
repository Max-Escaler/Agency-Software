CREATE OR REPLACE FUNCTION staff_assign_no_unknowns() RETURNS TRIGGER AS '
BEGIN
	IF TG_OP = ''INSERT'' THEN
		IF NEW.staff_assign_type_code = ''UNKNOWN'' THEN
			RAISE EXCEPTION ''Cannot insert record with staff assignment type of UNKNOWN'';
		END IF;
	END IF;
	IF TG_OP = ''UPDATE'' THEN 
		IF NEW.staff_assign_type_code = ''UNKNOWN'' AND OLD.staff_assign_type_code <> ''UNKNOWN'' THEN
			RAISE EXCEPTION ''Cannot change staff assignment type to UNKNOWN'';
		END IF;
	END IF;
	RETURN NEW;
END;' LANGUAGE 'plpgsql';

CREATE TRIGGER staff_assign_verify_no_unknowns
BEFORE UPDATE OR INSERT ON tbl_staff_assign
FOR EACH ROW EXECUTE PROCEDURE staff_assign_no_unknowns();