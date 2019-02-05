-------------------------------------
--
--        Feedback alert (copied from Jail/Hospital stuff)
--
-------------------------------------

CREATE OR REPLACE FUNCTION assessment_insert() RETURNS trigger AS '
DECLARE
  notify integer[3] := ARRAY[440,320,945];
  ref_tab TEXT := ''assessment'';
  alert_sub TEXT;
  alert_txt TEXT;
  feed_id integer;
  s integer;
  score	integer;
  ac integer;

BEGIN
  SELECT INTO score total_rating FROM assessment WHERE assessment_id=NEW.assessment_id;

  alert_sub  := staff_name(NEW.added_by) || '' has assessed '' || client_name(NEW.client_id) 
		|| '' ('' || NEW.client_id::text || ''), score '' || score::text;
  alert_txt  := ''The Record ID # is '' || NEW.assessment_id;
	ac := array_count(notify);
	FOR s IN 1..ac LOOP
		INSERT INTO tbl_alert (
			staff_id,
			ref_table,
			ref_id,
			alert_subject,
			alert_text,
			added_by,
			changed_by
		)
		VALUES (notify[s],ref_tab,NEW.assessment_id,alert_sub,alert_txt,sys_user(),sys_user());
	END LOOP;
        RETURN NEW;
END;' language 'plpgsql';

CREATE TRIGGER assessment_insert
    AFTER INSERT ON tbl_assessment FOR EACH ROW
    EXECUTE PROCEDURE assessment_insert();

