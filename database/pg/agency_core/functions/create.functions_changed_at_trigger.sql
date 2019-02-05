 
CREATE OR REPLACE FUNCTION auto_changed_at_update() RETURNS TRIGGER AS '
DECLARE 
	flag oid;
BEGIN
	--determine if table has a changed_at field
	SELECT INTO flag a.attrelid FROM pg_catalog.pg_attribute a
		WHERE a.attrelid = TG_RELID AND a.attname ~ ''^changed_at$'' AND NOT a.attisdropped;
	IF (flag IS NOT NULL) THEN
		SELECT INTO NEW.changed_at CURRENT_TIMESTAMP(0);
	END IF;
	RETURN NEW;
END;' LANGUAGE PLPGSQL;

/*
-- proof of concept:
CREATE TRIGGER tbl_client_note_changed_at_update
        BEFORE UPDATE ON tbl_client_note
        FOR EACH ROW EXECUTE PROCEDURE auto_changed_at_update();

SELECT changed_at FROM tbl_client_note WHERE client_note_id<4;
UPDATE tbl_client_note SET note=note||' jh was here' WHERE client_note_id<4;
SELECT changed_at FROM tbl_client_note WHERE client_note_id<4;

CREATE TRIGGER tbl_engine_config_changed_at_update
        BEFORE UPDATE ON tbl_engine_config
        FOR EACH ROW EXECUTE PROCEDURE auto_changed_at_update();

UPDATE tbl_engine_config SET val_name=val_name;
*/
