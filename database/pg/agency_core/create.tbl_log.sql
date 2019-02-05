CREATE TABLE tbl_log (
	log_id			SERIAL PRIMARY KEY,
	log_type_code		VARCHAR(10)[] NOT NULL,
	subject				VARCHAR(80) NOT NULL,
	log_text			TEXT NOT NULL,
	occurred_at			TIMESTAMP(0),
	written_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	sys_log				TEXT
);

CREATE VIEW log AS SELECT * FROM tbl_log;

/*
CREATE TRIGGER log_insert_references
	AFTER INSERT ON tbl_log FOR EACH ROW 
	EXECUTE PROCEDURE post_log_references();

CREATE TRIGGER log_insert_verify
	BEFORE INSERT ON tbl_log FOR EACH ROW
	EXECUTE PROCEDURE log_insert_verify();

CREATE RULE log_delete AS
        ON DELETE TO tbl_log
        DO INSTEAD NOTHING;

CREATE OR REPLACE FUNCTION validate_log_modify() RETURNS trigger AS '
        BEGIN
                IF NOT (old.old_log_id=new.old_log_id
                        AND old.added_at=new.added_at
                        AND old.added_by=new.added_by
                        AND old.occurred_at=new.occurred_at
                        AND old.log_text=new.log_text
                        AND old.old_hash_code=new.old_hash_code
                        AND old.md5_sum=new.md5_sum
                        AND old.subject=new.subject
                        AND old.was_assault_staff=new.was_assault_staff
                        AND old.was_assault_client=new.was_assault_client
                        AND old.was_police=new.was_police
                        AND old.was_medics=new.was_medics
                        AND old.was_bar=new.was_bar
                        AND old.was_drugs=new.was_drugs
                        AND old.log_id=new.log_id
                        AND old.in_a=new.in_a
                        AND old.in_b=new.in_b
                        AND old.in_c=new.in_c)

                THEN RAISE EXCEPTION ''Cannot make changes to existing log.'';
                END IF;
                RETURN NEW;
        END;
        ' LANGUAGE 'plpgsql';

--change table name to 'log' when done testing
CREATE TRIGGER log_modify
        BEFORE UPDATE ON log_test
        FOR EACH ROW EXECUTE PROCEDURE validate_log_modify();
*/
