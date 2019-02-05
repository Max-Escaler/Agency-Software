CREATE OR REPLACE FUNCTION process_staff_password() RETURNS TRIGGER AS $$

	DECLARE
		pass_config	RECORD;
		overlap RECORD;

	BEGIN
		-- If just adjusting the end date (loosely tested) (as done below in this function), allow it to pass and avoid recursion
		IF ( (TG_OP='UPDATE') AND (NEW.staff_id=OLD.staff_id) AND (NEW.staff_password_date=OLD.staff_password_date) AND (NEW.staff_password_md5=OLD.staff_password_md5)) THEN
			RETURN NEW;
		END IF;

		IF (NEW.staff_password_date < COALESCE( (SELECT max(staff_password_date) FROM tbl_staff_password tsp WHERE (tsp.staff_id=NEW.staff_id) AND tsp.staff_password_id != NEW.staff_password_id),NEW.staff_password_date)) THEN
			RAISE EXCEPTION 'Cannot add an older password';
		END IF;
		SELECT INTO pass_config * FROM config_staff_password_current;
		SELECT INTO overlap * FROM staff_password WHERE NEW.staff_password_date BETWEEN staff_password_date AND COALESCE(staff_password_date_end,current_date) AND staff_password_id != NEW.staff_password_id AND staff_id=NEW.staff_id ORDER BY staff_password_id DESC LIMIT 1;
		RAISE NOTICE 'Got overlap record %',overlap.staff_password_id;
		-- Set/Enforce End Date
		IF (NEW.staff_password_date_end IS NULL OR (NOT pass_config.allow_override_default_expiration)) THEN
			NEW.staff_password_date_end=CASE WHEN pass_config.default_expiration_days=-1 THEN NULL ELSE NEW.staff_password_date + pass_config.default_expiration_days END;
			RAISE NOTICE 'End date for new record: %',NEW.staff_password_date_end;
		END IF;
		-- Delete old passwords
		DELETE FROM tbl_staff_password tsp WHERE staff_password_id != NEW.staff_password_id AND staff_id=NEW.staff_id AND (NOT staff_password_id IN (SELECT staff_password_id FROM tbl_staff_password tsp2 WHERE tsp.staff_id=tsp2.staff_id ORDER BY staff_password_date DESC,added_at DESC LIMIT int4larger(0,(pass_config.password_retention_count-CASE WHEN TG_OP='INSERT' THEN 1 ELSE 0 END))));
		-- Adjust end date of old record, if necessary
		IF (overlap.staff_password_id IS NOT NULL) THEN
			RAISE NOTICE 'new end date: %',date_larger(NEW.staff_password_date-1,overlap.staff_password_date);
			RAISE NOTICE 'current date range: %,%,%',(SELECT staff_password_date FROM tbl_staff_password WHERE staff_password_id=overlap.staff_password_id),(SELECT staff_password_date_end FROM tbl_staff_password WHERE staff_password_id=overlap.staff_password_id),(SELECT staff_password_id FROM tbl_staff_password WHERE staff_password_id=overlap.staff_password_id);

			UPDATE tbl_staff_password SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n','')||'Adjusting end date to avoid overlap with password ID ' || NEW.staff_password_id::text,staff_password_date_end=date_larger(NEW.staff_password_date-1,overlap.staff_password_date) WHERE staff_password_id=overlap.staff_password_id;
		END IF;

		RETURN NEW;
	END;

$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS process_staff_password_trigger ON tbl_staff_password;
CREATE TRIGGER process_staff_password_trigger BEFORE INSERT OR UPDATE ON tbl_staff_password
	FOR EACH ROW EXECUTE PROCEDURE process_staff_password();


