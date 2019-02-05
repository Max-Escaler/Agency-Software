--this trigger will create as payee record in addition to a primary cm record if the virtual 
--lookup table value of CM_PAYEE is used on insert
CREATE OR REPLACE FUNCTION staff_assign_insert() RETURNS trigger AS '
BEGIN
	IF NEW.staff_assign_type_code<>''CM_PAYEE'' THEN
		RETURN NEW;
	END IF;
	IF NEW.staff_id IS NULL THEN
		RAISE EXCEPTION ''Cannot use CM_PAYEE staff_assign_type_code with non-DESC staff.'';
		RETURN NEW;
	END IF;
	IF NEW.is_deleted THEN
		RAISE EXCEPTION ''Cannot insert deleted records using CM_PAYEE staff_assign_type_code.'';
		RETURN NEW;
	END IF;
	INSERT INTO tbl_staff_assign (
 		client_id,
		staff_id,
		staff_assign_type_code,
		staff_assign_date,
		staff_assign_date_end,
		send_alert,
		comment,
		added_by,
		changed_by,
		sys_log
	) VALUES (
		NEW.client_id,
		NEW.staff_id,
		''PAYEE'',
		NEW.staff_assign_date,
		NEW.staff_assign_date_end,
		NEW.send_alert,
		NEW.comment,
		NEW.added_by,
		NEW.changed_by,
		NEW.sys_log
	);
	NEW.staff_assign_type_code := ''CM_PRIMARY'';
	RETURN NEW;
END;' language 'plpgsql';

CREATE TRIGGER staff_assign_insert
    BEFORE INSERT ON tbl_staff_assign FOR EACH ROW
    EXECUTE PROCEDURE staff_assign_insert();

