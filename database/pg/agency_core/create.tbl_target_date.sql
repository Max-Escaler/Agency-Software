CREATE TABLE tbl_target_date (
	target_date_id			SERIAL PRIMARY KEY,
	target_date			DATE NOT NULL,
	effective_at		TIMESTAMP NOT NULL DEFAULT current_timestamp,
	comment				TEXT,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE VIEW target_date AS SELECT * FROM tbl_target_date WHERE NOT is_deleted;

CREATE VIEW target_date_current AS 
	SELECT * FROM target_date ORDER BY effective_at DESC LIMIT 1;

CREATE OR REPLACE FUNCTION target_date_no_edit_or_delete() RETURNS TRIGGER AS $$

	BEGIN
	IF (TG_OP <> 'INSERT')
	THEN RAISE EXCEPTION 'Target records cannot be changed or deleted.  (Attempted operation: %)',TG_OP;
	END IF;
	IF (NEW.target_date <> date_trunc('month',NEW.target_date))
	THEN RAISE EXCEPTION 'Target date must be the first of a month';
	END IF;
	IF (NEW.target_date <= target_date())
	THEN RAISE EXCEPTION 'Target date can only be moved forward';
	END IF;
	RETURN NEW;
	END;

$$ LANGUAGE plpgsql;

CREATE TRIGGER protect_target_date BEFORE INSERT OR UPDATE OR DELETE ON tbl_target_date FOR EACH ROW EXECUTE PROCEDURE target_date_no_edit_or_delete();
CREATE TRIGGER target_date_no_trunacte BEFORE TRUNCATE ON tbl_target_date FOR STATEMENT EXECUTE PROCEDURE target_date_no_edit_or_delete();  

