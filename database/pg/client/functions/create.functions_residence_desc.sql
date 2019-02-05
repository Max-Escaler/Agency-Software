CREATE OR REPLACE FUNCTION residence_own_verify() RETURNS TRIGGER AS $$
DECLARE
	rid INTEGER;
BEGIN
	IF NEW.residence_date_end IS NULL THEN
		SELECT INTO rid residence_own_id FROM residence_own WHERE residence_own.client_id=NEW.client_id AND residence_own.residence_date_end IS NULL;
		IF rid IS NOT NULL THEN
			RAISE EXCEPTION 'Client % has a current residence_own record (residence_own_id: %).',NEW.client_id, rid;
		END IF;
	END IF;
	RETURN NEW;
END;$$ LANGUAGE plpgsql;

CREATE TRIGGER check_residence_own_record  BEFORE INSERT
    ON tbl_residence_own FOR EACH ROW
    EXECUTE PROCEDURE residence_own_verify();
