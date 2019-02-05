CREATE TABLE tbl_client_note (
	client_note_id		SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client(client_id),
	is_front_page		BOOLEAN NOT NULL,
	front_page_until	TIMESTAMP(0),
	flag_entry_codes	VARCHAR[],
	flag_entry_until	TIMESTAMP(0),
	is_entry_dismissable		BOOLEAN NOT NULL DEFAULT TRUE,
	note				TEXT NOT NULL,
	is_dismissed		BOOLEAN,
	dismissed_by		INTEGER REFERENCES tbl_staff (staff_id),
	dismissed_at		TIMESTAMP(0),
	dismissed_comment	TEXT,
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
	CONSTRAINT dismissed_check CHECK (COALESCE(is_dismissed::text,dismissed_at::text,dismissed_by::text,dismissed_comment) IS NULL
		OR ( ( (is_dismissed::text || dismissed_at::text || dismissed_by::text) IS NOT NULL) AND is_entry_dismissable))
);

/*
DROP VIEW elevated_concern_note;
DROP VIEW client_note_flag_entry;
DROP VIEW client_note_front_page;
DROP VIEW client_note;
*/

CREATE OR REPLACE VIEW client_note AS
SELECT * FROM tbl_client_note WHERE NOT is_deleted;

CREATE OR REPLACE VIEW client_note_flag_entry AS
SELECT * FROM client_note 
	WHERE (flag_entry_codes IS NOT NULL)
	AND NOT  ( is_entry_dismissable AND (  is_dismissed IS NOT NULL ) AND is_dismissed)
	AND (COALESCE(flag_entry_until,current_timestamp)>=current_timestamp);

CREATE OR REPLACE VIEW client_note_front_page AS
SELECT * FROM client_note WHERE is_front_page
	AND COALESCE(front_page_until,current_timestamp) >= current_timestamp;


