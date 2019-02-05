CREATE TABLE tbl_alert (
	alert_id	BIGSERIAL PRIMARY KEY,
	staff_id     	INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	ref_table     	VARCHAR(100),
	ref_id    	INTEGER,
	alert_subject	VARCHAR,	
	alert_text	TEXT,
	alert_subject_public	VARCHAR,
	alert_text_public	TEXT,
	has_read     	BOOLEAN NOT NULL DEFAULT FALSE,
	read_at     	TIMESTAMP(0),
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT
     );

CREATE VIEW alert AS
SELECT * FROM tbl_alert WHERE NOT is_deleted;

CREATE TRIGGER alert_insert
    AFTER INSERT ON tbl_alert FOR EACH ROW
    EXECUTE PROCEDURE alert_notify();

CREATE INDEX index_tbl_alert_staff_id_ref_table_ref_id ON tbl_alert ( staff_id,ref_table,ref_id );

